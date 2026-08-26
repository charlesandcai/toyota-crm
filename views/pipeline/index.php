<?php
"use strict"; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Pipeline</h5>
</div>

<div class="pipeline-board" id="pipelineBoard">
    <?php foreach ($stages as $stage): ?>
        <div class="pipeline-column" data-stage-id="<?= $stage['id'] ?>">
            <div class="pipeline-column-header" style="border-top: 3px solid <?= Security::escape($stage['color'] ?? '#6c757d') ?>">
                <span>
                    <span class="badge bg-light text-dark me-1"><?= count($stageLeads[$stage['id']] ?? []) ?></span>
                    <?= Security::escape($stage['name']) ?>
                </span>
            </div>
            <div class="pipeline-column-body" data-stage-id="<?= $stage['id'] ?>">
                <?php foreach ($stageLeads[$stage['id']] ?? [] as $lead): ?>
                    <div class="pipeline-card" draggable="true" data-lead-id="<?= $lead['id'] ?>" onclick="location.href='<?= Url::route('leads/' . $lead['id']) ?>'">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <a href="<?= Url::route('leads/' . $lead['id']) ?>" class="text-decoration-none text-dark fw-semibold" onclick="event.stopPropagation()">
                                <?= Security::escape($lead['lead_name']) ?>
                            </a>
                            <?php if ($lead['priority_name']): ?>
                                <span class="badge priority-<?= strtolower($lead['priority_name']) ?>" style="font-size:0.6rem"><?= Security::escape($lead['priority_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted mb-1">
                            <?= Security::escape($lead['model_name'] ?? '') ?>
                            <?php if ($lead['source_name']): ?>
                                &middot; <?= Security::escape($lead['source_name']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($lead['next_step']): ?>
                            <div class="small">
                                <i class="bi bi-arrow-right text-muted me-1"></i>
                                <?= Security::escape($lead['next_step']) ?>
                                <?php if ($lead['next_step_date']): ?>
                                    <span class="text-muted">(<?= date('M d', strtotime($lead['next_step_date'])) ?>)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($stageLeads[$stage['id']])): ?>
                    <div class="text-center text-muted small py-3" style="opacity:0.5">
                        No leads
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const board = document.getElementById('pipelineBoard');
    if (!board) return;

    let draggedCard = null;

    board.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('pipeline-card')) {
            draggedCard = e.target;
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }
    });

    board.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('pipeline-card')) {
            e.target.classList.remove('dragging');
            document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
            draggedCard = null;
        }
    });

    board.addEventListener('dragover', function(e) {
        e.preventDefault();
        const col = e.target.closest('.pipeline-column-body');
        if (col) {
            document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
            col.classList.add('drag-over');
        }
    });

    board.addEventListener('drop', async function(e) {
        e.preventDefault();
        const col = e.target.closest('.pipeline-column-body');
        if (!col || !draggedCard) return;

        const leadId = draggedCard.dataset.leadId;
        const newStageId = col.dataset.stageId;

        try {
            const resp = await apiRequest('<?= Url::route('api/pipeline/') ?>' + leadId + '/stage', 'POST', { stage_id: parseInt(newStageId) });
            if (resp.success) {
                col.appendChild(draggedCard);
                showToast('Pipeline stage updated.');
                // Update column counts
                document.querySelectorAll('.pipeline-column').forEach(column => {
                    const count = column.querySelector('.pipeline-column-body').children.length;
                    column.querySelector('.badge').textContent = count;
                });
            } else {
                showToast(resp.message || 'Error updating.', 'error');
            }
        } catch (err) {
            showToast('Network error.', 'error');
        }

        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
    });
});
</script>
