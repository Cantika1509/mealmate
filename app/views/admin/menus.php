<?php
$startRow = $totalMenus > 0 ? $offset + 1 : 0;
$endRow = min($offset + $perPage, $totalMenus);
?>

<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="admin-page-title mb-1">Master Data Menu</h1>
        <p class="text-muted mb-0">Kelola daftar hidangan yang bisa dijadwalkan ke dalam paket</p>
    </div>
    <button type="button" class="btn btn-mealmate-orange d-inline-flex align-items-center gap-2 align-self-start align-self-sm-auto" onclick="openMenuModal('create')">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Menu</span>
    </button>
</div>

<div class="admin-card p-3 p-md-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
            <form action="index.php" method="get" class="position-relative flex-grow-1" style="max-width: 320px;">
                <input type="hidden" name="page" value="admin-menus">
                <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control ps-5 rounded-3 admin-search-input" placeholder="Cari menu...">
            </form>

            <div class="d-inline-flex align-items-center gap-2">
                <a href="index.php?page=admin-menus&view=kanban&q=<?= urlencode($search) ?>" class="btn admin-view-pill <?= $viewMode === 'kanban' ? 'active' : '' ?>">Kanban</a>
                <a href="index.php?page=admin-menus&view=list&q=<?= urlencode($search) ?>" class="btn admin-view-pill <?= $viewMode === 'list' ? 'active' : '' ?>">List</a>
            </div>

            <?php if ($viewMode === 'list'): ?>
                <button type="button" id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none" onclick="openBulkDeleteModal()">
                    <i class="bi bi-trash me-1"></i>Hapus Terpilih (<span id="selectedCountLabel">0</span>)
                </button>
            <?php endif; ?>
        </div>

        <?php if ($viewMode === 'kanban'): ?>
            <span class="text-muted small fw-medium"><?= $totalMenus ?> menu</span>
        <?php endif; ?>
    </div>

    <?php if (empty($menus)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-fork-knife fs-1 d-block mb-2"></i>
            <p class="mb-0">Belum ada data menu yang ditemukan.</p>
        </div>
    <?php elseif ($viewMode === 'list'): ?>
        <form id="bulkDeleteForm" action="index.php?page=admin-menus&view=list" method="POST">
            <input type="hidden" name="action" value="bulk_delete">
            <input type="hidden" name="view" value="list">
            <div class="table-responsive">
                <table class="table admin-table admin-list-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 48px;">
                                <input type="checkbox" class="form-check-input admin-checkbox" id="selectAllMenus" onchange="toggleSelectAllMenus(this)">
                            </th>
                            <th style="width: 240px;">Nama Menu</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="menu_ids[]" value="<?= (int) $menu['menu_id'] ?>" class="form-check-input admin-checkbox menu-row-checkbox" onchange="updateBulkDeleteState()">
                                </td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($menu['menu_name']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($menu['description'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 84px;">Foto</th>
                        <th style="width: 240px;">Nama Menu</th>
                        <th>Deskripsi</th>
                        <th class="text-end" style="width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu):
                        $hasImage = !empty($menu['image']) && is_file(__DIR__ . '/../../../public/assets/img/' . $menu['image']);
                    ?>
                        <tr>
                            <td>
                                <?php if ($hasImage): ?>
                                    <img src="./assets/img/<?= htmlspecialchars($menu['image']) ?>" alt="<?= htmlspecialchars($menu['menu_name']) ?>" class="admin-menu-thumb">
                                <?php else: ?>
                                    <div class="admin-menu-icon">
                                        <i class="bi bi-fork-knife"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($menu['menu_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($menu['description'] ?? '') ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm admin-action-btn"
                                    data-id="<?= (int) $menu['menu_id'] ?>"
                                    data-name="<?= htmlspecialchars($menu['menu_name'], ENT_QUOTES) ?>"
                                    data-desc="<?= htmlspecialchars($menu['description'] ?? '', ENT_QUOTES) ?>"
                                    onclick="openMenuModalFromBtn(this)" aria-label="Edit Menu">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm admin-action-btn"
                                    data-id="<?= (int) $menu['menu_id'] ?>"
                                    data-name="<?= htmlspecialchars($menu['menu_name'], ENT_QUOTES) ?>"
                                    onclick="openDeleteMenuModal(this)" aria-label="Hapus Menu">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-3 mt-2 border-top">
        <span class="text-muted small">Menampilkan <?= $startRow ?>–<?= $endRow ?> dari <?= $totalMenus ?> menu</span>

        <?php if ($totalPages > 1): ?>
            <div class="d-flex align-items-center gap-1">
                <a href="index.php?page=admin-menus&view=<?= urlencode($viewMode) ?>&q=<?= urlencode($search) ?>&p=<?= max(1, $currentPageNum - 1) ?>"
                   class="btn btn-sm admin-page-btn <?= $currentPageNum <= 1 ? 'disabled' : '' ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?page=admin-menus&view=<?= urlencode($viewMode) ?>&q=<?= urlencode($search) ?>&p=<?= $i ?>"
                       class="btn btn-sm admin-page-btn <?= $i === $currentPageNum ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <a href="index.php?page=admin-menus&view=<?= urlencode($viewMode) ?>&q=<?= urlencode($search) ?>&p=<?= min($totalPages, $currentPageNum + 1) ?>"
                   class="btn btn-sm admin-page-btn <?= $currentPageNum >= $totalPages ? 'disabled' : '' ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="menuFormModal" tabindex="-1" aria-hidden="true" <?= !empty($errors) ? 'data-auto-open="true"' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <form action="index.php?page=admin-menus&view=<?= urlencode($viewMode) ?>" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" id="menuFormAction" value="<?= htmlspecialchars($old['modal_mode'] ?? 'create') ?>">
                <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
                <input type="hidden" name="menu_id" id="menuFormId" value="<?= (int) ($old['menu_id'] ?? 0) ?>">

                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="menuFormTitle">Tambah Menu Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="menu_name" class="form-label fw-medium small">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="menu_name" id="menu_name"
                               class="form-control <?= !empty($errors['menu_name']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['menu_name'] ?? '') ?>"
                               placeholder="Contoh: Ayam Bakar Kecap" required>
                        <?php if (!empty($errors['menu_name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['menu_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-medium small">Deskripsi Masakan <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="3"
                                  class="form-control <?= !empty($errors['description']) ? 'is-invalid' : '' ?>"
                                  placeholder="Jelaskan bahan dan cita rasa hidangan..." required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        <?php if (!empty($errors['description'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label for="image" class="form-label fw-medium small">Foto Menu (JPG, PNG, WEBP maks 2 MB)</label>
                        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp"
                               class="form-control <?= !empty($errors['image']) ? 'is-invalid' : '' ?>">
                        <?php if (!empty($errors['image'])): ?>
                            <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['image']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-mealmate-green px-4">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4">
            <form action="index.php?page=admin-menus&view=<?= urlencode($viewMode) ?>" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
                <input type="hidden" name="menu_id" id="deleteMenuId" value="0">

                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Hapus Menu?</h6>
                    <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus menu <strong id="deleteMenuName"></strong>?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-3 px-3">Ya, Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkDeleteMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-2">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h6 class="fw-bold mb-2">Hapus Menu Terpilih?</h6>
                <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus <strong id="bulkDeleteCountText">0</strong> menu yang dipilih?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-3 px-3" onclick="document.getElementById('bulkDeleteForm').submit()">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
</div>
