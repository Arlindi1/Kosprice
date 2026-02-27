<?php

namespace Database\Seeders\Kosovo\Support;

final class SeededRng
{
    public function __construct(private readonly int $seed)
    {
    }

    public function float(string $key, float $min, float $max): float
    {
        $hash = sprintf('%u', crc32($this->seed.'|'.$key));
        $normalized = ((float) $hash) / 4294967295.0;

        return $min + (($max - $min) * $normalized);
    }
}
