<?php
$errors = $errors ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('leads') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Create New Lead</h5>
    <span class="text-muted">Lead ID: <?= Security::escape($newLeadId) ?> (auto-generated)</span>
</div>

<div class="card section-card">
    <div class="card-body">
        <?php
        $formAction = Url::route('leads/store');
        $cancelUrl = Url::route('leads');
        $submitLabel = 'Create Lead';
        $submitIcon = '<i class="bi bi-plus-lg"></i>';
        $prefillInitialContactDate = true;
        $formValues = $_POST ?? [];
        require __DIR__ . '/_lead_form.php';
        ?>
    </div>
</div>