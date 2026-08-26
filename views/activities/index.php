<?php
$monthName = date('F', mktime(0, 0, 0, $month, 1));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Activities</h5>
    <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="route" value="activities">
        <select name="month" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="card section-card">
    <div class="card-header">
        <i class="bi bi-calendar-check me-2"></i><?= $monthName ?> <?= $year ?> Activities (<?= count($activities) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($activities)): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x d-block"></i>
                <p>No activities recorded for this month.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-crm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Lead</th>
                            <th>Type</th>
                            <th>Notes</th>
                            <th>Next Step</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td class="text-nowrap"><?= date('M d', strtotime($activity['activity_date'])) ?></td>
                                <td>
                                    <a href="<?= Url::route('leads/' . $activity['lead_id']) ?>" class="text-decoration-none">
                                        <strong><?= Security::escape($activity['lead_name']) ?></strong>
                                        <div class="text-muted small"><?= Security::escape($activity['lead_code']) ?></div>
                                    </a>
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= Security::escape($activity['activity_type']) ?></span></td>
                                <td class="small"><?= Security::escape(mb_strimwidth($activity['notes'] ?? '-', 0, 80)) ?></td>
                                <td class="small">
                                    <?= Security::escape($activity['next_step'] ?? '-') ?>
                                    <?php if ($activity['next_step_date']): ?>
                                        <div class="text-muted"><?= date('M d', strtotime($activity['next_step_date'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= Security::escape($activity['created_by_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
