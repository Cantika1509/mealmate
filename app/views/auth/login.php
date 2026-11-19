<div class="auth-split-wrapper">
    <div class="auth-visual-panel d-none d-lg-flex flex-column justify-content-start p-5 position-relative overflow-hidden">
        <img src="./assets/img/login-img.webp" alt="Hidangan rumahan berkualitas MealMate" class="auth-cover-img position-absolute top-0 start-0 w-100 h-100">
        <div class="auth-visual-overlay position-relative z-1 mt-3 ms-2">
            <div class="mb-4">
                <img src="./assets/img/logo.png" alt="MealMate Logo" width="110" height="110">
            </div>
            <h1 class="auth-visual-title text-white fw-bold mb-3">
                Makan Enak,<br>Hidup Lebih Mudah
            </h1>
            <p class="auth-visual-subtitle text-white mb-0">
                Menu rumahan berkualitas, setiap hari,<br>untuk kamu yang sibuk
            </p>
        </div>
    </div>

    <div class="auth-form-panel d-flex flex-column justify-content-center px-4 px-sm-5 py-5">
        <div class="auth-form-container mx-auto w-100">
            <a href="index.php?page=home" class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark mb-4">
                <img src="./assets/img/logo.png" alt="MealMate Logo" width="48" height="48">
                <span class="auth-brand-text">Meal Mate</span>
            </a>

            <h2 class="auth-heading fw-bold mb-2">Welcome Back</h2>
            <p class="auth-subheading text-muted mb-4">
                Masuk ke akun kamu untuk melanjutkan<br class="d-none d-sm-inline"> pesanan dan kelola langganan
            </p>

            <?= renderFlash($flash) ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger rounded-3 py-2 px-3" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=login" method="POST" novalidate>
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

                <div class="mb-2">
                    <div class="auth-input-group position-relative">
                        <i class="bi bi-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="password"
                            id="login-password"
                            class="form-control auth-input pe-5 <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="Password"
                            required
                        >
                        <button type="button" class="btn auth-password-toggle" onclick="togglePasswordVisibility('login-password', this)" aria-label="Tampilkan Password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="index.php?page=forgot-password" class="auth-forgot-link text-decoration-none fw-medium">Forgot Password?</a>
                </div>

                <button type="submit" class="btn auth-submit-btn w-100 py-3 fw-semibold d-flex align-items-center justify-content-center gap-2">
                    <span>Login</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <span class="text-muted small">Don't have an account? </span>
                <a href="index.php?page=signup" class="auth-switch-link fw-semibold text-decoration-none small">Sign Up</a>
            </div>
        </div>
    </div>
</div>
