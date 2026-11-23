const profileForm = document.getElementById('profile-form');

if (profileForm) {
    const submitButton = document.getElementById('profile-submit');
    const fields = profileForm.querySelectorAll('[data-initial]');
    const emailField = document.getElementById('email');
    const modalElement = document.getElementById('email-confirm-modal');
    const passwordField = document.getElementById('current_password');
    const confirmModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    const normalize = (value) => value.replace(/\r\n/g, '\n').trim();
    const isChanged = (field) => normalize(field.value) !== normalize(field.dataset.initial);
    const isEmailChanged = () =>
        normalize(emailField.value).toLowerCase() !== normalize(emailField.dataset.initial).toLowerCase();

    const syncSubmitButton = () => {
        submitButton.disabled = !Array.from(fields).some(isChanged);
    };

    profileForm.addEventListener('input', syncSubmitButton);
    window.addEventListener('pageshow', syncSubmitButton);

    profileForm.addEventListener('submit', (event) => {
        if (isEmailChanged() && !modalElement.classList.contains('show')) {
            event.preventDefault();
            confirmModal.show();
        }
    });

    modalElement.addEventListener('shown.bs.modal', () => passwordField.focus());
    modalElement.addEventListener('hidden.bs.modal', () => {
        passwordField.value = '';
        passwordField.classList.remove('is-invalid');
    });

    syncSubmitButton();

    if (passwordField.classList.contains('is-invalid')) {
        confirmModal.show();
    }
}   