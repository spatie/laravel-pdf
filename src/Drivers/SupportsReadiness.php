<?php

namespace Spatie\LaravelPdf\Drivers;

/**
 * Marker interface for drivers that can wait for an explicit readiness
 * signal (a JavaScript expression) before capturing the PDF.
 */
interface SupportsReadiness {}
