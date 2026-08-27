<?php
declare(strict_types=1);

class YearsStayedService
{
    public static function yearsSince(?string $date): ?int
    {
        if ($date === null || $date === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d) {
            return null;
        }
        $today = new DateTime('today');
        if ($d > $today) {
            return 0;
        }

        $years = (int) $today->format('Y') - (int) $d->format('Y');
        $beforeAnniversary =
            (int) $today->format('n') < (int) $d->format('n') ||
            ((int) $today->format('n') === (int) $d->format('n') &&
             (int) $today->format('j') < (int) $d->format('j'));

        if ($beforeAnniversary) {
            $years--;
        }

        return max(0, $years);
    }

    public static function formatYears(?string $date): string
    {
        $years = self::yearsSince($date);
        if ($years === null) {
            return '';
        }
        return $years . ' ' . ($years === 1 ? 'year' : 'years');
    }
}