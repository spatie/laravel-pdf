<?php

namespace Spatie\LaravelPdf\Commands;

use Illuminate\Console\Command;
use Spatie\LaravelPdf\PdfOptions;
use Spatie\LaravelPdf\PdfServiceProvider;
use Throwable;

class PdfHealthCommand extends Command
{
    protected $signature = 'pdf:health';

    protected $description = 'Check the health of the configured PDF driver and all fallback drivers';

    public function handle(): int
    {
        $primary = config('laravel-pdf.driver', 'browsershot');
        $fallbacks = config('laravel-pdf.fallback.drivers', []);

        $names = array_values(array_unique([$primary, ...$fallbacks]));

        $this->newLine();
        $this->components->info('Checking PDF driver health');

        $failed = 0;

        foreach ($names as $name) {
            $row = $this->checkDriver($name, $primary);
            $this->renderRow($row);

            if (! $row['healthy']) {
                $failed++;
            }
        }

        $this->newLine();

        $total = count($names);

        if ($failed === 0) {
            $this->info("All {$total} driver(s) are healthy.");

            return self::SUCCESS;
        }

        $this->error("{$failed} of {$total} driver(s) failed.");

        return self::FAILURE;
    }

    /**
     * @return array{name: string, role: string, healthy: bool, time: string, error: ?string}
     */
    protected function checkDriver(string $name, string $primary): array
    {
        $role = $name === $primary ? 'primary' : 'fallback';
        $start = hrtime(true);

        try {
            $driver = PdfServiceProvider::resolveDriverByName($name);
            $driver->generatePdf('<h1>Hello World</h1>', null, null, new PdfOptions);

            return [
                'name' => $name,
                'role' => $role,
                'healthy' => true,
                'time' => $this->formatElapsed($start),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'name' => $name,
                'role' => $role,
                'healthy' => false,
                'time' => $this->formatElapsed($start),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array{name: string, role: string, healthy: bool, time: string, error: ?string}  $row
     */
    protected function renderRow(array $row): void
    {
        $label = "{$row['name']} <fg=gray>({$row['role']})</>";
        $status = $row['healthy']
            ? "<info>healthy</info> <fg=gray>{$row['time']}</>"
            : "<error>failed</error> <fg=gray>{$row['time']}</> — {$row['error']}";

        $this->components->twoColumnDetail($label, $status);
    }

    protected function formatElapsed(int $start): string
    {
        return sprintf('%.0fms', (hrtime(true) - $start) / 1e6);
    }
}
