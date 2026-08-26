<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';

class WorkingDaysService
{
    private SettingsModel $settings;
    private PDO $db;

    public function __construct()
    {
        $this->settings = new SettingsModel();
        $this->db = Database::getConnection();
    }

    public function isWorkingDay(string $date): bool
    {
        $dayOfWeek = date('l', strtotime($date));

        $holidays = $this->settings->getHolidays((int) date('Y', strtotime($date)));
        foreach ($holidays as $holiday) {
            if ($holiday['holiday_date'] === $date) {
                return false;
            }
        }

        $workingDays = $this->settings->getWorkingDays();
        if (!empty($workingDays)) {
            foreach ($workingDays as $wd) {
                if ($wd['day_of_week'] === $dayOfWeek) {
                    return (bool) $wd['is_working'];
                }
            }
        }

        return in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
    }

    public function countWorkingDays(string $startDate, string $endDate): int
    {
        $count = 0;
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);

        while ($current <= $end) {
            if ($this->isWorkingDay($current->format('Y-m-d'))) {
                $count++;
            }
            $current->modify('+1 day');
        }

        return $count;
    }

    public function remainingWorkingDaysInMonth(int $year, int $month): int
    {
        $today = new DateTime(date('Y-m-d'));
        $endOfMonth = new DateTime("{$year}-{$month}-" . date('t', mktime(0, 0, 0, $month, 1, $year)));

        if ($today > $endOfMonth) {
            return 0;
        }

        return $this->countWorkingDays($today->format('Y-m-d'), $endOfMonth->format('Y-m-d'));
    }

    public function getWorkingDaysConfig(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $config = $this->settings->getWorkingDays();
        $result = [];

        foreach ($days as $day) {
            $found = false;
            foreach ($config as $c) {
                if ($c['day_of_week'] === $day) {
                    $result[$day] = (bool) $c['is_working'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[$day] = in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            }
        }

        return $result;
    }
}
