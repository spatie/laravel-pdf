<?php

namespace Spatie\LaravelPdf;

class PdfMetadata
{
    public function __construct(
        public ?string $title = null,
        public ?string $author = null,
        public ?string $subject = null,
        public ?string $keywords = null,
        public ?string $creator = null,
        public ?string $creationDate = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->title === null
            && $this->author === null
            && $this->subject === null
            && $this->keywords === null
            && $this->creator === null
            && $this->creationDate === null;
    }
}
