<?php
$errors = $errors ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><a href="<?= Url::route('leads/' . $lead['id']) ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a> Edit Lead <span class="text-muted"><?= Security::escape($lead['lead_id']) ?></span></h5>
</div>

<div class="card section-card">
    <div class="card-body">
        <?php
        $formAction = Url::route('leads/' . $lead['id'] . '/update');
        $cancelUrl = Url::route('leads/' . $lead['id']);
        $submitLabel = 'Save Changes';
        $submitIcon = '<i class="bi bi-check-lg"></i>';
        $formValues = $lead;
        require __DIR__ . '/_lead_form.php';
        ?>
    </div>
</div>