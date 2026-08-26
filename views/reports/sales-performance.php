<?php
"use strict"; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('reports') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Sales Performance</h5>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card section-card">
            <div class="card-header fw-semibold">Deals by Stage</div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($byStage as $s): ?>
                        <div class="col-6 col-md-4 col-lg">
                            <div class="text-center p-3 rounded" style="background: <?= Security::escape($s['color'] ?? '#6c757d') ?>10">
                                <div class="fw-bold fs-3" style="color: <?= Security::escape($s['color'] ?? '#6c757d') ?>"><?= (int)$s['count'] ?></div>
                                <div class="small text-muted"><?= Security::escape($s['name']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card section-card">
            <div class="card-header fw-semibold">Monthly Trends</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-crm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Leads</th>
                                <th class="text-center">Deals</th>
                                <th class="text-center">Closing Ratio</th>
                                <th class="text-center">Target</th>
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
                                    <td class="text-center"><?= $m['sales_target'] ?: '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
