<?php
"use strict";
$calColors = $colors ?? [];
$calLabels = $typeLabels ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Calendar</h5>
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <div class="card section-card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2 py-2">
                <div class="btn-group" role="group" id="calViewButtons">
                    <button type="button" class="btn btn-sm btn-outline-primary cal-view-btn" data-view="month">Month</button>
                    <button type="button" class="btn btn-sm btn-outline-primary cal-view-btn" data-view="week">Week</button>
                    <button type="button" class="btn btn-sm btn-outline-primary cal-view-btn" data-view="day">Day</button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="calToday">Today</button>
                <div class="btn-group ms-auto" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="calPrev" title="Previous"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="calNext" title="Next"><i class="bi bi-chevron-right"></i></button>
                </div>
                <span class="ms-2 fw-semibold" id="calDateLabel"></span>
            </div>
            <div class="card-body p-0">
                <div id="calendarGrid" class="cal-grid"></div>
                <div class="text-center text-muted py-4 d-none" id="calendarLoading"><i class="bi bi-hourglass-split me-1"></i>Loading…</div>
            </div>
        </div>

        <div class="card section-card mt-3">
            <div class="card-header fw-semibold">Legend</div>
            <div class="card-body">
                <div class="cal-legend">
                    <?php foreach ($calColors as $key => $color): ?>
                        <div class="cal-legend-item">
                            <span class="cal-dot" style="background:<?= Security::escape($color) ?>"></span>
                            <span class="small"><?= Security::escape($calLabels[$key] ?? ucfirst($key)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card section-card">
            <div class="card-header fw-semibold">Working Days</div>
            <div class="card-body">
                <form method="POST" action="<?= Url::route('settings/working-days/update') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>showToast('Working days saved','success')})">
                    <?= Security::csrfField() ?>
                    <?php foreach ($workingDays as $day => $isWorking): ?>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="working_days[]" value="<?= $day ?>" id="wd_<?= $day ?>" <?= $isWorking ? 'checked' : '' ?>>
                            <label class="form-check-label" for="wd_<?= $day ?>"><?= $day ?></label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-sm btn-danger mt-2">Save Working Days</button>
                </form>
            </div>
        </div>

        <div class="card section-card mt-3">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                Holidays <?= (int) $year ?>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal"><i class="bi bi-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($holidays)): ?>
                    <div class="text-muted text-center py-3 small">No holidays configured.</div>
                <?php else: ?>
                    <table class="table table-sm table-crm mb-0">
                        <thead><tr><th>Date</th><th>Name</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($holidays as $h): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($h['holiday_date'])) ?></td>
                                    <td><?= Security::escape($h['name']) ?></td>
                                    <td>
                                        <form method="POST" action="<?= Url::route('settings/holidays/delete') ?>" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this holiday?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="calEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="calEventTitle"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="calEventBody"></div>
            <div class="modal-footer">
                <a href="#" class="btn btn-danger btn-sm d-none" id="calEventLeadBtn"><i class="bi bi-person me-1"></i>View Lead</a>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="<?= Url::route('settings/holidays/store') ?>" onsubmit="event.preventDefault();submitAjaxForm(this,{onSuccess:()=>setTimeout(()=>location.reload(),500)})">
                <?= Security::csrfField() ?>
                <div class="modal-header">
                    <h6 class="modal-title">Add Holiday</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="date" name="holiday_date" class="form-control mb-2" required>
                    <input type="text" name="holiday_name" class="form-control" placeholder="Holiday name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= Url::asset('js/calendar.js') ?>"></script>