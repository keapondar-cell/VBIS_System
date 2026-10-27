<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final class SchoolTime
{
    public const TIMEZONE = 'Asia/Manila';

    public static function format(DateTimeInterface|string|null $value, string $format, string $fallback = 'N/A'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        $date = $value instanceof DateTimeInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value, 'UTC');

        return $date->setTimezone(config('app.display_timezone', self::TIMEZONE))->format($format);
    }

    public static function normalizeInput(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?$/i', $value)) {
            return $value;
        }

        $hasTimezone = preg_match('/(?:Z|[+-]\d{2}:\d{2})$/i', $value) === 1;
        $date = CarbonImmutable::parse($value, $hasTimezone ? 'UTC' : self::TIMEZONE);

        return $date->utc()->format('Y-m-d H:i:s');
    }

    public static function isFutureInput(mixed $value): bool
    {
        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        $normalized = self::normalizeInput($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
            return $normalized > now(self::TIMEZONE)->toDateString();
        }

        if (! is_string($normalized) || $normalized === $value) {
            return false;
        }

        return CarbonImmutable::parse($normalized, 'UTC')->isAfter(now('UTC'));
    }
}
