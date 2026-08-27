<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toyota CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .login-header {
            background: #212529;
            color: #fff;
            border-radius: 0.75rem 0.75rem 0 0;
            padding: 2rem;
            text-align: center;
        }
        .login-header i { font-size: 2.5rem; }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.15);
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-header">
            <i class="bi bi-car-front-fill text-danger"></i>
            <h4 class="mt-2 mb-0">Toyota CRM</h4>
            <small class="text-white-50">Sign in to your account</small>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i> <?= Security::escape($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= Url::route('auth/login') ?>">
                <?= Security::csrfField() ?>
                
                <div class="mb-3">
                    <label for="username" class="form-label fw-medium">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           required autofocus value="<?= Security::escape($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
