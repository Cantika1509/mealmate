function togglePasswordVisibility(inputId, btnElem) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = btnElem.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    }
}
