<?php

namespace Tests\Unit;

use App\Support\SchoolTime;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class SchoolTimeTest extends TestCase
{
    public function test_utc_timestamp_formats_in_philippine_time(): void
    {
        $timestamp = CarbonImmutable::parse('2026-10-25 01:15:00', 'UTC');

        $this->assertSame('Oct 25, 2026 09:15 AM', SchoolTime::format($timestamp, 'M d, Y h:i A'));
    }

    public function test_local_datetime_input_is_normalized_to_utc_and_date_only_stays_unshifted(): void
    {
        $this->assertSame(
            '2026-10-25 01:15:00',
            SchoolTime::normalizeInput('2026-10-25T09:15')
        );
        $this->assertSame('2026-10-25', SchoolTime::normalizeInput('2026-10-25'));
    }

    public function test_future_validation_uses_philippine_local_time_and_dates(): void
    {
        $this->assertTrue(SchoolTime::isFutureInput(now(SchoolTime::TIMEZONE)->addHours(2)->format('Y-m-d\TH:i')));
        $this->assertTrue(SchoolTime::isFutureInput(now(SchoolTime::TIMEZONE)->addDay()->toDateString()));
        $this->assertFalse(SchoolTime::isFutureInput(now(SchoolTime::TIMEZONE)->toDateString()));
    }
}
