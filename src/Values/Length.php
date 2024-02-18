<?php

namespace Spatie\LaravelPdf\Values;

use Spatie\LaravelPdf\Exceptions\CannotCalculateDifferentUnits;

class Length
{
    public function __construct(
        private readonly float $value,
        private readonly string $unit
    ) {}

    public static function make(float $value, string $unit): static {
        return new static($value, $unit);
    }

    public function plus(Length $length): Length {
        if ($length->unit !== $this->unit) {
            throw CannotCalculateDifferentUnits::new();
        }

        return new Length($this->value + $length->value, $length->unit);
    }

    public function isGreaterThan(Length $another): bool {
        return $this->toMeter()->value > $another->toMeter()->value;
    }

    public function toMeter(): Length {
        return new Length(
            value: $this->value * config("pdf.formulas.{$this->unit}"),
            unit: 'm'
        );
    }
}