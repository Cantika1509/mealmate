document.addEventListener('DOMContentLoaded', () => {
    const startDateInput = document.getElementById('checkoutStartDate');
    const endDateDisplay = document.getElementById('calculatedEndDateDisplay');
    const weekendWarning = document.getElementById('weekendWarningText');
    const addressInput = document.getElementById('checkoutAddress');
    const addressCounter = document.getElementById('addressCharCounter');

    const monthsIndo = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    function calculateEndDate(startDateStr, durationDays, applicableDays) {
        const parts = startDateStr.split('-').map(Number);
        if (parts.length !== 3 || !parts[0] || !parts[1] || !parts[2]) {
            return null;
        }

        const current = new Date(parts[0], parts[1] - 1, parts[2]);
        let remaining = Math.max(1, durationDays);
        const weekdaysOnly = (applicableDays === 'Senin-Jumat');

        while (true) {
            const dayOfWeek = current.getDay();
            const isWeekday = (dayOfWeek >= 1 && dayOfWeek <= 5);

            if (!weekdaysOnly || isWeekday) {
                remaining--;
                if (remaining <= 0) {
                    break;
                }
            }
            current.setDate(current.getDate() + 1);
        }

        const day = String(current.getDate()).padStart(2, '0');
        const month = monthsIndo[current.getMonth()] || '';
        const year = current.getFullYear();
        return `${day} ${month} ${year}`;
    }

    if (startDateInput && endDateDisplay) {
        const durationDays = parseInt(startDateInput.dataset.durationDays || '1', 10);
        const applicableDays = startDateInput.dataset.applicableDays || 'Senin-Minggu';

        const updateEndDate = () => {
            const val = startDateInput.value;
            if (!val) {
                return;
            }

            const parts = val.split('-').map(Number);
            const picked = new Date(parts[0], parts[1] - 1, parts[2]);
            const dayOfWeek = picked.getDay();
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);

            if (weekendWarning) {
                weekendWarning.classList.toggle('d-none', !(applicableDays === 'Senin-Jumat' && isWeekend));
            }

            const formatted = calculateEndDate(val, durationDays, applicableDays);
            if (formatted) {
                endDateDisplay.textContent = formatted;
            }
        };

        startDateInput.addEventListener('change', updateEndDate);
        startDateInput.addEventListener('input', updateEndDate);
    }

    if (addressInput && addressCounter) {
        const updateCharCount = () => {
            const len = addressInput.value.length;
            addressCounter.textContent = `${len}/500`;
        };
        addressInput.addEventListener('input', updateCharCount);
        updateCharCount();
    }
});
