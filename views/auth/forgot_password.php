<div class="auth-centered-wrapper d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="auth-card text-center p-4 p-sm-5">
        <a href="index.php?page=home" class="d-inline-flex flex-column align-items-center text-decoration-none text-dark mb-3">
            <img src="./assets/img/logo.png" alt="MealMate Logo" width="76" height="76" class="mb-2">
            <span class="auth-brand-text fs-2">Meal Mate</span>
        </a>

        <h2 class="auth-card-title fw-bold mb-2">Reset Password</h2>
        <p class="text-muted small mb-4">
            Enter your email address and we'll send you<br>a link to reset your password
        </p>

        <?= renderFlash($flash, 'text-start small') ?>

        <form action="index.php?page=forgot-password" method="POST" class="text-start" novalidate>
            <div class="mb-3">
                <div class="auth-input-group position-relative">
                    <i class="bi bi-envelope auth-input-icon"></i>
                    <input
                        type="email"
                        name="email"
                        id="reset-email"
                        class="form-control auth-input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                        placeholder="Email address"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        required
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn auth-submit-btn w-100 py-3 fw-semibold d-flex align-items-center justify-content-center gap-2 mb-3">
                <span>Send Reset Link</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <a href="index.php?page=login" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-2 mt-2">
            <i class="bi bi-arrow-left"></i>
            <span>Back To Login</span>
        </a>
    </div>
</div>
