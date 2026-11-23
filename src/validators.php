<?php
declare(strict_types=1);

function length_between(string $value, int $minimum, int $maximum): bool
{
    // PCRE permite contar caracteres Unicode sin depender de ext-mbstring.
    return preg_match('/\A.{'.$minimum.','.$maximum.'}\z/us', $value) === 1
        && preg_match('/[\x00-\x1F\x7F]/u', $value) !== 1;
}

function valid_email(string $correo): bool
{
    return strlen($correo) <= 190 && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/** @return array<string,string> */
function validate_registration(array $values): array
{
    $errors = [];
    if (!length_between($values['nombre'], 2, 100)) {
        $errors['nombre'] = 'El nombre debe contener entre 2 y 100 caracteres.';
    }
    if (!valid_email($values['correo'])) {
        $errors['correo'] = 'Ingresa un correo electrónico válido (máximo 190 caracteres).';
    }
    $size = strlen($values['password']);
    if ($size < 8 || $size > 72) {
        $errors['password'] = 'La contraseña debe tener entre 8 y 72 bytes.';
    }
    if ($values['password'] !== $values['confirmacion']) {
        $errors['confirmacion'] = 'Las contraseñas no coinciden.';
    }
    return $errors;
}

/** @return array<string,string> */
function validate_contact(array $values, ?array $allowedCategoryIds = null): array
{
    $errors = [];
    if (!length_between($values['nombre'], 2, 100)) {
        $errors['nombre'] = 'El nombre debe contener entre 2 y 100 caracteres.';
    }
    $phone = $values['telefono'];
    if (strlen($phone) > 30 || preg_match('/\A[0-9+() .-]+\z/D', $phone) !== 1
        || preg_match_all('/[0-9]/', $phone) < 7) {
        $errors['telefono'] = 'Ingresa un teléfono válido de al menos 7 dígitos.';
    }
    if (!valid_email($values['correo'])) {
        $errors['correo'] = 'Ingresa un correo electrónico válido (máximo 190 caracteres).';
    }
    if (!length_between($values['ciudad'], 2, 100)) {
        $errors['ciudad'] = 'La ciudad debe contener entre 2 y 100 caracteres.';
    }
    if ($allowedCategoryIds !== null) {
        $categoryId = positive_id($values['id_categoria'] ?? null);
        if ($categoryId === null || !in_array($categoryId, $allowedCategoryIds, true)) {
            $errors['id_categoria'] = 'Selecciona una categoría válida.';
        }
        if (!in_array($values['favorito'] ?? null, ['0', '1'], true)) {
            $errors['favorito'] = 'Selecciona un valor válido para favorito.';
        }
    }
    return $errors;
}
