<?php

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function setValidationErrors(array $errors): void {
    $_SESSION['validation_errors'] = $errors;
}

function getValidationErrors(): array {
    $errors = $_SESSION['validation_errors'] ?? [];
    unset($_SESSION['validation_errors']);
    return $errors;
}

function setOldInput(array $input): void {
    unset($input['password'], $input['old_password'], $input['new_password'], $input['confirm_password']);
    $_SESSION['old_input'] = $input;
}

function getOldInput(): array {
    $old = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    return $old;
}

function redirect(string $url): never {
    header("Location: {$url}");
    exit;
}

function redirectWithErrors(string $url, array $errors, array $old = []): never {
    setValidationErrors($errors);
    if (!empty($old)) {
        setOldInput($old);
    }
    redirect($url);
}
