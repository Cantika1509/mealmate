let currentMode = 'daily';
let currentWeeklyDay = 'Senin';

// escape teks database supaya tidak dijalankan sebagai HTML
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
} 

// switch mode daily or weekly
function switchMode(mode) {
    currentMode = mode;
    
    const btnDaily = document.getElementById('btn-mode-daily');
    const btnWeeklyDesktop = document.getElementById('btn-mode-weekly');
    const btnWeeklyMobile = document.getElementById('btn-mode-weekly-mobile');
    const selectorContainer = document.getElementById('weekly-selector-container');

    if (mode === 'daily') {
        // tombol harian aktif
        btnDaily.classList.add('active');
        btnDaily.classList.remove('non-active');

        // tombol mingguan mati
      if (btnWeeklyDesktop) {
            btnWeeklyDesktop.classList.add('non-active');
            btnWeeklyDesktop.classList.remove('active');
        }  
        if (btnWeeklyMobile) {
            btnWeeklyMobile.classList.add('non-active');
            btnWeeklyMobile.classList.remove('active');
            document.getElementById('mobile-dropdown-label').innerText = 'Menu Minggu ini';
        }
        
        selectorContainer.classList.add('d-none');
        updateHeader("Menu Hari ini", "Menu yang tersedia untuk hari ini", todayFormatted);
        renderCards(dailyData);
    } else {
        // mode mingguan dekstop
        btnDaily.classList.add('non-active');
        btnDaily.classList.remove('active');

        if (btnWeeklyDesktop) {
            btnWeeklyDesktop.classList.add('active');
            btnWeeklyDesktop.classList.remove('non-active');
        }
        
        selectorContainer.classList.remove('d-none');
        selectWeeklyDay(currentWeeklyDay, false);
    }
}


function selectWeeklyDay(day, isMobileSelect = false) {
    currentWeeklyDay = day;

    const btnDaily = document.getElementById('btn-mode-daily');
    const btnWeeklyDesktop = document.getElementById('btn-mode-weekly');
    const btnWeeklyMobile = document.getElementById('btn-mode-weekly-mobile');

    // pemicu dari dropdown mobile
    if (isMobileSelect) {
        currentMode = 'weekly';
        
        // tombol harian mati
        btnDaily.classList.add('non-active');
        btnDaily.classList.remove('active');
        
        // ngubah label ke hari yang dipilih di dropdown
        if (btnWeeklyMobile) {
            btnWeeklyMobile.classList.add('active');
            btnWeeklyMobile.classList.remove('non-active');
            document.getElementById('mobile-dropdown-label').innerText = `${day}`;
        }

        // jika pakai mobile di desktop ngilang
        if (btnWeeklyDesktop) {
            btnWeeklyDesktop.classList.add('active');
            btnWeeklyDesktop.classList.remove('non-active');
        }
    }

    // radio button dekstop
    const radioElem = document.getElementById(`day-${day}`);
    if (radioElem) radioElem.checked = true;

    // update hari
    const dayData = weeklyData[day] || {};
    const menus = dayData.menus || [];
    const dateText = dayData.tanggal || day;

    updateHeader(`Menu Hari ${day}`, `Menu yang tersedia untuk hari ${day}`, dateText);
    renderCards(menus);
}

// update dmc-1
function updateHeader(title, subtitle, date) {
    document.getElementById('header-title').innerText = title;
    document.getElementById('header-subtitle').innerText = subtitle;
    document.getElementById('header-date').innerText = date;
}

// render card di dmc-3
function renderCards(menus) {
    const container = document.getElementById('menu-cards-container');
    container.innerHTML = '';

    if (!menus || menus.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-2"><p class="text-muted">Menu belum tersedia.</p></div>';
        return;
    }

    menus.forEach(item => {
        //badge & icon berdasarkan slot waktu
        let badgeClass = 'badge-pagi';
        let iconClass = 'bi-sunrise';

        if (item.slot === 'Siang') {
            badgeClass = 'badge-siang';
            iconClass = 'bi-sun-fill';
        } else if (item.slot === 'Malam') {
            badgeClass = 'badge-malam';
            iconClass = 'bi-moon-stars';
        }

        // rating dari controller
        const rating = Number(item.rating) || 0;     // menu tanpa ulasan = 0 bintang
        const reviews = Number(item.reviews) || 0;   // jumlah ulasan default 0
        let starsHtml = '';

        for (let i = 1; i <= 5; i++) {
            if (rating >= i) {
                // bintang Penuh
                starsHtml += '<i class="bi bi-star-fill"></i>';
            } else if (rating >= i - 0.5) {
                // bintang setengah (rating x.5)
                starsHtml += '<i class="bi bi-star-half"></i>';
            } else {
                // bintang Kosong
                starsHtml += '<i class="bi bi-star"></i>';
            }
        }

        // template card di dmc-3
        const cardHtml = `
            <div class="card-container col-md d-flex justify-content-center align-items-center p-0 gap-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="max-width: 400px;">
                    <div class="position-relative">
                        <img src="./assets/img/${escapeHtml(item.image)}" class="card-img-top" alt="${escapeHtml(item.menu_name)}" loading="lazy" style="width: 100%; height: 220px; object-fit: cover;">

                        <span class="badge-slot ${badgeClass}">
                            <i class="bi ${iconClass}"></i>
                            ${escapeHtml(item.slot)}
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-success mb-2">${escapeHtml(item.menu_name)}</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            ${escapeHtml(item.description)}
                        </p>
                        
                        <div class="d-flex align-items-center gap-1 mt-2">
                            <div class="text-warning small">
                                ${starsHtml}
                            </div>
                            <span class="text-muted small fw-semibold ms-1">(${reviews})</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += cardHtml;
    });
}

// jalanin switch mode daily ketika html siap rawrrr
document.addEventListener('DOMContentLoaded', () => {
    // file dimuat di semua halaman lewat footer.php, jadi cek dulu elemennya ada
    if (document.getElementById('btn-mode-daily')) {
        switchMode('daily');
    }
    initPackageSlider();
});

function initPackageSlider() {
    const track = document.getElementById('package-track');
    const prev = document.getElementById('package-prev');
    const next = document.getElementById('package-next');
    if (!track || !prev || !next) return;

    const step = () => {
        const card = track.querySelector('.package-card');
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        return card ? card.offsetWidth + gap : track.clientWidth;
    };

    prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
}