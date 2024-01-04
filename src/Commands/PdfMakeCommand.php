<?php

namespace Spatie\LaravelPdf\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:pdf')]
class PdfMakeCommand extends GeneratorCommand
{
    protected $name = 'make:pdf';

    protected $description = 'Create a new PDF class.';

    protected $type = 'PDF';

    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        $this->writeView();
    }

    protected function writeView(): void
    {
        $path = $this->viewPath(
            str_replace('.', '/', 'pdf.' . $this->getView()) . '.blade.php'
        );

        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        if ($this->files->exists($path) && !$this->option('force')) {
            $this->components->error('View already exists.');

            return;
        }

        file_put_contents(
            $path,
            '<div>
    <!-- ' . Inspiring::quotes()->random() . ' -->
</div>'
        );
    }

    protected function buildClass($name)
    {
        return str_replace(
            '{{ view }}',
            'view(\'pdf.' . $this->getView() . '\')',
            parent::buildClass($name)
        );
    }

    protected function getView(): string
    {
        $name = str_replace('\\', '/', $this->argument('name'));

        return collect(explode('/', $name))
            ->map(function ($part) {
                return Str::kebab($part);
            })
            ->implode('.');
    }

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/pdf.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../..' . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Pdf';
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the PDF already exists'],
        ];
    }
}
