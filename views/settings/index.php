<?php
"use strict";
$thisYear = $currentYear;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Settings</h5>
</div>

<ul class="nav nav-tabs mb-3" id="settingsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#statuses">Statuses</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#stages">Stages</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#priorities">Priorities</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sources">Sources</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#models">Models</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#colors">Colors</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#targets">Targets</a></li>
</ul>

<div class="tab-content">
    <!-- Statuses -->
    <div class="tab-pane fade show active" id="statuses">
        <div class="card section-card">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Lead Statuses
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addStatusModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-crm mb-0">
                    <thead><tr><th>Name</th><th>Color</th><th>Order</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($statuses as $s): ?>
                            <tr>
                                <td><?= Security::escape($s['name']) ?></td>
                                <td><span class="badge" style="background:<?= Security::escape($s['color'] ?? '#6c757d') ?>;color:#fff">&nbsp;</span> <?= Security::escape($s['color'] ?? '') ?></td>
                                <td><?= $s['sort_order'] ?></td>
                                <td><?= $s['active'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" onclick='editItem("status", <?= json_encode($s) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stages -->
    <div class="tab-pane fade" id="stages">
        <div class="card section-card">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Opportunity Stages
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addStageModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-crm mb-0">
                    <thead><tr><th>Name</th><th>Color</th><th>Order</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($stages as $s): ?>
                            <tr>
                                <td><?= Security::escape($s['name']) ?></td>
                                <td><span class="badge" style="background:<?= Security::escape($s['color'] ?? '#6c757d') ?>;color:#fff">&nbsp;</span></td>
                                <td><?= $s['sort_order'] ?></td>
                                <td><?= $s['active'] ? 'Yes' : 'No' ?></td>
                                <td><button class="btn btn-sm btn-outline-secondary" onclick='editItem("stage", <?= json_encode($s) ?>)'><i class="bi bi-pencil"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Priorities -->
    <div class="tab-pane fade" id="priorities">
        <div class="card section-card">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Priority Levels
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPriorityModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-crm mb-0">
                    <thead><tr><th>Name</th><th>Color</th><th>Level</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($priorities as $p): ?>
                            <tr>
                                <td><span class="badge priority-<?= strtolower($p['name']) ?>"><?= Security::escape($p['name']) ?></span></td>
                                <td><?= Security::escape($p['color'] ?? '') ?></td>
                                <td><?= $p['level'] ?></td>
                                <td><?= $p['active'] ? 'Yes' : 'No' ?></td>
                                <td><button class="btn btn-sm btn-outline-secondary" onclick='editItem("priority", <?= json_encode($p) ?>)'><i class="bi bi-pencil"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sources -->
    <div class="tab-pane fade" id="sources">
        <div class="card section-card">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Lead Sources
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSourceModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-crm mb-0">
                    <thead><tr><th>Name</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($sources as $s): ?>
                            <tr>
                                <td><?= Security::escape($s['name']) ?></td>
                                <td><?= $s['active'] ? 'Yes' : 'No' ?></td>
                                <td><button class="btn btn-sm btn-outline-secondary" onclick='editItem("source", <?= json_encode($s) ?>)'><i class="bi bi-pencil"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Models -->
    <div class="tab-pane fade" id="models">
        <div class="card section-card">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Vehicle Models
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addModelModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-crm mb-0">
                    <thead><tr><th>Name</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($models as $m): ?>
                            <tr>
                                <td><?= Security::escape($m['name']) ?></td>
                                <td><?= $m['active'] ? 'Yes' : 'No' ?></td>
                                <td><button class="btn btn-sm btn-outline-secondary" onclick='editItem("model", <?= json_encode($m) ?>)'><i class="bi bi-pencil"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Colors -->
    <div class="tab-pane fade" id="colors">
        <div class="card section-card">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Vehicle Colors
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addColorModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-crm mb-0">
                    <thead><tr><th>Name</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($colors as $c): ?>
                            <tr>
                                <td><?= Security::escape($c['name']) ?></td>
                                <td><?= $c['active'] ? 'Yes' : 'No' ?></td>
                                <td><button class="btn btn-sm btn-outline-secondary" onclick='editItem("color", <?= json_encode($c) ?>)'><i class="bi bi-pencil"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Targets -->
    <div class="tab-pane fade" id="targets">
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold">Sales Targets</div>
            <div class="card-body">
                <form method="POST" action="<?= Url::route('settings/targets/store') ?>" class="row g-2 align-items-end mb-3" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
                    <?= Security::csrfField() ?>
                    <div class="col-auto">
                        <select name="year" class="form-select form-select-sm">
                            <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="month" class="form-select form-select-sm">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="number" name="target" class="form-control form-control-sm" placeholder="Target" min="0" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-danger">Save</button>
                    </div>
                </form>

                <?php if (!empty($salesTargets)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-crm">
                            <thead><tr><th>Month</th><th>Target</th><th style="width:120px"></th></tr></thead>
                            <tbody>
                                <?php foreach ($salesTargets as $t): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= date('F Y', mktime(0, 0, 0, $t['month'], 1, $t['year'])) ?></td>
                                        <td><?= $t['target'] ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1" onclick='editSalesTarget(<?= json_encode($t) ?>)'><i class="bi bi-pencil"></i></button>
                                            <form method="POST" action="<?= Url::route('settings/targets/delete') ?>" class="d-inline" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this sales target?')"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">No sales targets configured yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card section-card">
            <div class="card-header fw-semibold">Lead Generation Targets</div>
            <div class="card-body">
                <form method="POST" action="<?= Url::route('settings/lead-targets/store') ?>" class="row g-2 align-items-end mb-3" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
                    <?= Security::csrfField() ?>
                    <div class="col-auto">
                        <select name="year" class="form-select form-select-sm">
                            <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="month" class="form-select form-select-sm">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="source_id" class="form-select form-select-sm">
                            <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"><?= Security::escape($src['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="number" name="target" class="form-control form-control-sm" placeholder="Target" min="0" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-danger">Save</button>
                    </div>
                </form>

                <?php if (!empty($leadGenTargets)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-crm">
                            <thead><tr><th>Month</th><th>Source</th><th>Target</th><th style="width:120px"></th></tr></thead>
                            <tbody>
                                <?php foreach ($leadGenTargets as $lgt): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= date('F Y', mktime(0, 0, 0, $lgt['month'], 1, $lgt['year'])) ?></td>
                                        <td><?= Security::escape($lgt['source_name']) ?></td>
                                        <td><?= $lgt['target'] ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1" onclick='editLeadGenTarget(<?= json_encode($lgt) ?>)'><i class="bi bi-pencil"></i></button>
                                            <form method="POST" action="<?= Url::route('settings/lead-targets/delete') ?>" class="d-inline" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $lgt['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this lead generation target?')"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">No lead generation targets configured yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addStatusModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/statuses/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Add Status</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            <input type="color" name="color" value="#6c757d" class="form-control form-control-color">
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Save</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addStageModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/stages/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Add Stage</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            <input type="color" name="color" value="#6c757d" class="form-control form-control-color">
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Save</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addPriorityModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/priorities/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Add Priority</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            <input type="color" name="color" value="#6c757d" class="form-control form-control-color">
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Save</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addSourceModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/sources/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Add Source</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="name" class="form-control" placeholder="Name" required>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Save</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addModelModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/models/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Add Model</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="name" class="form-control" placeholder="Model name" required>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Save</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addColorModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/colors/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Add Color</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="name" class="form-control" placeholder="Color name" required>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Save</button></div>
    </form>
</div></div></div>

<!-- Edit Modal (generic) -->
<div class="modal fade" id="editItemModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form id="editItemForm" method="POST" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title" id="editItemTitle">Edit</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editItemId">
            <input type="text" name="name" class="form-control mb-2" id="editItemName" placeholder="Name" required>
            <div id="editColorField" class="mb-2">
                <input type="color" name="color" class="form-control form-control-color" id="editItemColor">
            </div>
            <div class="form-check mb-2">
                <input type="checkbox" name="active" class="form-check-input" id="editItemActive" value="1">
                <label class="form-check-label" for="editItemActive">Active</label>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Update</button></div>
    </form>
</div></div></div>

<!-- Edit Sales Target Modal -->
<div class="modal fade" id="editSalesTargetModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/targets/update') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Edit Sales Target</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editSalesTargetId">
            <div class="mb-2">
                <label class="form-label small">Month</label>
                <input type="text" class="form-control form-control-sm" id="editSalesTargetMonth" disabled>
            </div>
            <div>
                <label class="form-label small">Target</label>
                <input type="number" name="target" class="form-control form-control-sm" id="editSalesTargetValue" min="0" required>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Update</button></div>
    </form>
</div></div></div>

<!-- Edit Lead Gen Target Modal -->
<div class="modal fade" id="editLeadGenTargetModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <form method="POST" action="<?= Url::route('settings/lead-targets/update') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
        <?= Security::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Edit Lead Gen Target</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editLeadGenTargetId">
            <div class="mb-2">
                <label class="form-label small">Month</label>
                <input type="text" class="form-control form-control-sm" id="editLeadGenTargetMonth" disabled>
            </div>
            <div class="mb-2">
                <label class="form-label small">Source</label>
                <input type="text" class="form-control form-control-sm" id="editLeadGenTargetSource" disabled>
            </div>
            <div>
                <label class="form-label small">Target</label>
                <input type="number" name="target" class="form-control form-control-sm" id="editLeadGenTargetValue" min="0" required>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Update</button></div>
    </form>
</div></div></div>

<script>
function editItem(type, item) {
    const actionMap = {
        status: '<?= Url::route("settings/statuses/update") ?>',
        stage: '<?= Url::route("settings/stages/update") ?>',
        priority: '<?= Url::route("settings/priorities/update") ?>',
        source: '<?= Url::route("settings/sources/update") ?>',
        model: '<?= Url::route("settings/models/update") ?>',
        color: '<?= Url::route("settings/colors/update") ?>',
    };
    
    document.getElementById('editItemForm').action = actionMap[type];
    document.getElementById('editItemTitle').textContent = 'Edit ' + type.charAt(0).toUpperCase() + type.slice(1);
    document.getElementById('editItemId').value = item.id;
    document.getElementById('editItemName').value = item.name || '';
    document.getElementById('editItemActive').checked = item.active == 1;
    
    const colorField = document.getElementById('editColorField');
    if (['status', 'stage', 'priority'].includes(type)) {
        colorField.style.display = 'block';
        document.getElementById('editItemColor').value = item.color || '#6c757d';
    } else {
        colorField.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function editSalesTarget(t) {
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('editSalesTargetId').value = t.id;
    document.getElementById('editSalesTargetMonth').value = months[t.month - 1] + ' ' + t.year;
    document.getElementById('editSalesTargetValue').value = t.target;
    new bootstrap.Modal(document.getElementById('editSalesTargetModal')).show();
}

function editLeadGenTarget(lgt) {
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('editLeadGenTargetId').value = lgt.id;
    document.getElementById('editLeadGenTargetMonth').value = months[lgt.month - 1] + ' ' + lgt.year;
    document.getElementById('editLeadGenTargetSource').value = lgt.source_name;
    document.getElementById('editLeadGenTargetValue').value = lgt.target;
    new bootstrap.Modal(document.getElementById('editLeadGenTargetModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    if (hash) {
        var tab = document.querySelector('.nav-tabs a[href="' + hash + '"]');
        if (tab) {
            new bootstrap.Tab(tab).show();
        }
    }
    document.querySelectorAll('.nav-tabs a[data-bs-toggle="tab"]').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            history.replaceState(null, null, e.target.getAttribute('href'));
        });
    });
});
</script>
