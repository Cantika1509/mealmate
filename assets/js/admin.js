function openMenuModal(mode, id = 0, name = '', desc = '') {
    document.getElementById('menuFormAction').value = mode;
    document.getElementById('menuFormId').value = id;
    document.getElementById('menu_name').value = name;
    document.getElementById('description').value = desc;
    var imgInput = document.getElementById('image');
    if (imgInput) imgInput.value = '';
    document.getElementById('menuFormTitle').textContent = mode === 'update' ? 'Ubah Data Menu' : 'Tambah Menu Baru';
    new bootstrap.Modal(document.getElementById('menuFormModal')).show();
}

function openMenuModalFromBtn(btn) {
    openMenuModal('update', btn.dataset.id, btn.dataset.name, btn.dataset.desc);
}

function openDeleteMenuModal(btn) {
    document.getElementById('deleteMenuId').value = btn.dataset.id;
    document.getElementById('deleteMenuName').textContent = btn.dataset.name;
    new bootstrap.Modal(document.getElementById('deleteMenuModal')).show();
}

function toggleSelectAllMenus(masterCheckbox) {
    document.querySelectorAll('.menu-row-checkbox').forEach(function (cb) {
        cb.checked = masterCheckbox.checked;
    });
    updateBulkDeleteState();
}

function updateBulkDeleteState() {
    var checkboxes = document.querySelectorAll('.menu-row-checkbox');
    var checked = document.querySelectorAll('.menu-row-checkbox:checked');
    var btn = document.getElementById('bulkDeleteBtn');
    var countLabel = document.getElementById('selectedCountLabel');
    var selectAll = document.getElementById('selectAllMenus');

    if (countLabel) {
        countLabel.textContent = checked.length;
    }
    if (btn) {
        btn.classList.toggle('d-none', checked.length === 0);
    }
    if (selectAll && checkboxes.length > 0) {
        selectAll.checked = checked.length === checkboxes.length;
    }
}

function openBulkDeleteModal() {
    var checked = document.querySelectorAll('.menu-row-checkbox:checked');
    if (checked.length === 0) return;
    document.getElementById('bulkDeleteCountText').textContent = checked.length;
    new bootstrap.Modal(document.getElementById('bulkDeleteMenuModal')).show();
}

function openScheduleModal(date = '', slot = 'Pagi', menuId = '', origDate = '', origSlot = '') {
    var dateInput = document.getElementById('schedule_date');
    dateInput.value = date || dateInput.dataset.today || '';
    document.getElementById('schedule_slot').value = slot;
    document.getElementById('schedule_menu_id').value = menuId;
    document.getElementById('scheduleOriginalDate').value = origDate;
    document.getElementById('scheduleOriginalSlot').value = origSlot;
    document.getElementById('scheduleModalTitle').textContent = origDate ? 'Ubah Jadwal Menu' : 'Tambah Jadwal Menu';
    new bootstrap.Modal(document.getElementById('scheduleFormModal')).show();
}

function openScheduleModalFromBtn(btn) {
    openScheduleModal(btn.dataset.date, btn.dataset.slot, btn.dataset.menuId, btn.dataset.date, btn.dataset.slot);
}

function openDeleteScheduleModal(btn) {
    document.getElementById('deleteScheduleDate').value = btn.dataset.date;
    document.getElementById('deleteScheduleSlot').value = btn.dataset.slot;
    document.getElementById('deleteScheduleLabel').textContent = btn.dataset.label;
    new bootstrap.Modal(document.getElementById('deleteScheduleModal')).show();
}

window.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal[data-auto-open="true"]').forEach(function (modalEl) {
        new bootstrap.Modal(modalEl).show();
    });

    var input = document.getElementById('adminGlobalSearchInput');
    var dropdown = document.getElementById('adminGlobalSearchDropdown');
    var container = document.getElementById('adminGlobalSearchContainer');
    if (!input || !dropdown || !container) return;

    var timer = null;
    var activeReqId = 0;

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function renderResults(groups, query) {
        if (!groups || groups.length === 0) {
            dropdown.innerHTML = '<div class="p-3 text-center text-muted small">Tidak ditemukan hasil untuk "<strong>' + escapeHtml(query) + '</strong>"</div>';
            dropdown.classList.remove('d-none');
            return;
        }

        var html = '';
        groups.forEach(function (group) {
            html += '<div class="admin-search-group-label">' + escapeHtml(group.label) + '</div>';
            group.items.forEach(function (item) {
                html += '<a href="' + escapeHtml(item.url) + '" class="admin-search-item d-flex align-items-center gap-3 text-decoration-none">' +
                    '<div class="admin-search-item-icon"><i class="bi ' + escapeHtml(item.icon) + '"></i></div>' +
                    '<div class="min-w-0 flex-grow-1">' +
                        '<div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">' + escapeHtml(item.title) + '</div>' +
                        '<div class="text-muted text-truncate" style="font-size: 11.5px;">' + escapeHtml(item.subtitle) + '</div>' +
                    '</div>' +
                    '<i class="bi bi-chevron-right text-muted small"></i>' +
                '</a>';
            });
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var currentReq = ++activeReqId;
        var q = input.value.trim();
        if (q.length < 1) {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
            return;
        }

        timer = setTimeout(function () {
            fetch('index.php?page=admin-search&q=' + encodeURIComponent(q))
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (currentReq !== activeReqId) return;
                    renderResults(data.groups || [], q);
                })
                .catch(function () {
                    if (currentReq !== activeReqId) return;
                    dropdown.classList.add('d-none');
                });
        }, 200);
    });

    input.addEventListener('focus', function () {
        if (input.value.trim().length > 0 && dropdown.innerHTML !== '') {
            dropdown.classList.remove('d-none');
        }
    });

    document.addEventListener('click', function (e) {
        if (!container.contains(e.target)) {
            dropdown.classList.add('d-none');
        }
    });
});
