<main class="profile-main">
    <section id="profile-section" class="py-4 py-md-5">
        <div class="container">
            <div class="profile-card mx-auto">
                <div class="profile-head">
                    <h1 class="profile-title mb-1 fw-bold">Profil Saya</h1>
                    <p class="profile-subtitle fw-semibold">Kelola informasi akun dan data diri Anda</p>
                </div>

                <div class="row g-4 g-lg-0 profile-columns">
                    <section id="profile-summary" class="col-lg-5 pe-lg-4">
                        <div class="profile-summary p-4 h-100">
                            <div class="profile-avatar">
                                <?= escape(getInitials($profile['name'])) ?>
                            </div>

                            <h2 class="profile-name text-center"><?= escape($profile['name']) ?></h2>
                            <hr class="profile-divider">

                            <ul class="profile-info list-unstyled mb-0">
                                <li class="d-flex gap-3">
                                    <i class="bi bi-person"></i>
                                    <div>
                                        <span class="profile-info-label">Nama Lengkap</span>
                                        <span class="profile-info-value"><?= escape($profile['name']) ?></span>
                                    </div>
                                </li>

                                <li class="d-flex gap-3">
                                    <i class="bi bi-envelope"></i>
                                    <div>
                                        <span class="profile-info-label">Email</span>
                                        <span class="profile-info-value"><?= escape($profile['email']) ?></span>
                                    </div>
                                </li>

                                <li class="d-flex gap-3">
                                    <i class="bi bi-telephone"></i>
                                    <div>
                                        <span class="profile-info-label">No Telepon</span>
                                        <span class="profile-info-value"><?= escape($profile['phone_number']) ?></span>
                                    </div>
                                </li>

                                <li class="d-flex gap-3">
                                    <i class="bi bi-geo-alt"></i>
                                    <div>
                                        <span class="profile-info-label">Alamat Pengantaran</span>
                                        <?php if (trim($profile['address']) !== ''): ?>
                                            <span class="profile-info-value"><?= nl2br(escape($profile['address'])) ?></span>
                                        <?php else: ?>
                                            <span class="profile-info-value profile-info-empty">Belum diisi</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section id="profile-edit" class="col-lg-7 ps-lg-4 profile-form-col">
                        <div class="profile-head">
                            <h2 class="profile-section-title mb-1">Edit Data Diri</h2>
                            <p class="profile-subtitle">Perbarui informasi pribadi Anda agar tetap sesuai.</p>
                        </div>

                        <form id="profile-form" action="index.php?page=profile" method="POST" novalidate>
                            <input type="hidden" name="action" value="update_profile">

                            <div class="profile-field">
                                <label for="name" class="profile-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="profile-input-group">
                                    <i class="bi bi-person profile-input-icon"></i>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        maxlength="100"
                                        class="form-control profile-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                                        value="<?= escape($form['name']) ?>"
                                        data-initial="<?= escape($profile['name']) ?>"
                                        required
                                    >
                                    <?php if (!empty($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= escape($errors['name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="profile-field">
                                <label for="email" class="profile-label">Email <span class="text-danger">*</span></label>
                                <div class="profile-input-group">
                                    <i class="bi bi-envelope profile-input-icon"></i>
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        maxlength="100"
                                        class="form-control profile-input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                                        value="<?= escape($form['email']) ?>"
                                        data-initial="<?= escape($profile['email']) ?>"
                                        required
                                    >
                                    <?php if (!empty($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= escape($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="profile-field">
                                <label for="phone_number" class="profile-label">No Telepon <span class="text-danger">*</span></label>
                                <div class="profile-input-group">
                                    <i class="bi bi-telephone profile-input-icon"></i>
                                    <input
                                        type="tel"
                                        name="phone_number"
                                        id="phone_number"
                                        maxlength="15"
                                        class="form-control profile-input <?= !empty($errors['phone_number']) ? 'is-invalid' : '' ?>"
                                        value="<?= escape($form['phone_number']) ?>"
                                        data-initial="<?= escape($profile['phone_number']) ?>"
                                        required
                                    >
                                    <?php if (!empty($errors['phone_number'])): ?>
                                        <div class="invalid-feedback"><?= escape($errors['phone_number']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="profile-field profile-field-last">
                                <label for="address" class="profile-label">Alamat Pengantaran <span class="profile-optional">(opsional)</span></label>
                                <div class="profile-input-group">
                                    <i class="bi bi-geo-alt profile-input-icon profile-input-icon-top"></i>
                                    <textarea
                                        name="address"
                                        id="address"
                                        rows="3"
                                        data-initial="<?= escape($profile['address']) ?>"
                                        class="form-control profile-input profile-textarea <?= !empty($errors['address']) ? 'is-invalid' : '' ?>"
                                    ><?= escape($form['address']) ?></textarea>
                                    <?php if (!empty($errors['address'])): ?>
                                        <div class="invalid-feedback"><?= escape($errors['address']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="submit" id="profile-submit" class="btn profile-submit-btn w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-floppy"></i>
                                <span>Simpan Perubahan</span>
                            </button>

                            <div class="modal fade profile-modal" id="email-confirm-modal" tabindex="-1" aria-labelledby="email-confirm-title" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header border-0 pb-0">
                                            <h2 class="modal-title" id="email-confirm-title">Konfirmasi Perubahan Email</h2>
                                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>

                                        <div class="modal-body">
                                            <p class="profile-modal-text mb-3">Masukkan password Anda untuk melanjutkan. Setelah email diubah, Anda akan keluar dan perlu login ulang.</p>
                                            <label for="current_password" class="profile-label">Password Saat Ini</label>
                                            <div class="profile-input-group">
                                                <i class="bi bi-lock profile-input-icon"></i>
                                                <input
                                                    type="password"
                                                    name="current_password"
                                                    id="current_password"
                                                    autocomplete="current-password"
                                                    class="form-control profile-input <?= !empty($errors['current_password']) ? 'is-invalid' : '' ?>"
                                                >
                                                <?php if (!empty($errors['current_password'])): ?>
                                                    <div class="invalid-feedback"><?= escape($errors['current_password']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light profile-cancel-btn" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn profile-submit-btn px-4">Konfirmasi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </section>
</main>