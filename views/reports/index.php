<?php
$activePage = 'reports';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Reports</h5>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <a href="<?= Url::route('reports/monthly-summary') ?>" class="text-decoration-none">
            <div class="card section-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-calendar-month fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="fw-semibold">Monthly Summary</h6>
                    <p class="text-muted small mb-0">Closing ratios, sales targets, lead goals</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= Url::route('reports/lead-performance') ?>" class="text-decoration-none">
            <div class="card section-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-people fs-1 text-success mb-2 d-block"></i>
                    <h6 class="fw-semibold">Lead Performance</h6>
                    <p class="text-muted small mb-0">Leads by source, status, model, priority</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= Url::route('reports/sales-performance') ?>" class="text-decoration-none">
            <div class="card section-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-graph-up fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="fw-semibold">Sales Performance</h6>
                    <p class="text-muted small mb-0">Deals by stage, released, monthly trends</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= Url::route('reports/followup-performance') ?>" class="text-decoration-none">
            <div class="card section-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clock-history fs-1 text-danger mb-2 d-block"></i>
                    <h6 class="fw-semibold">Follow-up Performance</h6>
                    <p class="text-muted small mb-0">Overdue, due today, upcoming, no follow-up</p>
                </div>
            </div>
        </a>
    </div>
</div>
