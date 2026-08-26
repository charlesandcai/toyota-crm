<?php
"use strict";
$possibleFields = [
    'lead_name' => 'Lead Name *',
    'company' => 'Company',
    'phone' => 'Phone',
    'email' => 'Email',
    'status' => 'Status',
    'opportunity_stage' => 'Opportunity Stage',
    'priority' => 'Priority',
    'source' => 'Source',
    'model' => 'Model',
    'vehicle_color' => 'Vehicle Color',
    'initial_contact_date' => 'Initial Contact Date',
    'last_contact_date' => 'Last Contact Date',
    'next_step' => 'Next Step',
    'next_step_date' => 'Next Step Date',
    'location' => 'Location',
    'notes' => 'Notes',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Import Leads</h5>
</div>

<!-- Step 1: Upload -->
<div id="step-upload" class="card section-card">
    <div class="card-header fw-semibold"><i class="bi bi-upload me-2"></i>Step 1: Upload CSV</div>
    <div class="card-body">
        <form id="uploadForm" enctype="multipart/form-data">
            <?= Security::csrfField() ?>
            <div class="mb-3">
                <input type="file" name="csv_file" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-upload me-1"></i> Upload File
            </button>
        </form>
    </div>
</div>

<!-- Step 2: Map Columns -->
<div id="step-map" class="card section-card mt-3" style="display:none">
    <div class="card-header fw-semibold"><i class="bi bi-diagram-3 me-2"></i>Step 2: Map Columns</div>
    <div class="card-body">
        <div class="alert alert-info small">
            <i class="bi bi-info-circle me-1"></i>
            Match your spreadsheet columns to CRM fields. Fields marked with * are recommended.
            "Follow-up Status" and "Days Since Last Contact" will be calculated automatically.
        </div>
        
        <div id="fileInfo" class="mb-3 small text-muted"></div>
        
        <form id="mapForm">
            <?= Security::csrfField() ?>
            <div class="table-responsive">
                <table class="table table-sm table-crm">
                    <thead>
                        <tr>
                            <th>CRM Field</th>
                            <th>Spreadsheet Column</th>
                        </tr>
                    </thead>
                    <tbody id="mappingBody"></tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-danger mt-2">
                <i class="bi bi-check-lg me-1"></i> Import Leads
            </button>
        </form>
    </div>
</div>

<!-- Step 3: Results -->
<div id="step-results" class="card section-card mt-3" style="display:none">
    <div class="card-header fw-semibold"><i class="bi bi-check-circle me-2"></i>Import Results</div>
    <div class="card-body" id="resultsBody"></div>
</div>

<script>
let csvHeaders = [];
let csvSampleRows = [];

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';

    try {
        const resp = await fetch('/crm-php/public/index.php?route=imports/upload', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json();

        if (data.success) {
            csvHeaders = data.data.headers;
            csvSampleRows = data.data.sample_rows || [];
            
            document.getElementById('fileInfo').textContent = `${data.data.row_count} rows detected from ${csvHeaders.length} columns.`;
            
            const tbody = document.getElementById('mappingBody');
            tbody.innerHTML = '';
            
            <?php foreach ($possibleFields as $field => $label): ?>
            {
                const tr = document.createElement('tr');
                const td1 = document.createElement('td');
                td1.innerHTML = '<strong><?= Security::escape($label) ?></>';
                const td2 = document.createElement('td');
                const select = document.createElement('select');
                select.name = 'mapping[<?= $field ?>]';
                select.className = 'form-select form-select-sm';
                
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = '-- Skip --';
                select.appendChild(opt0);
                
                csvHeaders.forEach(function(h, idx) {
                    const opt = document.createElement('option');
                    opt.value = idx;
                    opt.textContent = h;
                    if (h.toLowerCase().replace(/[^a-z]/g, '') === '<?= str_replace(['_'], [''], $field) ?>'.replace(/[^a-z]/g, '')) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                });
                
                td2.appendChild(select);
                tr.appendChild(td1);
                tr.appendChild(td2);
                tbody.appendChild(tr);
            }
            <?php endforeach; ?>

            document.getElementById('step-map').style.display = 'block';
            document.getElementById('step-map').scrollIntoView({ behavior: 'smooth' });
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Upload failed. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-upload me-1"></i> Upload File';
    }
});

document.getElementById('mapForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Importing...';

    const formData = new FormData(this);
    formData.append('_csrf_token', '<?= Security::generateCSRFToken() ?>');
    
    const mapping = {};
    for (const [key, val] of formData.entries()) {
        if (key.startsWith('mapping[')) {
            const field = key.replace('mapping[', '').replace(']', '');
            mapping[field] = val;
        }
    }

    try {
        const resp = await fetch('/crm-php/public/index.php?route=imports/process', {
            method: 'POST',
            body: new URLSearchParams({
                _csrf_token: '<?= Security::generateCSRFToken() ?>',
                mapping: JSON.stringify(mapping)
            }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });
        const data = await resp.json();

        const resultsDiv = document.getElementById('resultsBody');
        if (data.success) {
            resultsDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-1"></i> ${data.message}
                </div>
                <div class="row text-center g-3">
                    <div class="col"><div class="fs-4 fw-bold text-success">${data.data.imported}</div><div class="small text-muted">Imported</div></div>
                    <div class="col"><div class="fs-4 fw-bold text-warning">${data.data.skipped}</div><div class="small text-muted">Skipped</div></div>
                    <div class="col"><div class="fs-4 fw-bold text-danger">${data.data.failed}</div><div class="small text-muted">Failed</div></div>
                </div>
                ${data.data.errors && data.data.errors.length > 0 ? '<div class="mt-3"><strong>Errors:</strong><ul class="small">' + data.data.errors.map(e => '<li>'+e+'</li>').join('') + '</ul></div>' : ''}
                <a href="/crm-php/public/index.php?route=leads" class="btn btn-danger mt-3">View Leads</a>
            `;
        } else {
            resultsDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i> ${data.message}</div>`;
        }

        document.getElementById('step-results').style.display = 'block';
        document.getElementById('step-results').scrollIntoView({ behavior: 'smooth' });
    } catch (err) {
        showToast('Import failed. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Import Leads';
    }
});
</script>
