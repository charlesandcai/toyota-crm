<?php
$monthName = date('F', mktime(0, 0, 0, $month, 1));
?>

<!-- Date selector -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Dashboard</h5>
    <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="route" value="dashboard">
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

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="<?= Url::route('leads') ?>" class="text-decoration-none">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="kpi-value text-dark"><?= number_format($totalLeads) ?></div>
                        <div class="kpi-label">Total Leads</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= Url::route('pipeline') ?>" class="text-decoration-none">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <div class="kpi-value text-dark"><?= number_format($activeDeals) ?></div>
                        <div class="kpi-label">Active Deals</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= Url::route('leads') ?>?filter=hot" class="text-decoration-none">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-fire"></i>
                    </div>
                    <div>
                        <div class="kpi-value text-dark"><?= number_format($warmLeads) ?></div>
                        <div class="kpi-label">Warm Leads</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= Url::route('pipeline') ?>" class="text-decoration-none">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-rocket-takeoff"></i>
                    </div>
                    <div>
                        <div class="kpi-value text-dark"><?= number_format($dealsToClose) ?></div>
                        <div class="kpi-label">Deals to Close</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Sales Target -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bullseye me-2 text-danger"></i>Sales Target - <?= $monthName ?> <?= $year ?></span>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col">
                        <div class="text-muted small">Target</div>
                        <div class="fw-bold fs-5"><?= $salesData['target'] ?></div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Closed</div>
                        <div class="fw-bold fs-5 text-success"><?= $salesData['closed'] ?></div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Remaining</div>
                        <div class="fw-bold fs-5 text-danger"><?= $salesData['remaining'] ?></div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Working Days Left</div>
                        <div class="fw-bold fs-5"><?= $salesData['working_days_left'] ?></div>
                    </div>
                </div>
                <?php if ($salesData['target'] > 0): ?>
                    <?php $pct = min(100, round(($salesData['closed'] / $salesData['target']) * 100)); ?>
                    <div class="target-progress mb-2">
                        <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                    </div>
                    <small class="text-muted"><?= $pct ?>% of target achieved</small>
                <?php endif; ?>
                <hr>
                <div class="small">
                    <span class="text-muted">Leads Needed (est.): </span>
                    <strong><?= $salesData['leads_needed'] ?></strong>
                    <span class="text-muted ms-2">
                        (Closing ratio: <?= $salesData['closing_ratio'] > 0 ? round($salesData['closing_ratio'] * 100, 1) . '%' : 'N/A' ?>)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Generation -->
    <div class="col-lg-6">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-megaphone me-2 text-primary"></i>Lead Generation - <?= $monthName ?></span>
            </div>
            <div class="card-body" style="max-height:280px;overflow-y:auto">
                <table class="table table-sm table-crm mb-0">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th class="text-center">Target</th>
                            <th class="text-center">Actual</th>
                            <th class="text-center">Remaining</th>
                            <th class="text-center">Req/Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leadGenData as $src): ?>
                            <?php
                            $srcTarget = $leadGenTargets[$src['id']] ?? 0;
                            $srcActual = (int) $src['lead_count'];
                            $srcRemaining = max(0, $srcTarget - $srcActual);
                            $reqPerDay = $workingDaysLeft > 0 ? ceil($srcRemaining / $workingDaysLeft) : '-';
                            ?>
                            <tr>
                                <td><?= Security::escape($src['name']) ?></td>
                                <td class="text-center"><?= $srcTarget ?></td>
                                <td class="text-center fw-semibold"><?= $srcActual ?></td>
                                <td class="text-center"><?= $srcRemaining ?></td>
                                <td class="text-center">
                                    <?= is_int($reqPerDay) ? $reqPerDay : $reqPerDay ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Needs Attention -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card section-card">
            <div class="card-header bg-danger bg-opacity-10 text-danger d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Overdue (<?= count($overdueLeads['leads']) ?>)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($overdueLeads['leads'])): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-check-circle d-block"></i>
                        <p>No overdue follow-ups. You're caught up!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-crm mb-0">
                            <thead>
                                <tr><th>Lead</th><th>Model</th><th>Next Step</th><th>Due</th><th>Days</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueLeads['leads'] as $lead): ?>
                                    <tr class="cursor-pointer" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                                        <td>
                                            <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                            <div class="text-muted small"><?= Security::escape($lead['lead_id']) ?></div>
                                        </td>
                                        <td><?= Security::escape($lead['model_name'] ?? '-') ?></td>
                                        <td><?= Security::escape($lead['next_step'] ?? '-') ?></td>
                                        <td class="text-danger fw-semibold"><?= date('M d', strtotime($lead['next_step_date'])) ?></td>
                                        <td>
                                            <?php
                                            $daysOverdue = FollowUpService::calculateDaysOverdue($lead['next_step_date']);
                                            echo $daysOverdue !== null ? $daysOverdue . 'd' : '-';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card section-card">
            <div class="card-header bg-warning bg-opacity-10 text-warning d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock me-2"></i>Due Today (<?= count($dueTodayLeads['leads']) ?>)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($dueTodayLeads['leads'])): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-calendar-check d-block"></i>
                        <p>Nothing due today.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-crm mb-0">
                            <thead>
                                <tr><th>Lead</th><th>Model</th><th>Next Step</th><th>Priority</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dueTodayLeads['leads'] as $lead): ?>
                                    <tr class="cursor-pointer" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                                        <td>
                                            <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                            <div class="text-muted small"><?= Security::escape($lead['lead_id']) ?></div>
                                        </td>
                                        <td><?= Security::escape($lead['model_name'] ?? '-') ?></td>
                                        <td><?= Security::escape($lead['next_step'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($lead['priority_name']): ?>
                                                <span class="badge priority-<?= strtolower($lead['priority_name']) ?>"><?= Security::escape($lead['priority_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Release Watch & Warm Leads -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-rocket-takeoff me-2 text-success"></i>Release Watch (<?= count($releaseWatchLeads) ?>)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($releaseWatchLeads)): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-rocket-takeoff d-block"></i>
                        <p>No deals in release pipeline.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-crm mb-0">
                            <thead>
                                <tr><th>Lead</th><th>Model</th><th>Stage</th><th>Next Step</th><th>Due</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($releaseWatchLeads as $lead): ?>
                                    <tr class="cursor-pointer" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                                        <td>
                                            <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                            <?php if ($lead['priority_name']): ?>
                                                <span class="badge priority-<?= strtolower($lead['priority_name']) ?> ms-1" style="font-size:0.65rem"><?= Security::escape($lead['priority_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Security::escape($lead['model_name'] ?? '-') ?></td>
                                        <td><span class="badge badge-status" style="background:<?= Security::escape($lead['stage_color'] ?? '#6c757d') ?>;color:#fff"><?= Security::escape($lead['stage_name'] ?? '-') ?></span></td>
                                        <td><?= Security::escape($lead['next_step'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($lead['next_step_date']): ?>
                                                <?php $fu = FollowUpService::calculateStatus($lead['next_step_date']); ?>
                                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $fu)) ?>"><?= date('M d', strtotime($lead['next_step_date'])) ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-fire me-2 text-warning"></i>Warm/Hot Leads (<?= count($warmHotLeads) ?>)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($warmHotLeads)): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-snow d-block"></i>
                        <p>No warm leads currently.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-crm mb-0">
                            <thead>
                                <tr><th>Lead</th><th>Model</th><th>Stage</th><th>Last Contact</th><th>Next Step</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($warmHotLeads as $lead): ?>
                                    <tr class="cursor-pointer" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                                        <td>
                                            <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                            <?php if ($lead['priority_name']): ?>
                                                <span class="badge priority-<?= strtolower($lead['priority_name']) ?> ms-1" style="font-size:0.65rem"><?= Security::escape($lead['priority_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Security::escape($lead['model_name'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($lead['stage_name']): ?>
                                                <span class="badge badge-status" style="background:<?= Security::escape($lead['stage_color'] ?? '#6c757d') ?>;color:#fff"><?= Security::escape($lead['stage_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($lead['last_contact_date']): ?>
                                                <?= date('M d', strtotime($lead['last_contact_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Security::escape($lead['next_step'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline Summary -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-kanban me-2 text-info"></i>Pipeline Summary</span>
                <a href="<?= Url::route('pipeline') ?>" class="btn btn-sm btn-outline-primary">View Pipeline</a>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($stageCounts as $stage): ?>
                        <div class="col-6 col-md-4 col-lg-3 col-xl">
                            <div class="text-center p-2 rounded" style="background: <?= Security::escape($stage['color'] ?? '#6c757d') ?>10">
                                <div class="fw-bold fs-4" style="color: <?= Security::escape($stage['color'] ?? '#6c757d') ?>"><?= (int)$stage['count'] ?></div>
                                <div class="small text-muted"><?= Security::escape($stage['name']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
