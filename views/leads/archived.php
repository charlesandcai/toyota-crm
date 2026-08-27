<?php
$search = $_GET['search'] ?? '';
$currentSort = $sort ?? 'l.archived_at';
$currentDir = $direction ?? 'DESC';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><i class="bi bi-archive me-2"></i>Archived Leads <span class="text-muted fs-6">(<?= number_format($totalLeads) ?>)</span></h5>
    <a href="<?= Url::route('leads') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Active Leads
    </a>
</div>

<!-- Filters -->
<div class="card section-card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="leads/archived">
            
            <div class="col-md-3 col-sm-6">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Search name, phone, email, lead ID..." 
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
            <div class="col-md-2 col-sm-6">
                <select name="sort" class="form-select form-select-sm">
                    <option value="l.archived_at" <?= $currentSort === 'l.archived_at' ? 'selected' : '' ?>>Archived Date</option>
                    <option value="l.lead_name" <?= $currentSort === 'l.lead_name' ? 'selected' : '' ?>>Name</option>
                    <option value="l.lead_id" <?= $currentSort === 'l.lead_id' ? 'selected' : '' ?>>Lead ID</option>
                    <option value="l.last_contact_date" <?= $currentSort === 'l.last_contact_date' ? 'selected' : '' ?>>Last Contact</option>
                </select>
            </div>
            <div class="col-md-1 col-auto">
                <div class="btn-group btn-group-sm">
                    <button type="submit" class="btn btn-outline-primary" title="Search"><i class="bi bi-search"></i></button>
                    <a href="<?= Url::route('leads/archived') ?>" class="btn btn-outline-secondary" title="Clear"><i class="bi bi-x-lg"></i></a>
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
                    <th>Lead ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Model</th>
                    <th>Status</th>
                    <th>Stage</th>
                    <th>Priority</th>
                    <th>Source</th>
                    <th>Last Contact</th>
                    <th>Archived</th>
                    <th style="width:140px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['leads'])): ?>
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <i class="bi bi-archive d-block"></i>
                                <p>No archived leads found.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['leads'] as $lead): ?>
                        <tr>
                            <td><span class="text-muted small"><?= Security::escape($lead['lead_id']) ?></span></td>
                            <td>
                                <strong><?= Security::escape($lead['lead_name']) ?></strong>
                                <?php if ($lead['company']): ?>
                                    <div class="text-muted small"><?= Security::escape($lead['company']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lead['phone']): ?>
                                    <a href="tel:<?= Security::escape($lead['phone']) ?>" class="text-decoration-none small"><?= Security::escape($lead['phone']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
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
                                    <?= date('M d, Y', strtotime($lead['last_contact_date'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lead['archived_at']): ?>
                                    <?= date('M d, Y', strtotime($lead['archived_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/restore') ?>" class="d-inline">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Restore to active" onclick="return confirm('Restore this lead to active CRM?')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Permanently delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal<?= $lead['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal<?= $lead['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h6 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Permanent Deletion</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="fw-bold text-danger">This permanently deletes the lead and cannot be undone.</p>
                                                <p class="mb-1">Lead: <strong><?= Security::escape($lead['lead_name']) ?></strong> (<?= Security::escape($lead['lead_id']) ?>)</p>
                                                <p class="text-muted small">All associated activities will also be permanently removed.</p>
                                                <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/force-delete') ?>" id="deleteForm<?= $lead['id'] ?>">
                                                    <?= Security::csrfField() ?>
                                                    <div class="mb-3">
                                                        <label class="form-label small">Type <strong>DELETE</strong> to confirm:</label>
                                                        <input type="text" name="confirm_delete" class="form-control form-control-sm"
                                                               placeholder="Type DELETE" autocomplete="off"
                                                               oninput="document.getElementById('deleteBtn<?= $lead['id'] ?>').disabled = this.value !== 'DELETE'">
                                                    </div>
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" id="deleteBtn<?= $lead['id'] ?>" class="btn btn-danger btn-sm" disabled>
                                                            <i class="bi bi-trash me-1"></i> Delete Permanently
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
            <i class="bi bi-archive d-block"></i>
            <p>No archived leads found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($result['leads'] as $lead): ?>
            <div class="lead-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="lead-id"><?= Security::escape($lead['lead_id']) ?></span>
                        <div class="lead-name text-dark"><?= Security::escape($lead['lead_name']) ?></div>
                        <?php if ($lead['phone']): ?>
                            <small class="text-muted"><?= Security::escape($lead['phone']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <?php if ($lead['priority_name']): ?>
                            <span class="badge priority-<?= strtolower($lead['priority_name']) ?>"><?= Security::escape($lead['priority_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-2 d-flex flex-wrap gap-1">
                    <?php if ($lead['status_name']): ?>
                        <span class="badge badge-status" style="background:<?= Security::escape($lead['status_color'] ?? '#6c757d') ?>;color:#fff;font-size:0.7rem"><?= Security::escape($lead['status_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($lead['model_name']): ?>
                        <span class="badge bg-light text-dark" style="font-size:0.7rem"><?= Security::escape($lead['model_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($lead['archived_at']): ?>
                        <small class="text-muted">Archived <?= date('M d, Y', strtotime($lead['archived_at'])) ?></small>
                    <?php endif; ?>
                </div>
                <div class="mt-2 d-flex gap-1">
                    <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/restore') ?>" class="d-inline">
                        <?= Security::csrfField() ?>
                        <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('Restore this lead?')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="modal" data-bs-target="#deleteModalMobile<?= $lead['id'] ?>">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>

                <!-- Mobile Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModalMobile<?= $lead['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h6 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Permanent Deletion</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="fw-bold text-danger">This permanently deletes the lead and cannot be undone.</p>
                                <p class="mb-1">Lead: <strong><?= Security::escape($lead['lead_name']) ?></strong> (<?= Security::escape($lead['lead_id']) ?>)</p>
                                <p class="text-muted small">All associated activities will also be permanently removed.</p>
                                <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/force-delete') ?>">
                                    <?= Security::csrfField() ?>
                                    <div class="mb-3">
                                        <label class="form-label small">Type <strong>DELETE</strong> to confirm:</label>
                                        <input type="text" name="confirm_delete" class="form-control form-control-sm"
                                               placeholder="Type DELETE" autocomplete="off"
                                               oninput="this.closest('form').querySelector('[type=submit]').disabled = this.value !== 'DELETE'">
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger btn-sm" disabled>
                                            <i class="bi bi-trash me-1"></i> Delete Permanently
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination pagination-sm justify-content-center">
            <?php
            $baseQuery = http_build_query(array_filter([
                'search' => $search ?: null,
                'status_id' => $_GET['status_id'] ?? null,
                'priority_id' => $_GET['priority_id'] ?? null,
                'source_id' => $_GET['source_id'] ?? null,
                'sort' => $currentSort,
                'direction' => $currentDir,
            ]));
            $baseUrl = Url::route('leads/archived') . ($baseQuery === '' ? '' : '&' . $baseQuery);
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