<?php $settingsTab = $settingsTab ?? 'users'; ?>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= Security::escape($_SESSION['flash_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= Url::route('settings') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i></a>
        <h5 class="d-inline ms-2 fw-bold">Enroll User</h5>
    </div>
    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus me-1"></i> Add User
    </button>
</div>

<div class="card section-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= Security::escape($u['full_name']) ?></td>
                    <td><code><?= Security::escape($u['username']) ?></code></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">User</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal-<?= $u['id'] ?>" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#passwordModal-<?= $u['id'] ?>" title="Reset Password">
                                <i class="bi bi-key"></i>
                            </button>
                            <button class="btn btn-outline-<?= $u['active'] ? 'secondary' : 'success' ?>"
                                data-bs-toggle="modal" data-bs-target="#toggleStatusModal-<?= $u['id'] ?>"
                                title="<?= $u['active'] ? 'Deactivate' : 'Activate' ?>">
                                <i class="bi bi-<?= $u['active'] ? 'person-dash' : 'person-check' ?>"></i>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Edit User Modal -->
                <div class="modal fade" id="editUserModal-<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="<?= Url::route('settings/users/update') ?>" data-ajax="true" data-reload="true">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit User: <?= Security::escape($u['username']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" name="username" value="<?= Security::escape($u['username']) ?>" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                                        <div class="form-text">Letters, numbers, and underscores only.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" value="<?= Security::escape($u['full_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="role" required>
                                            <option value="sales" <?= $u['role'] === 'sales' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="active" required>
                                            <option value="1" <?= $u['active'] ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= !$u['active'] ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reset Password Modal -->
                <div class="modal fade" id="passwordModal-<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="<?= Url::route('settings/users/password') ?>" data-ajax="true" data-reload="true">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reset Password: <?= Security::escape($u['username']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="password" minlength="8" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Toggle Status Modal -->
                <div class="modal fade" id="toggleStatusModal-<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="<?= Url::route('settings/users/update') ?>" data-ajax="true" data-reload="true">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="full_name" value="<?= Security::escape($u['full_name']) ?>">
                                <input type="hidden" name="username" value="<?= Security::escape($u['username']) ?>">
                                <input type="hidden" name="role" value="<?= $u['role'] ?>">
                                <input type="hidden" name="active" value="<?= $u['active'] ? 0 : 1 ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?= $u['active'] ? 'Deactivate' : 'Activate' ?> User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to <?= $u['active'] ? 'deactivate' : 'activate' ?> <strong><?= Security::escape($u['username']) ?></strong>?</p>
                                    <?php if ($u['active']): ?>
                                    <div class="alert alert-warning small">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Deactivated users cannot log in.
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-<?= $u['active'] ? 'warning' : 'success' ?>">
                                        <?= $u['active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= Url::route('settings/users/store') ?>" data-ajax="true" data-reload="true">
                <?= Security::csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Enroll New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                        <div class="form-text">Letters, numbers, and underscores only.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="sales">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
