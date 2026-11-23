<?php
require dirname(__DIR__) . '/config/bootstrap.php';
$user = require_login();
if (!is_post()) abort_request(405, 'Método no permitido.');
verify_csrf();
$id = positive_id($_POST['id'] ?? null);
$favorito = form_text('favorito');
if ($id === null || !in_array($favorito, ['0', '1'], true)) {
    abort_request(400, 'Solicitud no válida.');
}
if (find_contact($id, $user['id']) === null) abort_request(404, 'Contacto no encontrado.');
set_contact_favorite($id, $user['id'], $favorito === '1');
flash_set('success', $favorito === '1' ? 'Contacto añadido a favoritos.' : 'Contacto quitado de favoritos.');
redirect('dashboard.php');
