const flashToast = document.getElementById('flash-toast');

if (flashToast) {
    const alertElement = flashToast.querySelector('.alert');

    setTimeout(() => {
        alertElement.classList.remove('show');
        setTimeout(() => flashToast.remove(), 400);
    }, 3000);
}