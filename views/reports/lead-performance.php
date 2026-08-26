<?php
"use strict"; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('reports') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Lead Performance</h5>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card section-card">
            <div class="card-header fw-semibold">By Status</div>
            <div class="card-body">
                <?php foreach ($byStatus as $s): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge badge-status" style="background:<?= Security::escape($s['color'] ?? '#6c757d') ?>;color:#fff"><?= Security::escape($s['name']) ?></span>
                        <strong><?= (int)$s['count'] ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card section-card">
            <div class="card-header fw-semibold">By Priority</div>
            <div class="card-body">
                <?php foreach ($byPriority as $p): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge" style="background:<?= Security::escape($p['color'] ?? '#6c757d') ?>;color:#fff"><?= Security::escape($p['name']) ?></span>
                        <strong><?= (int)$p['count'] ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card section-card">
            <div class="card-header fw-semibold">By Model</div>
            <div class="card-body">
                <?php foreach ($byModel as $m): ?>
                    <?php if ((int)$m['count'] > 0): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= Security::escape($m['name']) ?></span>
                            <strong><?= (int)$m['count'] ?></strong>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card section-card">
            <div class="card-header fw-semibold">By Source</div>
            <div class="card-body">
                <?php foreach ($bySource as $src): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?= Security::escape($src['name']) ?></span>
                        <strong><?= (int)$src['lead_count'] ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
