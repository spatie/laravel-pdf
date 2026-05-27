<?php

namespace Spatie\LaravelPdf\Encryption;

use Com\Tecnick\Pdf\Encrypt\Encrypt;
use SensitiveParameter;
use Spatie\LaravelPdf\Enums\Permission;
use Spatie\LaravelPdf\Exceptions\CouldNotDecryptPdf;
use Spatie\LaravelPdf\Exceptions\CouldNotEncryptPdf;

class DefaultPdfEncrypter implements PdfEncrypter
{
    protected const AES_256_R6 = 4;

    protected const ZERO_IV = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";

    protected const ESCAPE_SEQUENCES = [
        'n' => "\n",
        'r' => "\r",
        't' => "\t",
        'b' => "\x08",
        'f' => "\x0C",
        '(' => '(',
        ')' => ')',
        '\\' => '\\',
    ];

    public function encrypt(string $pdf, PdfEncryption $encryption): string
    {
        $this->guardAgainstMissingPackage();
        $this->guardAgainstUnsupportedStructure($pdf);

        $objects = $this->parseObjects($pdf);

        if ($objects === []) {
            throw CouldNotEncryptPdf::couldNotParse('no indirect objects were found');
        }

        $fileId = random_bytes(16);

        $encrypter = new Encrypt(
            enabled: true,
            file_id: bin2hex($fileId),
            mode: self::AES_256_R6,
            permissions: $this->blockedPermissions($encryption),
            user_pass: $encryption->userPassword,
            owner_pass: $encryption->ownerPassword ?? '',
        );

        [$body, $offsets] = $this->renderObjects(
            $objects,
            fn (string $value, int $objectNumber) => $encrypter->encryptString($value, $objectNumber),
            emitStringsAsHex: true,
            version: $this->resolveVersion($pdf),
        );

        $encryptObjectNumber = max(array_keys($objects)) + 1;
        $offsets[$encryptObjectNumber] = strlen($body);
        $body .= $this->renderEncryptionObject($encryptObjectNumber, $encrypter->getEncryptionData());

        $trailer = $this->trailerDictionary($pdf, $encryptObjectNumber + 1, [
            'Encrypt' => "{$encryptObjectNumber} 0 R",
            'ID' => $this->idArray($fileId),
        ]);

        return $body.$this->renderCrossReference($offsets, strlen($body), $trailer);
    }

    public function decrypt(string $pdf, #[SensitiveParameter] string $password): string
    {
        $encryptObjectNumber = $this->encryptObjectNumber($pdf);

        $objects = $this->parseObjects($pdf);

        $encryptionDictionary = $objects[$encryptObjectNumber]['dictionary']
            ?? throw CouldNotDecryptPdf::missingEncryptionDictionary();

        $this->guardAgainstUnsupportedHandler($encryptionDictionary);

        $documentKey = $this->recoverDocumentKey($encryptionDictionary, $password);

        unset($objects[$encryptObjectNumber]);

        [$body, $offsets] = $this->renderObjects(
            $objects,
            fn (string $value) => $this->aesDecrypt($value, $documentKey),
            emitStringsAsHex: false,
            version: $this->resolveVersion($pdf),
        );

        $trailer = $this->trailerDictionary($pdf, max(array_keys($offsets)) + 1, []);

        return $body.$this->renderCrossReference($offsets, strlen($body), $trailer);
    }

    /**
     * @param  array<int, array{generation: int, dictionary: string, stream: ?string}>  $objects
     * @return array{0: string, 1: array<int, int>}
     */
    protected function renderObjects(array $objects, callable $transform, bool $emitStringsAsHex, string $version): array
    {
        $body = "%PDF-{$version}\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($body);

            $body .= "{$number} {$object['generation']} obj\n";
            $body .= $this->renderObjectContent($object, $number, $transform, $emitStringsAsHex);
            $body .= "endobj\n";
        }

        return [$body, $offsets];
    }

    /**
     * @param  array{generation: int, dictionary: string, stream: ?string}  $object
     */
    protected function renderObjectContent(array $object, int $number, callable $transform, bool $emitStringsAsHex): string
    {
        $dictionary = $this->transformStrings(
            $object['dictionary'],
            fn (string $value) => $transform($value, $number),
            $emitStringsAsHex,
        );

        if ($object['stream'] === null) {
            return rtrim($dictionary)."\n";
        }

        $stream = $transform($object['stream'], $number);
        $dictionary = $this->replaceLength(rtrim($dictionary), strlen($stream));

        return $dictionary."\nstream\n".$stream."\nendstream\n";
    }

    /**
     * @return array<int, array{generation: int, dictionary: string, stream: ?string}>
     */
    protected function parseObjects(string $pdf): array
    {
        $objects = [];
        $offset = 0;

        while (preg_match('/(\d+)\s+(\d+)\s+obj/', $pdf, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $number = (int) $matches[1][0];
            $generation = (int) $matches[2][0];
            $contentStart = $matches[0][1] + strlen($matches[0][0]);

            $endObjectPosition = strpos($pdf, 'endobj', $contentStart);

            if ($endObjectPosition === false) {
                break;
            }

            $streamPosition = strpos($pdf, 'stream', $contentStart);
            $isStream = $streamPosition !== false
                && $streamPosition < $endObjectPosition
                && str_ends_with(rtrim(substr($pdf, $contentStart, $streamPosition - $contentStart)), '>>');

            if (! $isStream) {
                $objects[$number] = [
                    'generation' => $generation,
                    'dictionary' => substr($pdf, $contentStart, $endObjectPosition - $contentStart),
                    'stream' => null,
                ];

                $offset = $endObjectPosition + 6;

                continue;
            }

            $dictionary = substr($pdf, $contentStart, $streamPosition - $contentStart);
            $dataStart = $this->streamDataStart($pdf, $streamPosition);
            $stream = $this->readStream($pdf, $dictionary, $dataStart);

            $objects[$number] = [
                'generation' => $generation,
                'dictionary' => $dictionary,
                'stream' => $stream,
            ];

            $offset = strpos($pdf, 'endobj', $dataStart + strlen($stream));
            $offset = $offset === false ? strlen($pdf) : $offset + 6;
        }

        return $objects;
    }

    protected function streamDataStart(string $pdf, int $streamPosition): int
    {
        $dataStart = $streamPosition + strlen('stream');

        if (substr($pdf, $dataStart, 2) === "\r\n") {
            return $dataStart + 2;
        }

        if (($pdf[$dataStart] ?? '') === "\n" || ($pdf[$dataStart] ?? '') === "\r") {
            return $dataStart + 1;
        }

        return $dataStart;
    }

    protected function readStream(string $pdf, string $dictionary, int $dataStart): string
    {
        if (preg_match('/\/Length\s+(\d+)(?!\s+\d+\s+R)/', $dictionary, $matches)) {
            return substr($pdf, $dataStart, (int) $matches[1]);
        }

        $endStreamPosition = strpos($pdf, 'endstream', $dataStart);

        if ($endStreamPosition === false) {
            throw CouldNotEncryptPdf::couldNotParse('a stream was not terminated');
        }

        return rtrim(substr($pdf, $dataStart, $endStreamPosition - $dataStart), "\r\n");
    }

    protected function transformStrings(string $content, callable $transform, bool $emitStringsAsHex): string
    {
        $output = '';
        $index = 0;
        $length = strlen($content);

        while ($index < $length) {
            $character = $content[$index];

            if ($character === '(') {
                [$raw, $index] = $this->readLiteralString($content, $index);
                $output .= $this->renderString($transform($raw), $emitStringsAsHex);

                continue;
            }

            if ($character === '<' && ($content[$index + 1] ?? '') === '<') {
                $output .= '<<';
                $index += 2;

                continue;
            }

            if ($character === '<') {
                [$raw, $index] = $this->readHexString($content, $index);
                $output .= $this->renderString($transform($raw), $emitStringsAsHex);

                continue;
            }

            $output .= $character;
            $index++;
        }

        return $output;
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function readLiteralString(string $content, int $start): array
    {
        $index = $start + 1;
        $length = strlen($content);
        $depth = 1;
        $raw = '';

        while ($index < $length) {
            $character = $content[$index];

            if ($character === '\\') {
                [$decoded, $index] = $this->decodeEscape($content, $index);
                $raw .= $decoded;

                continue;
            }

            if ($character === '(') {
                $depth++;
            }

            if ($character === ')') {
                $depth--;

                if ($depth === 0) {
                    return [$raw, $index + 1];
                }
            }

            $raw .= $character;
            $index++;
        }

        return [$raw, $index];
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function decodeEscape(string $content, int $index): array
    {
        $next = $content[$index + 1] ?? '';

        if (array_key_exists($next, self::ESCAPE_SEQUENCES)) {
            return [self::ESCAPE_SEQUENCES[$next], $index + 2];
        }

        if ($next === "\n") {
            return ['', $index + 2];
        }

        if ($next === "\r") {
            $skip = ($content[$index + 2] ?? '') === "\n" ? 3 : 2;

            return ['', $index + $skip];
        }

        if ($next >= '0' && $next <= '7') {
            $octal = $next;
            $cursor = $index + 2;

            while (strlen($octal) < 3 && ($content[$cursor] ?? '') >= '0' && ($content[$cursor] ?? '') <= '7') {
                $octal .= $content[$cursor];
                $cursor++;
            }

            return [chr(octdec($octal) & 0xFF), $cursor];
        }

        return [$next, $index + 2];
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function readHexString(string $content, int $start): array
    {
        $end = strpos($content, '>', $start + 1);

        if ($end === false) {
            return ['', strlen($content)];
        }

        $hex = preg_replace('/\s+/', '', substr($content, $start + 1, $end - $start - 1));

        if (strlen($hex) % 2 !== 0) {
            $hex .= '0';
        }

        return [hex2bin($hex) ?: '', $end + 1];
    }

    protected function renderString(string $raw, bool $emitStringsAsHex): string
    {
        if ($emitStringsAsHex) {
            return '<'.bin2hex($raw).'>';
        }

        $escaped = strtr($raw, [
            '\\' => '\\\\',
            '(' => '\\(',
            ')' => '\\)',
            "\r" => '\\r',
        ]);

        return "({$escaped})";
    }

    protected function replaceLength(string $dictionary, int $length): string
    {
        return preg_replace('/\/Length\s+\d+(\s+\d+\s+R)?/', "/Length {$length}", $dictionary, 1);
    }

    /**
     * @param  array<int, int>  $offsets
     */
    protected function renderCrossReference(array $offsets, int $crossReferenceOffset, string $trailer): string
    {
        ksort($offsets);
        $size = max(array_keys($offsets)) + 1;

        $crossReference = "xref\n0 {$size}\n0000000000 65535 f \n";

        for ($number = 1; $number < $size; $number++) {
            $crossReference .= isset($offsets[$number])
                ? sprintf("%010d 00000 n \n", $offsets[$number])
                : "0000000000 65535 f \n";
        }

        return $crossReference."trailer\n{$trailer}\nstartxref\n{$crossReferenceOffset}\n%%EOF\n";
    }

    /**
     * @param  array<string, string>  $extraEntries
     */
    protected function trailerDictionary(string $pdf, int $size, array $extraEntries): string
    {
        $entries = "/Size {$size} /Root {$this->reference($pdf, 'Root')}";

        $info = $this->reference($pdf, 'Info');

        if ($info !== null) {
            $entries .= " /Info {$info}";
        }

        foreach ($extraEntries as $key => $value) {
            $entries .= " /{$key} {$value}";
        }

        return "<< {$entries} >>";
    }

    protected function reference(string $pdf, string $key): ?string
    {
        if (! preg_match_all('/\/'.$key.'\s+(\d+\s+\d+\s+R)/', $pdf, $matches)) {
            return null;
        }

        return end($matches[1]);
    }

    protected function idArray(string $fileId): string
    {
        $hex = bin2hex($fileId);

        return "[<{$hex}> <{$hex}>]";
    }

    protected function resolveVersion(string $pdf): string
    {
        preg_match('/^%PDF-(\d+\.\d+)/', $pdf, $matches);

        $version = $matches[1] ?? '1.7';

        return (float) $version < 1.7 ? '1.7' : $version;
    }

    /**
     * @return array<int, string>
     */
    protected function blockedPermissions(PdfEncryption $encryption): array
    {
        if ($encryption->permissions === null) {
            return [];
        }

        $granted = array_map(fn (Permission $permission) => $permission->value, $encryption->permissions);

        return array_values(array_diff(Permission::all(), $granted));
    }

    /**
     * @param  array<string, mixed>  $encryptionData
     */
    protected function renderEncryptionObject(int $objectNumber, array $encryptionData): string
    {
        $fileKey = $encryptionData['key'];
        $userValue = $encryptionData['U'];

        [$ownerValue, $ownerKeyValue] = $this->ownerValues($encryptionData['owner_password'], $fileKey, $userValue);

        $entries = implode("\n", [
            '/Filter /Standard',
            '/V 5',
            '/R 6',
            '/Length 256',
            '/CF << /StdCF << /Type /CryptFilter /CFM /AESV3 /AuthEvent /DocOpen /Length 32 >> >>',
            '/StmF /StdCF',
            '/StrF /StdCF',
            '/O '.$this->renderString($ownerValue, false),
            '/U '.$this->renderString($userValue, false),
            '/OE '.$this->renderString($ownerKeyValue, false),
            '/UE '.$this->renderString($encryptionData['UE'], false),
            '/Perms '.$this->renderString($encryptionData['perms'], false),
            '/P '.(int) $encryptionData['P'],
            '/EncryptMetadata true',
        ]);

        return "{$objectNumber} 0 obj\n<< {$entries} >>\nendobj\n";
    }

    /**
     * Compute a spec-correct AES-256 R6 owner value and owner key.
     *
     * tc-lib-pdf-encrypt omits the 48-byte U string from the initial hash of
     * Algorithm 2.B, producing an owner password that standard readers reject,
     * so the owner values are computed here instead.
     *
     * @return array{0: string, 1: string}
     */
    protected function ownerValues(#[SensitiveParameter] string $ownerPassword, string $fileKey, string $userValue): array
    {
        $validationSalt = random_bytes(8);
        $keySalt = random_bytes(8);

        $ownerValue = $this->hash2B($ownerPassword, $validationSalt, $userValue).$validationSalt.$keySalt;

        $intermediateKey = $this->hash2B($ownerPassword, $keySalt, $userValue);

        $ownerKeyValue = openssl_encrypt(
            $fileKey,
            'aes-256-cbc',
            $intermediateKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::ZERO_IV,
        );

        return [$ownerValue, $ownerKeyValue];
    }

    protected function recoverDocumentKey(string $encryptionDictionary, #[SensitiveParameter] string $password): string
    {
        $userValue = $this->dictionaryString($encryptionDictionary, 'U');
        $ownerValue = $this->dictionaryString($encryptionDictionary, 'O');

        if ($this->hash2B($password, substr($userValue, 32, 8), '') === substr($userValue, 0, 32)) {
            $intermediateKey = $this->hash2B($password, substr($userValue, 40, 8), '');

            return $this->aesNoPadDecrypt($this->dictionaryString($encryptionDictionary, 'UE'), $intermediateKey);
        }

        if ($this->hash2B($password, substr($ownerValue, 32, 8), $userValue) === substr($ownerValue, 0, 32)) {
            $intermediateKey = $this->hash2B($password, substr($ownerValue, 40, 8), $userValue);

            return $this->aesNoPadDecrypt($this->dictionaryString($encryptionDictionary, 'OE'), $intermediateKey);
        }

        throw CouldNotDecryptPdf::invalidPassword();
    }

    /**
     * Algorithm 2.B from ISO 32000-2 (the AES-256 R6 key-derivation hash).
     */
    protected function hash2B(#[SensitiveParameter] string $password, string $salt, string $userValue): string
    {
        $hash = hash('sha256', $password.$salt.$userValue, true);
        $round = 0;

        do {
            $block = str_repeat($password.$hash.$userValue, 64);

            $encrypted = openssl_encrypt(
                $block,
                'aes-128-cbc',
                substr($hash, 0, 16),
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                substr($hash, 16, 16),
            );

            $sum = 0;

            for ($index = 0; $index < 16; $index++) {
                $sum += ord($encrypted[$index]);
            }

            $algorithm = match ($sum % 3) {
                0 => 'sha256',
                1 => 'sha384',
                default => 'sha512',
            };

            $hash = hash($algorithm, $encrypted, true);
            $round++;
            $lastByte = ord($encrypted[strlen($encrypted) - 1]);
        } while (! ($round >= 64 && $lastByte <= ($round - 32)));

        return substr($hash, 0, 32);
    }

    protected function aesNoPadDecrypt(string $data, string $key): string
    {
        $decrypted = openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, self::ZERO_IV);

        return $decrypted === false ? '' : $decrypted;
    }

    protected function aesDecrypt(string $data, string $key): string
    {
        if (strlen($data) <= 16) {
            return '';
        }

        $initializationVector = substr($data, 0, 16);
        $cipherText = substr($data, 16);

        $plain = openssl_decrypt($cipherText, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $initializationVector);

        return $plain === false ? '' : $plain;
    }

    protected function encryptObjectNumber(string $pdf): int
    {
        if (! preg_match('/\/Encrypt\s+(\d+)\s+\d+\s+R/', $pdf, $matches)) {
            throw CouldNotDecryptPdf::notEncrypted();
        }

        return (int) $matches[1];
    }

    protected function dictionaryString(string $dictionary, string $key): string
    {
        if (! preg_match('/\/'.$key.'(?![A-Za-z])\s*\(/', $dictionary, $matches, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $parenthesisPosition = $matches[0][1] + strlen($matches[0][0]) - 1;

        [$raw] = $this->readLiteralString($dictionary, $parenthesisPosition);

        return $raw;
    }

    protected function dictionaryInteger(string $dictionary, string $key): int
    {
        preg_match('/\/'.$key.'(?![A-Za-z])\s+(-?\d+)/', $dictionary, $matches);

        return (int) ($matches[1] ?? 0);
    }

    protected function guardAgainstMissingPackage(): void
    {
        if (! class_exists(Encrypt::class)) {
            throw CouldNotEncryptPdf::packageNotInstalled();
        }
    }

    protected function guardAgainstUnsupportedStructure(string $pdf): void
    {
        if (preg_match('/\/Type\s*\/(ObjStm|XRef)/', $pdf)) {
            throw CouldNotEncryptPdf::unsupportedStructure();
        }

        if (! str_contains($pdf, 'trailer')) {
            throw CouldNotEncryptPdf::unsupportedStructure();
        }
    }

    protected function guardAgainstUnsupportedHandler(string $encryptionDictionary): void
    {
        if ($this->dictionaryInteger($encryptionDictionary, 'R') !== 6) {
            throw CouldNotDecryptPdf::unsupportedHandler();
        }
    }
}
