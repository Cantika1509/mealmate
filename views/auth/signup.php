<div class="auth-split-wrapper">
    <div class="auth-form-panel d-flex flex-column justify-content-center px-4 px-sm-5 py-5">
        <div class="auth-form-container mx-auto w-100">
            <a href="index.php?page=home" class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark mb-4">
                <img src="./assets/img/logo.png" alt="MealMate Logo" width="48" height="48">
                <span class="auth-brand-text">Meal Mate</span>
            </a>

            <h2 class="auth-heading fw-bold mb-2">Daftar Sekarang</h2>
            <p class="auth-subheading text-muted mb-4">
                Mulai perjalanan makan sehat dan praktis<br class="d-none d-sm-inline"> bersama MealMate!
            </p>

            <?= renderFlash($flash) ?>

            <form action="index.php?page=signup" method="POST" novalidate>
                <div class="mb-3">
                    <div class="auth-input-group position-relative">
                        <i class="bi bi-person auth-input-icon"></i>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control auth-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                            placeholder="Nama Lengkap"
                            value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                            required
                        >
                        <?php if (!empty($errors['name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="auth-input-group position-relative">
                        <i class="bi bi-envelope auth-input-icon"></i>
                        <input
                            type="email"
                            name="email"
                            id="email"
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

                <div class="mb-3">
                    <div class="auth-input-group position-relative">
                        <i class="bi bi-telephone auth-input-icon"></i>
                        <input
                            type="tel"
                            name="phone_number"
                            id="phone_number"
                            class="form-control auth-input <?= !empty($errors['phone_number']) ? 'is-invalid' : '' ?>"
                            placeholder="Nomor Telepon"
                            value="<?= htmlspecialchars($old['phone_number'] ?? '') ?>"
                            required
                        >
                        <?php if (!empty($errors['phone_number'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['phone_number']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="auth-input-group position-relative">
                        <i class="bi bi-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="password"
                            id="signup-password"
                            class="form-control auth-input pe-5 <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="Password (minimal 8 karakter)"
                            required
                        >
                        <button type="button" class="btn auth-password-toggle" onclick="togglePasswordVisibility('signup-password', this)" aria-label="Tampilkan Password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn auth-submit-btn w-100 py-3 fw-semibold d-flex align-items-center justify-content-center gap-2">
                    <span>Sign Up</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <span class="text-muted small">Already have an account? </span>
                <a href="index.php?page=login" class="auth-switch-link fw-semibold text-decoration-none small">Login</a>
            </div>
        </div>
    </div>

    <div class="auth-visual-panel d-none d-lg-flex flex-column justify-content-end p-5 position-relative overflow-hidden">
        <img src="./assets/img/signup-img.webp" alt="Pelanggan menikmati paket katering sehat MealMate bersama" class="auth-cover-img position-absolute top-0 start-0 w-100 h-100">
    </div>
</div>