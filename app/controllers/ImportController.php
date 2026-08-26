<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/LeadModel.php';
require_once dirname(__DIR__, 2) . '/app/models/SettingsModel.php';

class ImportController
{
    public function index(): void
    {
        Security::requireAuth();
        $activePage = 'imports';
        Response::view('imports.index', compact('activePage'));
    }

    public function upload(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Response::error('Please upload a valid CSV file.');
            return;
        }

        $file = $_FILES['csv_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['csv'])) {
            Response::error('Only CSV files are supported.');
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            Response::error('File size must be less than 10MB.');
            return;
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, ['text/csv', 'text/plain', 'application/csv', 'text/x-csv', 'text/x-comma-separated-values'])) {
            Response::error('Invalid file type.');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Response::error('Unable to read the uploaded file.');
            return;
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            Response::error('Unable to read CSV headers.');
            return;
        }

        $rows = [];
        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if ($rowNum > 10000) {
                fclose($handle);
                Response::error('File exceeds maximum row limit (10,000).');
                return;
            }
            $rows[] = $row;
        }
        fclose($handle);

        // Store temp data in session
        $_SESSION['import_headers'] = $headers;
        $_SESSION['import_rows'] = $rows;
        $_SESSION['import_filename'] = $file['name'];

        Response::success('File uploaded. Please map columns.', [
            'headers' => $headers,
            'row_count' => count($rows),
            'sample_rows' => array_slice($rows, 0, 3),
        ]);
    }

    public function process(): void
    {
        Security::requireAuth();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            Response::error('Invalid form submission.');
            return;
        }

        if (empty($_SESSION['import_rows'])) {
            Response::error('No import data found. Please upload a file again.');
            return;
        }

        $mapping = json_decode($_POST['mapping'] ?? '{}', true);
        if (empty($mapping)) {
            Response::error('Column mapping is required.');
            return;
        }

        $settingsModel = new SettingsModel();
        $leadModel = new LeadModel();
        $db = Database::getConnection();

        $statuses = $this->buildLookup($settingsModel->getStatuses(), 'name');
        $stages = $this->buildLookup($settingsModel->getStages(), 'name');
        $priorities = $this->buildLookup($settingsModel->getPriorities(), 'name');
        $sources = $settingsModel->getSources();
        $sourceLookup = $this->buildLookup($sources, 'name');
        $models = $this->buildLookup($settingsModel->getModels(), 'name');
        $colors = $this->buildLookup($settingsModel->getColors(), 'name');

        $rows = $_SESSION['import_rows'];
        $headers = $_SESSION['import_headers'];

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        $db->beginTransaction();

        try {
            foreach ($rows as $idx => $row) {
                $rowNum = $idx + 2;
                $data = [];

                foreach ($mapping as $dbField => $colIndex) {
                    if ($colIndex === '' || $colIndex === null) continue;
                    $colIndex = (int) $colIndex;
                    if (!isset($row[$colIndex])) continue;
                    $data[$dbField] = trim($row[$colIndex]);
                }

                if (empty($data['lead_name'])) {
                    $errors[] = "Row {$rowNum}: Lead name is required. Skipped.";
                    $skipped++;
                    continue;
                }

                // Resolve lookups
                if (!empty($data['status']) && isset($statuses[$data['status']])) {
                    $data['status_id'] = $statuses[$data['status']];
                } else {
                    $data['status_id'] = null;
                }
                unset($data['status']);

                if (!empty($data['opportunity_stage']) && isset($stages[$data['opportunity_stage']])) {
                    $data['opportunity_stage_id'] = $stages[$data['opportunity_stage']];
                } else {
                    $data['opportunity_stage_id'] = null;
                }
                unset($data['opportunity_stage']);

                if (!empty($data['priority']) && isset($priorities[$data['priority']])) {
                    $data['priority_id'] = $priorities[$data['priority']];
                } else {
                    $data['priority_id'] = null;
                }
                unset($data['priority']);

                if (!empty($data['source']) && isset($sourceLookup[$data['source']])) {
                    $data['source_id'] = $sourceLookup[$data['source']];
                } else {
                    $data['source_id'] = null;
                }
                unset($data['source']);

                if (!empty($data['model']) && isset($models[$data['model']])) {
                    $data['model_id'] = $models[$data['model']];
                } else {
                    $data['model_id'] = null;
                }
                unset($data['model']);

                if (!empty($data['vehicle_color']) && isset($colors[$data['vehicle_color']])) {
                    $data['color_id'] = $colors[$data['vehicle_color']];
                } else {
                    $data['color_id'] = null;
                }
                unset($data['vehicle_color']);

                // Skip imported lead_id (auto-generate), follow-up status, days since last contact
                unset($data['imported_lead_id']);
                unset($data['follow_up_status']);
                unset($data['days_since_last_contact']);

                // Generate lead ID
                $data['lead_id'] = $leadModel->generateLeadId();
                $data['archived'] = 0;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');

                $leadModel->create($data);
                $imported++;
            }

            $db->commit();
            unset($_SESSION['import_rows'], $_SESSION['import_headers']);

            Response::success('Import completed.', [
                'imported' => $imported,
                'skipped' => $skipped,
                'failed' => $failed,
                'errors' => $errors,
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Import error: " . $e->getMessage());
            Response::error('Import failed due to a server error. No data was saved.');
        }
    }

    private function buildLookup(array $items, string $key): array
    {
        $lookup = [];
        foreach ($items as $item) {
            $lookup[$item[$key]] = $item['id'];
        }
        return $lookup;
    }
}
