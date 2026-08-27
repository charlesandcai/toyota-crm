<?php
$search = $_GET['search'] ?? '';
$currentFilter = $_GET['filter'] ?? 'all';
$currentSort = $sort ?? 'l.next_step_date';
$currentDir = $direction ?? 'ASC';

$leadsQuery = array_filter([
    'search' => $search !== '' ? $search : null,
    'filter' => $currentFilter !== 'all' ? $currentFilter : null,
    'status_id' => $_GET['status_id'] ?? null,
    'priority_id' => $_GET['priority_id'] ?? null,
    'source_id' => $_GET['source_id'] ?? null,
    'sort' => $currentSort,
    'direction' => $currentDir,
]);
$leadsBase = Url::route('leads') . (empty($leadsQuery) ? '' : '&' . http_build_query($leadsQuery));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Leads <span class="text-muted fs-6">(<?= number_format($totalLeads) ?>)</span></h5>
    <a href="<?= Url::route('leads/create') ?>" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i> New Lead
    </a>
</div>

<!-- Quick Filters -->
<div class="d-flex flex-wrap gap-1 mb-3">
    <?php
    $quickFilters = [
        'all' => 'All Leads',
        'new' => 'New Leads',
        'hot' => 'Hot Leads',
        'followup' => 'Follow-up',
        'due_today' => 'Due Today',
        'overdue' => 'Overdue',
        'active_deals' => 'Active Deals',
        'lost' => 'Lost',
    ];
    foreach ($quickFilters as $key => $label):
        $qfParams = $leadsQuery;
        if ($key === 'all') {
            unset($qfParams['filter']);
        } else {
            $qfParams['filter'] = $key;
        }
        $qfHref = Url::route('leads') . (empty($qfParams) ? '' : '&' . http_build_query($qfParams));
    ?>
        <a href="<?= $qfHref ?>" class="quick-filter-btn <?= $currentFilter === $key ? 'active' : '' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card section-card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="leads">
            <?php if ($currentFilter !== 'all'): ?>
                <input type="hidden" name="filter" value="<?= Security::escape($currentFilter) ?>">
            <?php endif; ?>
            
            <div class="col-md-3 col-sm-6">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Search name, phone, email, company..." 
                       value="<?= Security::escape($search) ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="status_id" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (int)($_GET['status_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= Security::escape($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="priority_id" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    <?php foreach ($priorities as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (int)($_GET['priority_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= Security::escape($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="source_id" class="form-select form-select-sm">
                    <option value="">All Sources</option>
                    <?php foreach ($sources as $src): ?>
                        <option value="<?= $src['id'] ?>" <?= (int)($_GET['source_id'] ?? 0) === (int)$src['id'] ? 'selected' : '' ?>>
                            <?= Security::escape($src['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-6">
                <select name="sort" class="form-select form-select-sm">
                    <option value="l.next_step_date" <?= $currentSort === 'l.next_step_date' ? 'selected' : '' ?>>Next Step</option>
                    <option value="l.last_contact_date" <?= $currentSort === 'l.last_contact_date' ? 'selected' : '' ?>>Last Contact</option>
                    <option value="l.created_at" <?= $currentSort === 'l.created_at' ? 'selected' : '' ?>>Created</option>
                    <option value="l.lead_name" <?= $currentSort === 'l.lead_name' ? 'selected' : '' ?>>Name</option>
                    <option value="p.level" <?= $currentSort === 'p.level' ? 'selected' : '' ?>>Priority</option>
                </select>
            </div>
            <div class="col-md-1 col-auto">
                <div class="btn-group btn-group-sm">
                    <button type="submit" class="btn btn-outline-primary" title="Search"><i class="bi bi-search"></i></button>
                    <a href="<?= Url::route('leads') ?>" class="btn btn-outline-secondary" title="Clear"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Desktop Table -->
<div class="d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-crm">
            <thead>
                <tr>
                    <?php
                    $nameQ = $leadsQuery;
                    $nameQ['sort'] = 'l.lead_name';
                    $nameQ['direction'] = $currentSort === 'l.lead_name' && $currentDir === 'ASC' ? 'DESC' : 'ASC';
                    unset($nameQ['page']);
                    $nameSortHref = Url::route('leads') . '&' . http_build_query($nameQ);
                    ?>
                    <th><a href="<?= $nameSortHref ?>" class="text-decoration-none text-muted">Lead ID</a></th>
                    <th><a href="<?= $nameSortHref ?>" class="text-decoration-none text-muted">Name</a></th>
                    <th>Model</th>
                    <th>Status</th>
                    <th>Stage</th>
                    <th>Priority</th>
                    <th>Source</th>
                    <th>Last Contact</th>
                    <th>Next Step</th>
                    <th>Follow-up</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['leads'])): ?>
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <i class="bi bi-inbox d-block"></i>
                                <p>No leads found. Try adjusting your filters.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['leads'] as $lead): ?>
                        <?php $fuStatus = FollowUpService::calculateStatus($lead['next_step_date']); ?>
                        <tr class="cursor-pointer" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                            <td><span class="text-muted small"><?= Security::escape($lead['lead_id']) ?></span></td>
                            <td>
                                <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                <?php if ($lead['company']): ?>
                                    <div class="text-muted small"><?= Security::escape($lead['company']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= Security::escape($lead['model_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($lead['status_name']): ?>
                                    <span class="badge badge-status" style="background:<?= Security::escape($lead['status_color'] ?? '#6c757d') ?>;color:#fff">
                                        <?= Security::escape($lead['status_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lead['stage_name']): ?>
                                    <span class="badge badge-status" style="background:<?= Security::escape($lead['stage_color'] ?? '#6c757d') ?>;color:#fff">
                                        <?= Security::escape($lead['stage_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lead['priority_name']): ?>
                                    <span class="badge priority-<?= strtolower($lead['priority_name']) ?>">
                                        <?= Security::escape($lead['priority_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= Security::escape($lead['source_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($lead['last_contact_date']): ?>
                                    <?= date('M d', strtotime($lead['last_contact_date'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= Security::escape($lead['next_step'] ?? '-') ?>
                                <?php if ($lead['next_step_date']): ?>
                                    <div class="small text-muted"><?= date('M d', strtotime($lead['next_step_date'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $fuStatus)) ?>">
                                    <?= $fuStatus ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= Url::route('leads/' . $lead['id']) ?>" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards -->
<div class="d-md-none">
    <?php if (empty($result['leads'])): ?>
        <div class="empty-state">
            <i class="bi bi-inbox d-block"></i>
            <p>No leads found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($result['leads'] as $lead): ?>
            <?php $fuStatus = FollowUpService::calculateStatus($lead['next_step_date']); ?>
            <a href="<?= Url::route('leads/' . $lead['id']) ?>" class="text-decoration-none">
                <div class="lead-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="lead-id"><?= Security::escape($lead['lead_id']) ?></span>
                            <div class="lead-name text-dark"><?= Security::escape($lead['lead_name']) ?></div>
                            <?php if ($lead['model_name']): ?>
                                <small class="text-muted"><?= Security::escape($lead['model_name']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <?php if ($lead['priority_name']): ?>
                                <span class="badge priority-<?= strtolower($lead['priority_name']) ?>"><?= Security::escape($lead['priority_name']) ?></span>
                            <?php endif; ?>
                            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $fuStatus)) ?> mt-1"><?= $fuStatus ?></span>
                        </div>
                    </div>
                    <div class="mt-2 d-flex flex-wrap gap-1">
                        <?php if ($lead['status_name']): ?>
                            <span class="badge badge-status" style="background:<?= Security::escape($lead['status_color'] ?? '#6c757d') ?>;color:#fff;font-size:0.7rem"><?= Security::escape($lead['status_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($lead['stage_name']): ?>
                            <span class="badge badge-status" style="background:<?= Security::escape($lead['stage_color'] ?? '#6c757d') ?>;color:#fff;font-size:0.7rem"><?= Security::escape($lead['stage_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($lead['source_name']): ?>
                            <span class="badge bg-light text-dark" style="font-size:0.7rem"><?= Security::escape($lead['source_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($lead['next_step']): ?>
                        <div class="mt-2 small">
                            <i class="bi bi-arrow-right text-muted me-1"></i>
                            <?= Security::escape($lead['next_step']) ?>
                            <?php if ($lead['next_step_date']): ?>
                                <span class="text-muted">(<?= date('M d', strtotime($lead['next_step_date'])) ?>)</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination pagination-sm justify-content-center">
            <?php
            $baseUrl = $leadsBase;
            ?>
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl ?>&page=<?= $page - 1 ?>">Prev</a>
            </li>
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl ?>&page=<?= $page + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
