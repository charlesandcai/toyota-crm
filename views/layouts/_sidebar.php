<?php
$activePage = $activePage ?? 'dashboard';
?>
<ul class="nav flex-column sidebar-nav">
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= Url::route('dashboard') ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'leads' ? 'active' : '' ?>" href="<?= Url::route('leads') ?>">
            <i class="bi bi-people-fill"></i><span>Leads</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'leads_archived' ? 'active' : '' ?>" href="<?= Url::route('leads/archived') ?>">
            <i class="bi bi-archive"></i><span>Archived Leads</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'pipeline' ? 'active' : '' ?>" href="<?= Url::route('pipeline') ?>">
            <i class="bi bi-kanban"></i><span>Pipeline</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'activities' ? 'active' : '' ?>" href="<?= Url::route('activities') ?>">
            <i class="bi bi-calendar-check"></i><span>Activities</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= Url::route('reports') ?>">
            <i class="bi bi-graph-up"></i><span>Reports</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'imports' ? 'active' : '' ?>" href="<?= Url::route('imports') ?>">
            <i class="bi bi-upload"></i><span>Import</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activePage === 'settings' ? 'active' : '' ?>" href="<?= Url::route('settings') ?>">
            <i class="bi bi-gear"></i><span>Settings</span>
        </a>
    </li>
</ul>
