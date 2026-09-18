<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

/**
 * Shared timestamp formatting for server-generated exports (CSV and friends).
 *
 * Timestamps are stored and serialized as UTC. The browser renders them in the
 * viewer's local timezone, so an export has to do the same or the file and the
 * screen disagree. The client sends its IANA zone as `tz`.
 */
trait FormatsExportTimestamps
{
    protected function resolveTimezone(?string $tz): string
    {
        if ($tz && in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
            return $tz;
        }

        return config('app.display_timezone') ?: config('app.timezone');
    }

    protected function localDateTime($value, string $tz, string $format = 'Y-m-d h:i A'): string
    {
        if (empty($value)) {
            return '—';
        }

        return Carbon::parse($value, 'UTC')->setTimezone($tz)->format($format);
    }
}
