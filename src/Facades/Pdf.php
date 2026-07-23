<?php

namespace Spatie\LaravelPdf\Facades;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Response;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Facade;
use Spatie\LaravelPdf\FakePdfBuilder;
use Spatie\LaravelPdf\PdfBuilder;
use Spatie\LaravelPdf\PdfFactory;
use Spatie\LaravelPdf\Drivers\PdfDriver;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Enums\Permission;
use Spatie\LaravelPdf\Enums\Unit;

/**
 * @method static string     decrypt(string $pathOrContents, string $password)
 * @method static PdfBuilder setDriver(PdfDriver $driver)
 * @method static PdfBuilder driver(string $driverName)
 * @method static PdfBuilder view(string $view, array $data = [])
 * @method static PdfBuilder headerView(string $view, array $data = [])
 * @method static PdfBuilder footerView(string $view, array $data = [])
 * @method static PdfBuilder landscape()
 * @method static PdfBuilder portrait()
 * @method static PdfBuilder orientation(string|Orientation $orientation)
 * @method static PdfBuilder inline(string $downloadName = '')
 * @method static PdfBuilder html(string $html)
 * @method static PdfBuilder headerHtml(string $html)
 * @method static PdfBuilder footerHtml(string $html)
 * @method static PdfBuilder download(?string $downloadName = null)
 * @method static PdfBuilder headers(array $headers)
 * @method static PdfBuilder name(string $downloadName)
 * @method static string     base64()
 * @method static PdfBuilder margins(float $top = 0, float $right = 0, float $bottom = 0, float $left = 0, Unit|string $unit = 'mm')
 * @method static PdfBuilder format(string|Format $format)
 * @method static PdfBuilder paperSize(float $width, float $height, Unit|string $unit = 'mm')
 * @method static PdfBuilder scale(float $scale)
 * @method static PdfBuilder pageRanges(string $pageRanges)
 * @method static PdfBuilder tagged()
 * @method static PdfBuilder waitUntilReady(?string $expression = null, ?int $timeout = null)
 * @method static PdfBuilder cache(DateTimeInterface|DateInterval|int|null $ttl = null, ?string $key = null)
 * @method static PdfBuilder dontCache()
 * @method static PdfBuilder meta(?string $title = null, ?string $author = null, ?string $subject = null, ?string $keywords = null, ?string $creator = null, string|DateTimeInterface|null $creationDate = null)
 * @method static PdfBuilder encrypt(string $userPassword = '', ?string $ownerPassword = null, ?array $permissions = null)
 * @method static PdfBuilder withBrowsershot(callable $callback)
 * @method static ?Closure   getCustomizeBrowsershotCallback()
 * @method static PdfBuilder onLambda()
 * @method static PdfBuilder save(string $path)
 * @method static QueuedPdfResponse saveQueued(string $path, ?string $connection = null, ?string $queue = null)
 * @method static PdfBuilder disk(string $diskName, string $visibility = 'private')
 * @method static string     getHtml()
 * @method static ?string    getHeaderHtml()
 * @method static ?string    getFooterHtml()
 * @method static string     generatePdfContent()
 * @method static Response   toResponse($request)
 * @method static Attachment toMailAttachment()
 * @method static bool       isInline()
 * @method static bool       isDownload()
 * @method static bool       contains(string|array $text)
 * 
 * @mixin PdfBuilder
 * @mixin FakePdfBuilder
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PdfFactory::class;
    }

    public static function fake(): FakePdfBuilder
    {
        $fake = new FakePdfBuilder;

        if ($callback = PdfFactory::defaultBuilder()->getCustomizeBrowsershotCallback()) {
            $fake->withBrowsershot($callback);
        }

        static::swap($fake);

        return $fake;
    }
}
