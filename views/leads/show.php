<?php
require_once dirname(__DIR__, 2) . '/app/services/YearsStayedService.php';
$followupStatus = $followupStatus ?? 'No follow-up';
$daysSinceContact = $daysSinceContact ?? null;

$fmtMoney = function ($value): string {
    return $value !== null && $value !== '' ? '₱' . number_format((float) $value, 2) : '';
};
$fmtDate = function ($value): string {
    return $value ? date('M d, Y', strtotime($value)) : '';
};
$leadCustomerType = $lead['customer_type'] ?? null;
$isCorporate = $leadCustomerType === 'Corporate';
$primaryName = $isCorporate ? ($lead['company_name'] ?? $lead['lead_name']) : $lead['lead_name'];
$hasSpouse = ($lead['spouse_exists'] ?? null) === 'Yes';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= Url::route('leads') ?>" class="text-decoration-none text-muted me-2"><i class="bi bi-arrow-left"></i></a>
        <span class="text-muted"><?= Security::escape($lead['lead_id']) ?></span>
        <h5 class="mb-0 fw-bold"><?= Security::escape($lead['lead_name']) ?></h5>
    </div>
    <div class="d-flex gap-2 mt-2 mt-md-0">
        <a href="<?= Url::route('leads/' . $lead['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addActivityModal">
            <i class="bi bi-plus-lg me-1"></i> Activity
        </button>
        <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/archive') ?>" class="d-inline">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Archive this lead?')">
                <i class="bi bi-archive me-1"></i> Archive
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    <!-- Main Info -->
    <div class="col-lg-8">
        <!-- Customer Profile -->
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold">Customer Profile</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="info-label">Customer Type</div>
                        <?php if ($leadCustomerType): ?>
                            <span class="badge bg-<?= $isCorporate ? 'purple' : 'blue' ?> bg-opacity-10" style="color:<?= $isCorporate ? '#6C3483' : '#1F618D' ?>;font-size:0.85rem">
                                <?= Security::escape($leadCustomerType) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-label"><?= $isCorporate ? 'Company Name' : 'Full Name' ?></div>
                        <div class="info-value"><?= Security::escape($primaryName) ?></div>
                    </div>
                    <?php if ($isCorporate): ?>
                        <div class="col-sm-6">
                            <div class="info-label">Representative</div>
                            <div class="info-value"><?= Security::escape($lead['representative_name'] ?? '-') ?></div>
                        </div>
                    <?php else: ?>
                        <div class="col-sm-6">
                            <div class="info-label">Company</div>
                            <div class="info-value"><?= Security::escape($lead['company'] ?? '-') ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <div class="info-label">Phone / Mobile</div>
                        <div class="info-value">
                            <?php if ($lead['phone']): ?>
                                <a href="tel:<?= Security::escape($lead['phone']) ?>" class="text-decoration-none"><?= Security::escape($lead['phone']) ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-label">Telephone Number</div>
                        <div class="info-value">
                            <?php if ($lead['telephone_number']): ?>
                                <a href="tel:<?= Security::escape($lead['telephone_number']) ?>" class="text-decoration-none"><?= Security::escape($lead['telephone_number']) ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <?php if ($lead['email']): ?>
                                <a href="mailto:<?= Security::escape($lead['email']) ?>" class="text-decoration-none"><?= Security::escape($lead['email']) ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-label">TIN Number</div>
                        <div class="info-value"><?= Security::escape($lead['tin_number'] ?? '-') ?></div>
                    </div>
                    <?php if (!$isCorporate): ?>
                        <div class="col-sm-6">
                            <div class="info-label">Birthday</div>
                            <div class="info-value"><?= $fmtDate($lead['birthday'] ?? null) ?: '-' ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Number of Dependents</div>
                            <div class="info-value"><?= $lead['number_of_dependents'] !== null ? (int) $lead['number_of_dependents'] : '-' ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?= Security::escape($lead['location'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-label">Address Ownership</div>
                        <div class="info-value"><?= Security::escape($lead['address_ownership'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-label">Address Since</div>
                        <div class="info-value">
                            <?= $fmtDate($lead['address_since'] ?? null) ?: '-' ?>
                            <?php $addrYears = YearsStayedService::formatYears($lead['address_since'] ?? null); ?>
                            <?php if ($addrYears !== ''): ?>
                                <span class="text-muted">(<?= Security::escape($addrYears) ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($lead['employer_name'] || $lead['employer_address'] || $lead['employer_address_since'] || $lead['position'] || $lead['monthly_salary'] !== null || $lead['other_income'] !== null): ?>
            <!-- Employment / Business -->
            <div class="card section-card mb-3">
                <div class="card-header fw-semibold"><?= $isCorporate ? 'Business Details' : 'Employment / Business' ?></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-label"><?= $isCorporate ? 'Business Name' : 'Employer Name' ?></div>
                            <div class="info-value"><?= Security::escape($lead['employer_name'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label"><?= $isCorporate ? 'Business Address' : 'Employer Address' ?></div>
                            <div class="info-value"><?= Security::escape($lead['employer_address'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Employer Address Since</div>
                            <div class="info-value">
                                <?= $fmtDate($lead['employer_address_since'] ?? null) ?: '-' ?>
                                <?php $empYears = YearsStayedService::formatYears($lead['employer_address_since'] ?? null); ?>
                                <?php if ($empYears !== ''): ?>
                                    <span class="text-muted">(<?= Security::escape($empYears) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label"><?= $isCorporate ? 'Representative Position' : 'Position' ?></div>
                            <div class="info-value"><?= Security::escape($lead['position'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label"><?= $isCorporate ? 'Monthly Income' : 'Monthly Salary' ?></div>
                            <div class="info-value"><?= $fmtMoney($lead['monthly_salary'] ?? null) ?: '-' ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Other Source of Income</div>
                            <div class="info-value"><?= $fmtMoney($lead['other_income'] ?? null) ?: '-' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hasSpouse): ?>
            <!-- Spouse -->
            <div class="card section-card mb-3">
                <div class="card-header fw-semibold">Spouse</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-label">Spouse Name</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_name'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">TIN Number</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_tin_number'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Telephone Number</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_telephone_number'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Address</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_address'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Address Ownership</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_address_ownership'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Number of Dependents</div>
                            <div class="info-value"><?= $lead['spouse_number_of_dependents'] !== null ? (int) $lead['spouse_number_of_dependents'] : '-' ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Address Since</div>
                            <div class="info-value">
                                <?= $fmtDate($lead['spouse_address_since'] ?? null) ?: '-' ?>
                                <?php $spYears = YearsStayedService::formatYears($lead['spouse_address_since'] ?? null); ?>
                                <?php if ($spYears !== ''): ?>
                                    <span class="text-muted">(<?= Security::escape($spYears) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-sm-6">
                            <div class="info-label">Employer Name</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_employer_name'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Employer Address</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_employer_address'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Employer Address Since</div>
                            <div class="info-value">
                                <?= $fmtDate($lead['spouse_employer_address_since'] ?? null) ?: '-' ?>
                                <?php $spEmpYears = YearsStayedService::formatYears($lead['spouse_employer_address_since'] ?? null); ?>
                                <?php if ($spEmpYears !== ''): ?>
                                    <span class="text-muted">(<?= Security::escape($spEmpYears) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Position</div>
                            <div class="info-value"><?= Security::escape($lead['spouse_position'] ?? '-') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Monthly Salary</div>
                            <div class="info-value"><?= $fmtMoney($lead['spouse_monthly_salary'] ?? null) ?: '-' ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Other Source of Income</div>
                            <div class="info-value"><?= $fmtMoney($lead['spouse_other_income'] ?? null) ?: '-' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Vehicle Interest -->
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold">Vehicle Interest</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="info-label">Model</div>
                        <div class="info-value"><?= Security::escape($lead['model_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Color</div>
                        <div class="info-value"><?= Security::escape($lead['color_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Release Date</div>
                        <div class="info-value"><?= $lead['release_date'] ? date('M d, Y', strtotime($lead['release_date'])) : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Information -->
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                Sales Information
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changeStatusModal">Status</button>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changeStageModal">Stage</button>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePriorityModal">Priority</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="info-label">Lead Status</div>
                        <?php if ($lead['status_name']): ?>
                            <span class="badge badge-status" style="background:<?= Security::escape($lead['status_color'] ?? '#6c757d') ?>;color:#fff;font-size:0.85rem">
                                <?= Security::escape($lead['status_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Opportunity Stage</div>
                        <?php if ($lead['stage_name']): ?>
                            <span class="badge badge-status" style="background:<?= Security::escape($lead['stage_color'] ?? '#6c757d') ?>;color:#fff;font-size:0.85rem">
                                <?= Security::escape($lead['stage_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Priority</div>
                        <?php if ($lead['priority_name']): ?>
                            <span class="badge priority-<?= strtolower($lead['priority_name']) ?>" style="font-size:0.85rem">
                                <?= Security::escape($lead['priority_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Buyer Type</div>
                        <div class="info-value"><?= Security::escape($lead['buyer_type'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Purpose of Buying</div>
                        <div class="info-value"><?= Security::escape($lead['purchase_purpose'] ?? '-') ?></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Source</div>
                        <div class="info-value"><?= Security::escape($lead['source_name'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                Activity Timeline
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <div class="empty-state py-2">
                        <p class="mb-0">No activities recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($activities as $activity): ?>
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1">
                                            <?= Security::escape($activity['activity_type']) ?>
                                        </span>
                                        <?php if ($activity['next_step']): ?>
                                            <div class="small">
                                                <i class="bi bi-arrow-right text-muted"></i>
                                                Next: <?= Security::escape($activity['next_step']) ?>
                                                <?php if ($activity['next_step_date']): ?>
                                                    <span class="text-muted">(<?= date('M d', strtotime($activity['next_step_date'])) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($activity['activity_date'])) ?>
                                        <?php if ($activity['created_by_name']): ?>
                                            <br>by <?= Security::escape($activity['created_by_name']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <?php if ($activity['notes']): ?>
                                    <p class="mt-1 mb-0 small text-dark"><?= nl2br(Security::escape($activity['notes'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Follow-up Info -->
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold">Follow-up</div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="info-label">Follow-up Status</div>
                    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $followupStatus)) ?>"><?= $followupStatus ?></span>
                </div>
                <div class="mb-2">
                    <div class="info-label">Next Step</div>
                    <div class="info-value"><?= Security::escape($lead['next_step'] ?? '-') ?></div>
                </div>
                <div class="mb-2">
                    <div class="info-label">Next Step Date</div>
                    <div class="info-value"><?= $lead['next_step_date'] ? date('M d, Y', strtotime($lead['next_step_date'])) : '-' ?></div>
                </div>
                <div class="mb-2">
                    <div class="info-label">Last Contact</div>
                    <div class="info-value"><?= $lead['last_contact_date'] ? date('M d, Y', strtotime($lead['last_contact_date'])) : '-' ?></div>
                </div>
                <div>
                    <div class="info-label">Days Since Last Contact</div>
                    <div class="info-value">
                        <?php if ($daysSinceContact !== null): ?>
                            <?= $daysSinceContact ?> day<?= $daysSinceContact !== 1 ? 's' : '' ?>
                            <?php if ($daysSinceContact > 7): ?>
                                <span class="badge badge-overdue ms-1">Stale</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">No contact recorded</span>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#updateFollowupModal">
                    <i class="bi bi-calendar-plus me-1"></i> Update Follow-up
                </button>
                <div class="d-flex gap-1">
                    <?php if ($lead['phone']): ?>
                        <a href="tel:<?= Security::escape($lead['phone']) ?>" class="btn btn-outline-success btn-sm flex-fill">
                            <i class="bi bi-telephone me-1"></i> Call
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['phone']): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead['phone']) ?>" target="_blank" class="btn btn-outline-success btn-sm flex-fill">
                            <i class="bi bi-whatsapp me-1"></i> Message
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card section-card mb-3">
            <div class="card-header fw-semibold">Notes</div>
            <div class="card-body">
                <?php if ($lead['notes']): ?>
                    <p class="mb-0 small"><?= nl2br(Security::escape($lead['notes'])) ?></p>
                <?php else: ?>
                    <p class="text-muted small mb-0">No notes yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Meta -->
        <div class="card section-card">
            <div class="card-body small text-muted">
                <div>Created: <?= date('M d, Y', strtotime($lead['created_at'])) ?></div>
                <div>Updated: <?= date('M d, Y', strtotime($lead['updated_at'])) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- Add Activity Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/activity/store') ?>">
                <?= Security::csrfField() ?>
                <div class="modal-header">
                    <h6 class="modal-title fw-semibold">Add Activity</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Activity Type <span class="text-danger">*</span></label>
                        <select name="activity_type" class="form-select" required>
                            <?php foreach ($activityTypes as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Activity Date</label>
                        <input type="datetime-local" name="activity_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Step</label>
                        <input type="text" name="next_step" class="form-control" placeholder="e.g. Send proposal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Step Date</label>
                        <input type="date" name="next_step_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Change Status</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <?php foreach ($statuses as $s): ?>
                        <button class="btn btn-sm <?= (int)($lead['status_id'] ?? 0) === (int)$s['id'] ? 'btn-primary' : 'btn-outline-primary' ?> text-start"
                                onclick="updateField('status', <?= $s['id'] ?>)">
                            <?= Security::escape($s['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Stage Modal -->
<div class="modal fade" id="changeStageModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Change Stage</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-sm btn-outline-secondary text-start"
                            onclick="updateField('stage', null)">
                        -- None --
                    </button>
                    <?php foreach ($stages as $s): ?>
                        <button class="btn btn-sm <?= (int)($lead['opportunity_stage_id'] ?? 0) === (int)$s['id'] ? 'btn-primary' : 'btn-outline-primary' ?> text-start"
                                onclick="updateField('stage', <?= $s['id'] ?>)">
                            <?= Security::escape($s['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Priority Modal -->
<div class="modal fade" id="changePriorityModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Change Priority</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <?php foreach ($priorities as $p): ?>
                        <button class="btn btn-sm <?= (int)($lead['priority_id'] ?? 0) === (int)$p['id'] ? 'btn-primary' : 'btn-outline-primary' ?> text-start"
                                onclick="updateField('priority', <?= $p['id'] ?>)">
                            <?= Security::escape($p['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Follow-up Modal -->
<div class="modal fade" id="updateFollowupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Update Follow-up</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Next Step</label>
                    <input type="text" id="followupNextStep" class="form-control" value="<?= Security::escape($lead['next_step'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Next Step Date</label>
                    <input type="date" id="followupDate" class="form-control" value="<?= Security::escape($lead['next_step_date'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="updateFollowup()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
async function updateField(field, value) {
    const url = '<?= Url::route('api/leads/' . $lead['id'] . '/') ?>' + field;
    const resp = await apiRequest(url, 'POST', { value: value });
    if (resp.success) {
        showToast(resp.message);
        setTimeout(() => location.reload(), 500);
    } else {
        showToast(resp.message || 'Error updating.', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('change' + field.charAt(0).toUpperCase() + field.slice(1) + 'Modal'))?.hide();
}

async function updateFollowup() {
    const nextStep = document.getElementById('followupNextStep').value;
    const nextStepDate = document.getElementById('followupDate').value;
    
    const url = '<?= Url::route('api/leads/' . $lead['id'] . '/followup') ?>';
    const resp = await apiRequest(url, 'POST', { next_step: nextStep, next_step_date: nextStepDate });
    if (resp.success) {
        showToast(resp.message);
        setTimeout(() => location.reload(), 500);
    } else {
        showToast(resp.message || 'Error updating.', 'error');
    }
}
</script>
