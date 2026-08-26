<?php
"use strict"; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('reports') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Monthly Summary</h5>
    <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="route" value="reports/monthly-summary">
        <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="card section-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-crm">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class="text-center">Leads</th>
                        <th class="text-center">Deals</th>
                        <th class="text-center">Closing Ratio</th>
                        <th class="text-center">Sales Target</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $m): ?>
                        <tr>
                            <td class="fw-semibold"><?= $m['month_name'] ?></td>
                            <td class="text-center"><?= $m['total_leads'] ?></td>
                            <td class="text-center"><?= $m['total_deals'] ?></td>
                            <td class="text-center">
                                <?= $m['closing_ratio'] > 0 ? round($m['closing_ratio'] * 100, 1) . '%' : '-' ?>
                            </td>
                            <td class="text-center"><?= $m['sales_target'] > 0 ? $m['sales_target'] : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
