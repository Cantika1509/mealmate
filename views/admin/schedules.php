<?php
$hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulanSingkat = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
?>

<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="admin-page-title mb-1">Kelola Jadwal Menu</h1>
        <p class="text-muted mb-0">Atur menu harian yang akan disajikan kepada pelanggan sesuai jadwal.</p>
    </div>
    <button type="button" class="btn btn-mealmate-orange d-inline-flex align-items-center gap-2 align-self-start align-self-sm-auto" onclick="openScheduleModal()">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Jadwal</span>
    </button>
</div>

<div class="admin-card p-3 p-md-4">
    <?php if (empty($dates)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
            <p class="mb-0">Belum ada jadwal menu yang tersedia.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 190px;">Tanggal</th>
                        <th style="width: 140px;">Slot</th>
                        <th>Menu Disajikan</th>
                        <th class="text-end" style="width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dates as $dateStr):
                        $rows = $schedulesByDate[$dateStr] ?? [];
                        $dt = new DateTimeImmutable($dateStr);
                        $tglLabel = sprintf('%02d %s %s', (int) $dt->format('d'), $bulanSingkat[(int) $dt->format('n')], $dt->format('Y'));
                        $hariLabel = $hariIndo[$dt->format('l')] ?? '';
                    ?>
                        <?php foreach ($rows as $idx => $item):
                            $slotClass = match ($item['slot']) {
                                'Pagi' => 'admin-slot-pagi',
                                'Siang' => 'admin-slot-siang',
                                default => 'admin-slot-malam',
                            };
                            $slotIcon = match ($item['slot']) {
                                'Pagi' => 'bi-brightness-high',
                                'Siang' => 'bi-sun',
                                default => 'bi-moon',
                            };
                            $hasImage = !empty($item['image']) && is_file(__DIR__ . '/../../assets/img/' . $item['image']);
                        ?>
                            <tr>
                                <td>
                                    <?php if ($idx === 0): ?>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-calendar3 text-muted mt-1"></i>
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($tglLabel) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($hariLabel) ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="admin-slot-badge <?= $slotClass ?>">
                                        <i class="bi <?= $slotIcon ?>"></i>
                                        <span><?= htmlspecialchars($item['slot']) ?></span>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($hasImage): ?>
                                            <img src="./assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['menu_name']) ?>" class="admin-schedule-thumb flex-shrink-0">
                                        <?php else: ?>
                                            <div class="admin-schedule-placeholder flex-shrink-0"></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 13px;"><?= htmlspecialchars($item['menu_name']) ?></div>
                                            <div class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($item['description'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm admin-action-btn"
                                        data-date="<?= htmlspecialchars($item['date']) ?>"
                                        data-slot="<?= htmlspecialchars($item['slot']) ?>"
                                        data-menu-id="<?= (int) $item['menu_id'] ?>"
                                        onclick="openScheduleModalFromBtn(this)" aria-label="Edit Jadwal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm admin-action-btn"
                                        data-date="<?= htmlspecialchars($item['date']) ?>"
                                        data-slot="<?= htmlspecialchars($item['slot']) ?>"
                                        data-label="<?= htmlspecialchars($tglLabel . ' - ' . $item['slot'], ENT_QUOTES) ?>"
                                        onclick="openDeleteScheduleModal(this)" aria-label="Hapus Jadwal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-3 mt-2 border-top">
        <span class="text-muted small">Menampilkan <?= count($dates) ?> dari <?= $totalDates ?> tanggal</span>

        <?php if ($totalPages > 1): ?>
            <div class="d-flex align-items-center gap-1">
                <a href="index.php?page=admin-schedules&q=<?= urlencode($search) ?>&p=<?= max(1, $currentPageNum - 1) ?>"
                   class="btn btn-sm admin-page-btn <?= $currentPageNum <= 1 ? 'disabled' : '' ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?page=admin-schedules&q=<?= urlencode($search) ?>&p=<?= $i ?>"
                       class="btn btn-sm admin-page-btn <?= $i === $currentPageNum ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <a href="index.php?page=admin-schedules&q=<?= urlencode($search) ?>&p=<?= min($totalPages, $currentPageNum + 1) ?>"
                   class="btn btn-sm admin-page-btn <?= $currentPageNum >= $totalPages ? 'disabled' : '' ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="scheduleFormModal" tabindex="-1" aria-hidden="true" <?= !empty($errors) ? 'data-auto-open="true"' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <form action="index.php?page=admin-schedules" method="POST" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="original_date" id="scheduleOriginalDate" value="<?= htmlspecialchars($old['original_date'] ?? '') ?>">
                <input type="hidden" name="original_slot" id="scheduleOriginalSlot" value="<?= htmlspecialchars($old['original_slot'] ?? '') ?>">

                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="scheduleModalTitle">Atur Jadwal Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="schedule_date" class="form-label fw-medium small">Tanggal Penyajian <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="schedule_date"
                               data-today="<?= date('Y-m-d') ?>"
                               class="form-control <?= !empty($errors['date']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['date'] ?? date('Y-m-d')) ?>" required>
                        <?php if (!empty($errors['date'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['date']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="schedule_slot" class="form-label fw-medium small">Slot Waktu <span class="text-danger">*</span></label>
                        <select name="slot" id="schedule_slot" class="form-select <?= !empty($errors['slot']) ? 'is-invalid' : '' ?>" required>
                            <?php foreach (['Pagi', 'Siang', 'Malam'] as $s): ?>
                                <option value="<?= $s ?>" <?= (($old['slot'] ?? 'Pagi') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['slot'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['slot']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label for="schedule_menu_id" class="form-label fw-medium small">Menu Disajikan <span class="text-danger">*</span></label>
                        <select name="menu_id" id="schedule_menu_id" class="form-select <?= !empty($errors['menu_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Menu --</option>
                            <?php foreach ($allMenus as $m): ?>
                                <option value="<?= (int) $m['menu_id'] ?>" <?= ((int) ($old['menu_id'] ?? 0) === (int) $m['menu_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['menu_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['menu_id'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['menu_id']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-mealmate-green px-4">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4">
            <form action="index.php?page=admin-schedules" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="date" id="deleteScheduleDate" value="">
                <input type="hidden" name="slot" id="deleteScheduleSlot" value="">

                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Hapus Jadwal?</h6>
                    <p class="text-muted small mb-4">Hapus jadwal penyajian pada <strong id="deleteScheduleLabel"></strong>?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-3 px-3">Ya, Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
