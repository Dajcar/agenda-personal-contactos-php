<?php
require dirname(__DIR__) . '/config/bootstrap.php';
require_login();
if (!is_post()) {
    abort_request(405, 'Para cerrar sesión, utiliza el botón de la aplicación.');
}
verify_csrf();
log_out();
redirect('login.php?salida=1');
