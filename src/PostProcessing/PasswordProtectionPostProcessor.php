<?php

namespace Spatie\LaravelPdf\PostProcessing;

use setasign\FpdiProtection\FpdiProtection;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PasswordProtectionPostProcessor implements PdfPostProcessor
{
    public function __construct(
        protected string $password,
    ) {}

    public function process(string $pdf): string
    {
        $temporaryDirectory = (new TemporaryDirectory)->create();

        try {
            $sourcePath = $temporaryDirectory->path('source.pdf');

            file_put_contents($sourcePath, $pdf);

            $protectedPdf = new FpdiProtection(useArcfourFallback: true);
            $protectedPdf->setProtection($this->permissions(), $this->password);

            $pageCount = $protectedPdf->setSourceFile($sourcePath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $protectedPdf->importPage($page);
                $size = $protectedPdf->getTemplateSize($templateId);

                $protectedPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $protectedPdf->useTemplate($templateId);
            }

            return $protectedPdf->Output('S');
        } finally {
            $temporaryDirectory->delete();
        }
    }

    protected function permissions(): array
    {
        return [
            FpdiProtection::PERM_PRINT,
            FpdiProtection::PERM_MODIFY,
            FpdiProtection::PERM_COPY,
            FpdiProtection::PERM_ANNOT,
            FpdiProtection::PERM_FILL_FORM,
            FpdiProtection::PERM_ACCESSIBILITY,
            FpdiProtection::PERM_ASSEMBLE,
            FpdiProtection::PERM_DIGITAL_PRINT,
        ];
    }
}
