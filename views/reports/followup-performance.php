<?php
"use strict"; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('reports') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Follow-up Performance</h5>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card section-card text-center">
            <div class="card-body py-4">
                <div class="fs-2 fw-bold text-danger"><?= $overdue['total'] ?></div>
                <div class="text-muted">Overdue</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card section-card text-center">
            <div class="card-body py-4">
                <div class="fs-2 fw-bold text-warning"><?= $dueToday['total'] ?></div>
                <div class="text-muted">Due Today</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card section-card text-center">
            <div class="card-body py-4">
                <div class="fs-2 fw-bold text-secondary"><?= $noFollowup ?></div>
                <div class="text-muted">No Follow-up Set</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($overdue['leads'])): ?>
    <div class="card section-card mt-3">
        <div class="card-header bg-danger bg-opacity-10 text-danger fw-semibold">Overdue Leads</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-crm mb-0">
                    <thead>
                        <tr><th>Lead</th><th>Model</th><th>Next Step</th><th>Due Date</th><th>Days Overdue</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdue['leads'] as $lead): ?>
                            <tr class="cursor-pointer" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                                <td>
                                    <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                    <div class="text-muted small"><?= Security::escape($lead['lead_id']) ?></div>
                                </td>
                                <td><?= Security::escape($lead['model_name'] ?? '-') ?></td>
                                <td><?= Security::escape($lead['next_step'] ?? '-') ?></td>
                                <td class="text-danger"><?= date('M d', strtotime($lead['next_step_date'])) ?></td>
                                <td><?= FollowUpService::calculateDaysOverdue($lead['next_step_date']) ?? '-' ?>d</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
