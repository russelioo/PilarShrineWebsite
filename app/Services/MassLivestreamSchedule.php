<?php

namespace App\Services;

use App\Models\MassSchedule;
use Carbon\CarbonImmutable;

class MassLivestreamSchedule
{
    public const TIMEZONE = 'Asia/Manila';

    public const MINUTES_BEFORE = 5;

    public const MINUTES_AFTER_START = 80;

    /** @return array{schedule: MassSchedule, opens_at: CarbonImmutable, closes_at: CarbonImmutable}|null */
    public function currentWindow(CarbonImmutable $now): ?array
    {
        $now = $now->setTimezone(self::TIMEZONE);
        $schedules = MassSchedule::query()->where('is_active', true)->where('is_livestreamed', true)
            ->orderBy('start_time')->orderBy('id')->get();

        // Include the adjacent days for broadcasts whose windows cross midnight.
        foreach ([-1, 0, 1] as $offset) {
            $day = $now->startOfDay()->addDays($offset);
            foreach ($schedules as $schedule) {
                if (! $this->occursOn($schedule->day_of_week, $day)
                    || ! preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', (string) $schedule->start_time)) {
                    continue;
                }

                $start = $day->setTimeFromTimeString($schedule->start_time);
                $opensAt = $start->subMinutes(self::MINUTES_BEFORE);
                $closesAt = $start->addMinutes(self::MINUTES_AFTER_START);
                if ($now->greaterThanOrEqualTo($opensAt) && $now->lessThan($closesAt)) {
                    return ['schedule' => $schedule, 'opens_at' => $opensAt, 'closes_at' => $closesAt];
                }
            }
        }

        return null;
    }

    private function occursOn(string $frequency, CarbonImmutable $day): bool
    {
        $frequency = strtolower(trim(preg_replace('/\s+/', ' ', $frequency)));
        $weekdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $weekdayPattern = implode('|', $weekdays);

        if (in_array($frequency, ['daily', 'every day'], true)) {
            return true;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $frequency)) {
            return $frequency === $day->toDateString();
        }

        if (preg_match('/^(?:every )?(\d{1,2})(?:st|nd|rd|th)? of (?:the )?month$/', $frequency, $match)) {
            return (int) $match[1] === $day->day;
        }

        if (preg_match('/^(?:every )?(first|second|third|fourth|fifth|last) ('.$weekdayPattern.')(?: of (?:the )?month)?$/', $frequency, $match)) {
            $ordinal = ['first' => 1, 'second' => 2, 'third' => 3, 'fourth' => 4, 'fifth' => 5];

            return $match[2] === strtolower($day->format('l'))
                && ($match[1] === 'last'
                    ? $day->addWeek()->month !== $day->month
                    : (int) ceil($day->day / 7) === $ordinal[$match[1]]);
        }

        $frequency = preg_replace('/^anticipated (?:sunday )?mass \(('.$weekdayPattern.')\)$/', '$1', $frequency);
        $frequency = preg_replace('/^every /', '', $frequency);
        if (preg_match('/^('.$weekdayPattern.')(?: to |\s*[-–]\s*)('.$weekdayPattern.')$/u', $frequency, $match)) {
            $first = array_search($match[1], $weekdays, true);
            $last = array_search($match[2], $weekdays, true);

            return $first <= $last
                ? $day->dayOfWeek >= $first && $day->dayOfWeek <= $last
                : $day->dayOfWeek >= $first || $day->dayOfWeek <= $last;
        }

        // Only recognized weekday lists repeat weekly; free text must not go live by accident.
        $days = preg_split('/\s*(?:,|&|\band\b)\s*/', $frequency, flags: PREG_SPLIT_NO_EMPTY);

        return $days !== [] && array_diff($days, $weekdays) === []
            && in_array(strtolower($day->format('l')), $days, true);
    }
}
