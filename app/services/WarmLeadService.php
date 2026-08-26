<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/services/WorkingDaysService.php';

class WarmLeadService
{
    public static function isWarmOrHot(array $lead): bool
    {
        if (!empty($lead['priority_name']) && $lead['priority_name'] === 'High') {
            return true;
        }

        $stageName = $lead['stage_name'] ?? '';
        $advancedStages = ['Test Drive', 'Financing Appli', 'Approved', 'Booked', 'Reserved', 'With PO', 'Downpayment'];
        if (in_array($stageName, $advancedStages)) {
            return true;
        }

        if (!empty($lead['next_step_date']) && $lead['next_step_date'] <= date('Y-m-d')) {
            return true;
        }

        return false;
    }

    public static function getReleaseWatchStages(): array
    {
        return ['Approved', 'Booked', 'Reserved', 'With PO', 'Downpayment'];
    }
}
