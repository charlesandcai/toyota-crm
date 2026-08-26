<?php
$errors = $errors ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('leads/' . $lead['id']) ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Edit Lead <span class="text-muted"><?= Security::escape($lead['lead_id']) ?></span></h5>
</div>

<div class="card section-card">
    <div class="card-body">
        <form method="POST" action="<?= Url::route('leads/' . $lead['id'] . '/update') ?>">
            <?= Security::csrfField() ?>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Lead Name <span class="text-danger">*</span></label>
                    <input type="text" name="lead_name" class="form-control <?= isset($errors['lead_name']) ? 'is-invalid' : '' ?>" 
                           required value="<?= Security::escape($lead['lead_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Company</label>
                    <input type="text" name="company" class="form-control" value="<?= Security::escape($lead['company'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= Security::escape($lead['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email</label>
                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                           value="<?= Security::escape($lead['email'] ?? '') ?>">
                </div>

                <div class="col-12"><hr></div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status_id" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (int)($lead['status_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Opportunity Stage</label>
                    <select name="opportunity_stage_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php foreach ($stages as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (int)($lead['opportunity_stage_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Priority</label>
                    <select name="priority_id" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (int)($lead['priority_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Source</label>
                    <select name="source_id" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach ($sources as $src): ?>
                            <option value="<?= $src['id'] ?>" <?= (int)($lead['source_id'] ?? 0) === (int)$src['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($src['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Vehicle Model</label>
                    <select name="model_id" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach ($models as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= (int)($lead['model_id'] ?? 0) === (int)$m['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($m['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Vehicle Color</label>
                    <select name="color_id" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach ($colors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int)($lead['color_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12"><hr></div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Initial Contact Date</label>
                    <input type="date" name="initial_contact_date" class="form-control" 
                           value="<?= Security::escape($lead['initial_contact_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Last Contact Date</label>
                    <input type="date" name="last_contact_date" class="form-control" 
                           value="<?= Security::escape($lead['last_contact_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Next Step</label>
                    <input type="text" name="next_step" class="form-control" 
                           value="<?= Security::escape($lead['next_step'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Next Step Date</label>
                    <input type="date" name="next_step_date" class="form-control" 
                           value="<?= Security::escape($lead['next_step_date'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-medium">Location</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?= Security::escape($lead['location'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= Security::escape($lead['notes'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                    <a href="<?= Url::route('leads/' . $lead['id']) ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
