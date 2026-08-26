<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

class FollowUpService
{
    public static function calculateStatus(?string $nextStepDate): string
    {
        if (empty($nextStepDate)) {
            return 'No follow-up';
        }

        $today = date('Y-m-d');

        if ($nextStepDate < $today) {
            return 'Overdue';
        } elseif ($nextStepDate === $today) {
            return 'Due Today';
        } else {
            return 'Upcoming';
        }
    }

    public static function calculateDaysOverdue(?string $nextStepDate): ?int
    {
        if (empty($nextStepDate)) {
            return null;
        }

        $today = new DateTime(date('Y-m-d'));
        $due = new DateTime($nextStepDate);

        if ($due >= $today) {
            return null;
        }

        return (int) $today->diff($due)->days;
    }

    public static function daysSinceContact(?string $lastContactDate): ?int
    {
        if (empty($lastContactDate)) {
            return null;
        }

        $today = new DateTime(date('Y-m-d'));
        $lastContact = new DateTime($lastContactDate);

        return (int) $today->diff($lastContact)->days;
    }
}
