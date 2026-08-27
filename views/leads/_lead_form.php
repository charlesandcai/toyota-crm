<?php
require_once dirname(__DIR__, 2) . '/app/services/YearsStayedService.php';
$errors = $errors ?? [];
$formValues = $formValues ?? [];
$v = function (string $key) use ($formValues) {
    return Security::escape($formValues[$key] ?? '');
};
$customerType = ($formValues['customer_type'] ?? 'Individual') === 'Corporate' ? 'Corporate' : 'Individual';
$spouseExists = ($formValues['spouse_exists'] ?? 'No') === 'Yes' ? 'Yes' : 'No';
?>

<form method="POST" action="<?= $formAction ?>" id="leadForm">
    <?= Security::csrfField() ?>

    <div class="row g-3">
        <!-- SECTION: CUSTOMER TYPE -->
        <div class="col-12">
            <div class="form-section">Customer Type <span class="text-danger">*</span></div>
            <hr>
        </div>
        <div class="col-md-6">
            <div class="mt-1">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="customer_type" id="ctIndividual" value="Individual"
                           <?= $customerType === 'Individual' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-medium" for="ctIndividual">Individual</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="customer_type" id="ctCorporate" value="Corporate"
                           <?= $customerType === 'Corporate' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-medium" for="ctCorporate">Corporate</label>
                </div>
            </div>
            <?php if (isset($errors['customer_type'])): ?>
                <div class="text-danger small mt-1"><?= Security::escape($errors['customer_type']) ?></div>
            <?php endif; ?>
        </div>

        <?php
        $topErrors = ['lead_name', 'company_name', 'email', 'birthday', 'address_since', 'employer_address_since',
            'number_of_dependents', 'monthly_salary', 'other_income', 'spouse_name', 'spouse_address_since',
            'spouse_employer_address_since', 'spouse_number_of_dependents', 'spouse_monthly_salary', 'spouse_other_income'];
        $hasTopErrors = false;
        foreach ($topErrors as $k) {
            if (isset($errors[$k])) { $hasTopErrors = true; break; }
        }
        if ($hasTopErrors): ?>
            <div class="col-12">
                <div class="alert alert-danger py-2">
                    <?php foreach ($topErrors as $k): ?>
                        <?php if (isset($errors[$k])): ?>
                            <div><i class="bi bi-exclamation-circle me-1"></i><?= Security::escape($errors[$k]) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- SECTION: PERSONAL / CORPORATE INFORMATION -->
        <div class="col-12">
            <div class="form-section">Personal / Corporate Information</div>
            <hr>
        </div>

        <div class="cust-individual">
            <div class="col-md-6">
                <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="lead_name" class="form-control <?= isset($errors['lead_name']) ? 'is-invalid' : '' ?>"
                       required value="<?= $v('lead_name') ?>">
                <?php if (isset($errors['lead_name'])): ?>
                    <div class="invalid-feedback"><?= Security::escape($errors['lead_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Company</label>
                <input type="text" name="company" class="form-control" value="<?= $v('company') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Birthday</label>
                <input type="date" name="birthday" class="form-control <?= isset($errors['birthday']) ? 'is-invalid' : '' ?>"
                       value="<?= $v('birthday') ?>">
                <?php if (isset($errors['birthday'])): ?>
                    <div class="invalid-feedback"><?= Security::escape($errors['birthday']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Number of Dependents</label>
                <input type="number" min="0" name="number_of_dependents" class="form-control <?= isset($errors['number_of_dependents']) ? 'is-invalid' : '' ?>"
                       value="<?= $v('number_of_dependents') ?>">
                <?php if (isset($errors['number_of_dependents'])): ?>
                    <div class="invalid-feedback"><?= Security::escape($errors['number_of_dependents']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="cust-corporate">
            <div class="col-md-6">
                <label class="form-label fw-medium">Company Name <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>"
                       required value="<?= $v('company_name') ?>">
                <?php if (isset($errors['company_name'])): ?>
                    <div class="invalid-feedback"><?= Security::escape($errors['company_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Representative</label>
                <input type="text" name="representative_name" class="form-control" value="<?= $v('representative_name') ?>">
            </div>
        </div>

        <!-- SECTION: CONTACT INFORMATION -->
        <div class="col-12">
            <div class="form-section">Contact Information</div>
            <hr>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">TIN Number</label>
            <input type="text" name="tin_number" class="form-control" placeholder="e.g. 123-456-789-000" value="<?= $v('tin_number') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Phone / Mobile</label>
            <input type="tel" name="phone" class="form-control" value="<?= $v('phone') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Telephone Number</label>
            <input type="tel" name="telephone_number" class="form-control" value="<?= $v('telephone_number') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Email</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   value="<?= $v('email') ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= Security::escape($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <!-- SECTION: ADDRESS INFORMATION -->
        <div class="col-12">
            <div class="form-section">Address Information</div>
            <hr>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Address</label>
            <input type="text" name="location" class="form-control" value="<?= $v('location') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Address Ownership</label>
            <select name="address_ownership" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach (['Rent', 'Owned', 'Mortgaged'] as $own): ?>
                    <option value="<?= $own ?>" <?= ($formValues['address_ownership'] ?? '') === $own ? 'selected' : '' ?>><?= $own ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Address Since</label>
            <input type="date" name="address_since" data-years-target="yearsStayed"
                   class="form-control <?= isset($errors['address_since']) ? 'is-invalid' : '' ?>" value="<?= $v('address_since') ?>">
            <?php if (isset($errors['address_since'])): ?>
                <div class="invalid-feedback"><?= Security::escape($errors['address_since']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 d-flex align-items-end pb-1">
            <div class="w-100">
                <label class="form-label fw-medium text-muted d-block mb-1">Years Stayed</label>
                <div class="years-stayed text-muted" id="yearsStayed"><?= Security::escape(YearsStayedService::formatYears($formValues['address_since'] ?? null)) ?></div>
            </div>
        </div>

        <!-- SECTION: EMPLOYER / BUSINESS DETAILS -->
        <div class="col-12">
            <div class="form-section">Employer / Business Details</div>
            <hr>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Employer / Business Name</label>
            <input type="text" name="employer_name" class="form-control" value="<?= $v('employer_name') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Business / Employer Address</label>
            <input type="text" name="employer_address" class="form-control" value="<?= $v('employer_address') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Employer Address Since</label>
            <input type="date" name="employer_address_since" data-years-target="employerYearsStayed"
                   class="form-control <?= isset($errors['employer_address_since']) ? 'is-invalid' : '' ?>" value="<?= $v('employer_address_since') ?>">
            <?php if (isset($errors['employer_address_since'])): ?>
                <div class="invalid-feedback"><?= Security::escape($errors['employer_address_since']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 d-flex align-items-end pb-1">
            <div class="w-100">
                <label class="form-label fw-medium text-muted d-block mb-1">Years Stayed</label>
                <div class="years-stayed text-muted" id="employerYearsStayed"><?= Security::escape(YearsStayedService::formatYears($formValues['employer_address_since'] ?? null)) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Position / Representative Position</label>
            <input type="text" name="position" class="form-control" value="<?= $v('position') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Monthly Salary / Income</label>
            <input type="number" min="0" step="0.01" name="monthly_salary" class="form-control <?= isset($errors['monthly_salary']) ? 'is-invalid' : '' ?>"
                   value="<?= $v('monthly_salary') ?>">
            <?php if (isset($errors['monthly_salary'])): ?>
                <div class="invalid-feedback"><?= Security::escape($errors['monthly_salary']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Other Source of Income</label>
            <input type="number" min="0" step="0.01" name="other_income" class="form-control <?= isset($errors['other_income']) ? 'is-invalid' : '' ?>"
                   value="<?= $v('other_income') ?>">
            <?php if (isset($errors['other_income'])): ?>
                <div class="invalid-feedback"><?= Security::escape($errors['other_income']) ?></div>
            <?php endif; ?>
        </div>

        <!-- SECTION: SPOUSE INFORMATION -->
        <div class="spouse-wrapper col-12">
            <div class="col-12 px-0">
                <div class="form-section">Spouse Information</div>
                <hr>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="spouse_exists" id="spYes" value="Yes"
                           <?= $spouseExists === 'Yes' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-medium" for="spYes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="spouse_exists" id="spNo" value="No"
                           <?= $spouseExists === 'No' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-medium" for="spNo">No</label>
                </div>
            </div>

            <div class="spouse-section">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Spouse Name <span class="text-danger">*</span></label>
                    <input type="text" name="spouse_name" class="form-control <?= isset($errors['spouse_name']) ? 'is-invalid' : '' ?>"
                           required value="<?= $v('spouse_name') ?>">
                    <?php if (isset($errors['spouse_name'])): ?>
                        <div class="invalid-feedback"><?= Security::escape($errors['spouse_name']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Spouse TIN Number</label>
                    <input type="text" name="spouse_tin_number" class="form-control" value="<?= $v('spouse_tin_number') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Spouse Telephone Number</label>
                    <input type="tel" name="spouse_telephone_number" class="form-control" value="<?= $v('spouse_telephone_number') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Spouse Address</label>
                    <input type="text" name="spouse_address" class="form-control" value="<?= $v('spouse_address') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Address Ownership</label>
                    <select name="spouse_address_ownership" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach (['Rent', 'Owned', 'Mortgaged'] as $own): ?>
                            <option value="<?= $own ?>" <?= ($formValues['spouse_address_ownership'] ?? '') === $own ? 'selected' : '' ?>><?= $own ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Number of Dependents</label>
                    <input type="number" min="0" name="spouse_number_of_dependents" class="form-control <?= isset($errors['spouse_number_of_dependents']) ? 'is-invalid' : '' ?>"
                           value="<?= $v('spouse_number_of_dependents') ?>">
                    <?php if (isset($errors['spouse_number_of_dependents'])): ?>
                        <div class="invalid-feedback"><?= Security::escape($errors['spouse_number_of_dependents']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Address Since</label>
                    <input type="date" name="spouse_address_since" data-years-target="spouseYearsStayed"
                           class="form-control <?= isset($errors['spouse_address_since']) ? 'is-invalid' : '' ?>" value="<?= $v('spouse_address_since') ?>">
                    <?php if (isset($errors['spouse_address_since'])): ?>
                        <div class="invalid-feedback"><?= Security::escape($errors['spouse_address_since']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 d-flex align-items-end pb-1">
                    <div class="w-100">
                        <label class="form-label fw-medium text-muted d-block mb-1">Spouse Years Stayed</label>
                        <div class="years-stayed text-muted" id="spouseYearsStayed"><?= Security::escape(YearsStayedService::formatYears($formValues['spouse_address_since'] ?? null)) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Spouse Employer Name</label>
                    <input type="text" name="spouse_employer_name" class="form-control" value="<?= $v('spouse_employer_name') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Spouse Employer Address</label>
                    <input type="text" name="spouse_employer_address" class="form-control" value="<?= $v('spouse_employer_address') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Employer Address Since</label>
                    <input type="date" name="spouse_employer_address_since" data-years-target="spouseEmployerYearsStayed"
                           class="form-control <?= isset($errors['spouse_employer_address_since']) ? 'is-invalid' : '' ?>" value="<?= $v('spouse_employer_address_since') ?>">
                    <?php if (isset($errors['spouse_employer_address_since'])): ?>
                        <div class="invalid-feedback"><?= Security::escape($errors['spouse_employer_address_since']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 d-flex align-items-end pb-1">
                    <div class="w-100">
                        <label class="form-label fw-medium text-muted d-block mb-1">Spouse Employer Years Stayed</label>
                        <div class="years-stayed text-muted" id="spouseEmployerYearsStayed"><?= Security::escape(YearsStayedService::formatYears($formValues['spouse_employer_address_since'] ?? null)) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Position</label>
                    <input type="text" name="spouse_position" class="form-control" value="<?= $v('spouse_position') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Monthly Salary</label>
                    <input type="number" min="0" step="0.01" name="spouse_monthly_salary" class="form-control <?= isset($errors['spouse_monthly_salary']) ? 'is-invalid' : '' ?>"
                           value="<?= $v('spouse_monthly_salary') ?>">
                    <?php if (isset($errors['spouse_monthly_salary'])): ?>
                        <div class="invalid-feedback"><?= Security::escape($errors['spouse_monthly_salary']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Other Source of Income</label>
                    <input type="number" min="0" step="0.01" name="spouse_other_income" class="form-control <?= isset($errors['spouse_other_income']) ? 'is-invalid' : '' ?>"
                           value="<?= $v('spouse_other_income') ?>">
                    <?php if (isset($errors['spouse_other_income'])): ?>
                        <div class="invalid-feedback"><?= Security::escape($errors['spouse_other_income']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SECTION: VEHICLE INTEREST -->
        <div class="col-12">
            <div class="form-section">Vehicle Interest</div>
            <hr>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Vehicle Model</label>
            <select name="model_id" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach ($models as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= (int)($formValues['model_id'] ?? 0) === (int)$m['id'] ? 'selected' : '' ?>>
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
                    <option value="<?= $c['id'] ?>" <?= (int)($formValues['color_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= Security::escape($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Release Date</label>
            <input type="date" name="release_date" class="form-control <?= isset($errors['release_date']) ? 'is-invalid' : '' ?>"
                   value="<?= $v('release_date') ?>">
            <?php if (isset($errors['release_date'])): ?>
                <div class="invalid-feedback"><?= Security::escape($errors['release_date']) ?></div>
            <?php endif; ?>
        </div>

        <!-- SECTION: SALES INFORMATION -->
        <div class="col-12">
            <div class="form-section">Sales Information</div>
            <hr>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Status</label>
            <select name="status_id" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (int)($formValues['status_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
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
                    <option value="<?= $s['id'] ?>" <?= (int)($formValues['opportunity_stage_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
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
                    <option value="<?= $p['id'] ?>" <?= (int)($formValues['priority_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
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
                    <option value="<?= $src['id'] ?>" <?= (int)($formValues['source_id'] ?? 0) === (int)$src['id'] ? 'selected' : '' ?>>
                        <?= Security::escape($src['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- SECTION: PURCHASE INFORMATION -->
        <div class="col-12">
            <div class="form-section">Purchase Information</div>
            <hr>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Buyer Type</label>
            <select name="buyer_type" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach (['First Time', 'Additional Purchase', 'Replacement'] as $bt): ?>
                    <option value="<?= $bt ?>" <?= ($formValues['buyer_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium">Purpose of Buying</label>
            <select name="purchase_purpose" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach (['Business / Work', 'Personal / Family'] as $pp): ?>
                    <option value="<?= $pp ?>" <?= ($formValues['purchase_purpose'] ?? '') === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- SECTION: FOLLOW-UP & ACTIVITY DATES -->
        <div class="col-12">
            <div class="form-section">Follow-up &amp; Activity Dates</div>
            <hr>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Initial Contact Date</label>
            <input type="date" name="initial_contact_date" class="form-control <?= isset($errors['initial_contact_date']) ? 'is-invalid' : '' ?>"
                   value="<?= $v('initial_contact_date') !== '' ? $v('initial_contact_date') : (($prefillInitialContactDate ?? false) ? date('Y-m-d') : '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Last Contact Date</label>
            <input type="date" name="last_contact_date" class="form-control" value="<?= $v('last_contact_date') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Next Step</label>
            <input type="text" name="next_step" class="form-control" placeholder="e.g. Send quote" value="<?= $v('next_step') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium">Next Step Date</label>
            <input type="date" name="next_step_date" class="form-control <?= isset($errors['next_step_date']) ? 'is-invalid' : '' ?>"
                   value="<?= $v('next_step_date') ?>">
        </div>

        <!-- SECTION: NOTES -->
        <div class="col-12">
            <div class="form-section">Notes</div>
            <hr>
        </div>
        <div class="col-12">
            <textarea name="notes" class="form-control" rows="3"><?= $v('notes') ?></textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-danger">
                <?= $submitIcon ?><span class="ms-1"><?= $submitLabel ?></span>
            </button>
            <a href="<?= $cancelUrl ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </div>
</form>

<script src="<?= Url::asset('js/customer-form.js') ?>"></script>