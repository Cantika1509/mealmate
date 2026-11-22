<?php
$bulanSingkat = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$formatTgl = static function (string $dateStr, bool $withTime = false) use ($bulanSingkat): string {
    $dt = new DateTimeImmutable($dateStr);
    $tgl = sprintf('%02d %s %s', (int) $dt->format('d'), $bulanSingkat[(int) $dt->format('n')], $dt->format('Y'));
    return $withTime ? $tgl . ', ' . $dt->format('H:i') : $tgl;
};

$orderCode = sprintf('#ORD-%03d', (int) $order['order_id']);
$hasPackageImg = !empty($order['package_image']) && is_file(__DIR__ . '/../../../public/assets/img/' . $order['package_image']);
$proofFile = $order['payment_proof'] ?? '';
$proofExistsOnDisk = $proofFile !== '' && is_file(__DIR__ . '/../../../public/assets/img/' . $proofFile);
$proofUrl = $proofExistsOnDisk ? './assets/img/' . $proofFile : '';

$bannerConfig = match ($order['status']) {
    'Menunggu Verifikasi' => [
        'class' => 'admin-verify-banner-pending',
        'icon' => 'bi-clock-history',
        'title' => 'Menunggu Verifikasi',
        'desc' => 'Bukti pembayaran telah diunggah oleh pelanggan dan sedang menunggu validasi admin.',
    ],
    'Aktif' => [
        'class' => 'admin-verify-banner-active',
        'icon' => 'bi-check-circle-fill',
        'title' => 'Langganan Aktif',
        'desc' => 'Bukti pembayaran telah tervalidasi (Valid). Paket katering sedang dalam masa pengantaran aktif.',
    ],
    'Selesai' => [
        'class' => 'admin-verify-banner-completed',
        'icon' => 'bi-patch-check-fill',
        'title' => 'Pesanan Selesai',
        'desc' => 'Masa langganan pesanan ini telah selesai dilaksanakan.',
    ],
    'Ditolak' => [
        'class' => 'admin-verify-banner-rejected',
        'icon' => 'bi-x-circle-fill',
        'title' => 'Pembayaran Ditolak',
        'desc' => 'Bukti pembayaran ditolak oleh admin. Alasan: ' . ($order['rejection_reason'] ?: '-'),
    ],
    'Dibatalkan' => [
        'class' => 'admin-verify-banner-cancelled',
        'icon' => 'bi-slash-circle',
        'title' => 'Pesanan Dibatalkan',
        'desc' => 'Pesanan ini telah dibatalkan oleh pelanggan sebelum pembayaran diverifikasi.',
    ],
    default => [
        'class' => 'admin-verify-banner-unpaid',
        'icon' => 'bi-hourglass-split',
        'title' => 'Belum Dibayar',
        'desc' => 'Pelanggan belum mengunggah bukti transfer pembayaran untuk pesanan ini.',
    ],
};
?>

<div class="mb-4">
    <a href="index.php?page=admin-orders" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Daftar Pesanan</span>
    </a>
    <h1 class="admin-page-title mb-1">Detail Pesanan <span style="color: #13523C;"><?= htmlspecialchars($orderCode) ?></span></h1>
    <p class="text-muted mb-0">Dipesan pada <?= htmlspecialchars($formatTgl($order['created_at'], true)) ?></p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="admin-card p-4 h-100">
            <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                <i class="bi bi-person fs-5 text-dark"></i>
                <h6 class="fw-bold mb-0 text-dark">Informasi Pelanggan & Pesanan</h6>
            </div>

            <div class="admin-detail-group pb-3 mb-3 border-bottom">
                <div class="d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-people text-muted mt-1"></i>
                    <div>
                        <div class="text-muted small">Nama Pelanggan</div>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-telephone text-muted mt-1"></i>
                    <div>
                        <div class="text-muted small">No. Telepon</div>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($order['customer_phone'] ?? '-') ?></div>
                    </div>
                </div>
            </div>

            <div class="admin-detail-group pb-3 mb-3 border-bottom">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-geo-alt text-muted mt-1"></i>
                    <div>
                        <div class="text-muted small mb-1">Alamat Pengiriman</div>
                        <div class="text-dark small lh-base"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></div>
                    </div>
                </div>
            </div>

            <div class="admin-detail-group pb-3 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-box-seam text-muted"></i>
                    <span class="text-muted small">Paket Langganan</span>
                </div>
                <div class="d-flex align-items-center gap-3 ps-4">
                    <?php if ($hasPackageImg): ?>
                        <img src="./assets/img/<?= htmlspecialchars($order['package_image']) ?>" alt="<?= htmlspecialchars($order['package_name']) ?>" class="admin-pkg-detail-thumb">
                    <?php else: ?>
                        <div class="admin-menu-icon flex-shrink-0">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($order['package_name']) ?></div>
                        <div class="text-muted small mb-1">(<?= (int) $order['duration_days'] ?> hari • <?= htmlspecialchars(str_replace('-', ' - ', $order['applicable_days'])) ?>)</div>
                        <span class="badge rounded-pill" style="background-color: #E3EFE6; color: #13523C; font-weight: 600; font-size: 11px;">
                            Slot <?= htmlspecialchars($order['slot']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="admin-detail-group pb-3 mb-3 border-bottom">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-calendar3 text-muted mt-1"></i>
                            <div>
                                <div class="text-muted small">Tanggal Mulai</div>
                                <div class="fw-semibold text-dark small"><?= htmlspecialchars($formatTgl($order['start_date'])) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-calendar-check text-muted mt-1"></i>
                            <div>
                                <div class="text-muted small">Estimasi Selesai</div>
                                <div class="fw-semibold text-dark small"><?= htmlspecialchars($formatTgl($order['end_date'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($order['notes'])): ?>
                <div class="admin-detail-group pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-chat-left-text text-muted mt-1"></i>
                        <div>
                            <div class="text-muted small">Catatan Pelanggan</div>
                            <div class="text-dark small"><?= htmlspecialchars($order['notes']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-receipt text-muted mt-1"></i>
                <div>
                    <div class="text-muted small">Total Harga</div>
                    <div class="fw-bold fs-4 text-dark"><?= formatRupiah((int) $order['total_price']) ?></div>
                    <div class="text-muted" style="font-size: 11.5px;">(termasuk biaya pengiriman)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                    <i class="bi bi-credit-card fs-5 text-dark"></i>
                    <h6 class="fw-bold mb-0 text-dark">Verifikasi Pembayaran</h6>
                </div>

                <div class="admin-verify-banner <?= $bannerConfig['class'] ?> d-flex align-items-start gap-3 p-3 rounded-3 mb-4">
                    <i class="bi <?= $bannerConfig['icon'] ?> fs-5 mt-1"></i>
                    <div>
                        <div class="fw-bold small"><?= htmlspecialchars($bannerConfig['title']) ?></div>
                        <div class="small opacity-90"><?= htmlspecialchars($bannerConfig['desc']) ?></div>
                    </div>
                </div>

                <?php if ($proofFile !== ''): ?>
                    <div class="admin-proof-viewer rounded-3 p-3 p-sm-4 mb-3 d-flex flex-column align-items-center justify-content-center text-center" id="paymentProofViewer">
                        <?php if ($proofExistsOnDisk): ?>
                            <img src="<?= htmlspecialchars($proofUrl) ?>"
                                 alt="Bukti Pembayaran <?= htmlspecialchars($orderCode) ?>"
                                 id="paymentProofImg"
                                 class="img-fluid rounded-3 shadow-sm"
                                 style="max-height: 360px; object-fit: contain;"
                                 onerror="handlePaymentProofError()">
                        <?php endif; ?>

                        <div id="paymentProofFallback" class="<?= $proofExistsOnDisk ? 'd-none' : '' ?> bg-white rounded-3 shadow-sm p-4 text-start w-100" style="max-width: 340px;">
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                                <span class="fw-bold text-primary">BCA <span class="fw-normal small text-muted">m-Transfer</span></span>
                                <span class="badge bg-light text-secondary border">Struk Transfer</span>
                            </div>
                            <div class="small text-muted mb-1">m-Transfer : <strong class="text-success">BERHASIL</strong></div>
                            <div class="small text-muted mb-2"><?= htmlspecialchars($formatTgl($order['created_at'], true)) ?></div>
                            <div class="small text-dark mb-2">
                                Ke <strong>1234 5678 9012</strong><br>
                                a/n <strong>PT MealMate Catering</strong>
                            </div>
                            <div class="fw-bold text-dark mb-2"><?= formatRupiah((int) $order['total_price']) ?></div>
                            <div class="small text-muted mb-3">Berita: Pembayaran pesanan <?= htmlspecialchars($orderCode) ?></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="reloadPaymentProofImage('<?= htmlspecialchars($proofUrl, ENT_QUOTES) ?>')">
                                <i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang Gambar Asli
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="fw-semibold small text-dark mb-2">Bukti Pembayaran</div>
                        <div class="d-flex align-items-center justify-content-between border rounded-3 px-3 py-2 bg-light">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="bi bi-file-earmark-image text-secondary"></i>
                                <span class="small fw-medium text-dark text-truncate"><?= htmlspecialchars($proofFile) ?></span>
                                <?php if (!empty($order['payment_status'])): ?>
                                    <span class="badge <?= $order['payment_status'] === 'Valid' ? 'bg-success' : 'bg-danger' ?> ms-1">
                                        <?= htmlspecialchars($order['payment_status']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($proofExistsOnDisk): ?>
                                <a href="<?= htmlspecialchars($proofUrl) ?>" download class="btn btn-sm text-secondary p-1" title="Unduh Bukti Bayar">
                                    <i class="bi bi-download"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="admin-proof-viewer rounded-3 p-5 mb-4 text-center text-muted">
                        <i class="bi bi-image fs-1 d-block mb-2 opacity-50"></i>
                        <p class="mb-0 small">Belum ada berkas bukti pembayaran yang diunggah untuk pesanan ini.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <?php if ($order['status'] === 'Menunggu Verifikasi' && !empty($order['payment_id'])): ?>
                    <div class="row g-2">
                        <div class="col-12 col-sm-7">
                            <form action="index.php?page=admin-order-detail&id=<?= (int) $order['order_id'] ?>" method="POST">
                                <input type="hidden" name="action" value="verify_payment">
                                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                <input type="hidden" name="decision" value="approve">
                                <button type="submit" class="btn btn-mealmate-green w-100 py-2">
                                    <i class="bi bi-check2 me-1"></i>Validasi (Terima)
                                </button>
                            </form>
                        </div>
                        <div class="col-12 col-sm-5">
                            <button type="button" class="btn btn-outline-danger w-100 py-2 fw-semibold rounded-3" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">
                                <i class="bi bi-x-lg me-1"></i>Tolak
                            </button>
                        </div>
                    </div>
                <?php elseif ($order['status'] === 'Aktif'): ?>
                    <form action="index.php?page=admin-order-detail&id=<?= (int) $order['order_id'] ?>" method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                        <input type="hidden" name="new_status" value="Selesai">
                        <button type="submit" class="btn btn-mealmate-green w-100 py-2">
                            <i class="bi bi-check2-all me-1"></i>Tandai Pesanan Selesai
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectPaymentModal" tabindex="-1" aria-hidden="true" <?= !empty($errors['rejection_reason']) ? 'data-auto-open="true"' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <form action="index.php?page=admin-order-detail&id=<?= (int) $order['order_id'] ?>" method="POST" novalidate>
                <input type="hidden" name="action" value="verify_payment">
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <input type="hidden" name="decision" value="reject">

                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-danger">Tolak Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <p class="text-muted small mb-3">
                        Tuliskan alasan penolakan dengan jelas agar pelanggan mengetahui penyebab bukti pembayaran ditolak (misalnya nominal tidak sesuai atau foto buram).
                    </p>
                    <div class="mb-2">
                        <label for="rejection_reason" class="form-label fw-medium small">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3"
                                  class="form-control <?= !empty($errors['rejection_reason']) ? 'is-invalid' : '' ?>"
                                  placeholder="Contoh: Nominal transfer tidak sesuai dengan total tagihan pesanan." required><?= htmlspecialchars($old['rejection_reason'] ?? '') ?></textarea>
                        <?php if (!empty($errors['rejection_reason'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['rejection_reason']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4">Tolak Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
