<div class="auth-centered-wrapper d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="auth-card text-center p-4 p-sm-5">
        <a href="index.php?page=home" class="d-inline-flex flex-column align-items-center text-decoration-none text-dark mb-3">
            <img src="./assets/img/logo.png" alt="MealMate Logo" width="76" height="76" class="mb-2">
            <span class="auth-brand-text fs-2">Meal Mate</span>
        </a>

        <h2 class="auth-card-title fw-bold mb-2">Create New Password</h2>
        <p class="text-muted small mb-4">
            Your new password must be different from<br>previous used passwords.
        </p>

        <?= renderFlash($flash, 'text-start small') ?>

        <form action="index.php?page=reset-password" method="POST" class="text-start" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

            <div class="mb-3">
                <div class="auth-input-group position-relative">
                    <i class="bi bi-lock auth-input-icon"></i>
                    <input
                        type="password"
                        name="password"
                        id="new-password"
                        class="form-control auth-input pe-5 <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                        placeholder="Password"
                        required
                    >
                    <button type="button" class="btn auth-password-toggle" onclick="togglePasswordVisibility('new-password', this)" aria-label="Tampilkan Password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <div class="auth-input-group position-relative">
                    <i class="bi bi-lock auth-input-icon"></i>
                    <input
                        type="password"
                        name="confirm_password"
                        id="confirm-password"
                        class="form-control auth-input pe-5 <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                        placeholder="Confirm Password"
                        required
                    >
                    <button type="button" class="btn auth-password-toggle" onclick="togglePasswordVisibility('confirm-password', this)" aria-label="Tampilkan Konfirmasi Password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn auth-submit-btn w-100 py-3 fw-semibold">
                Update Password
            </button>
        </form>
    </div>
</div>
