<?php
declare(strict_types=1);
// Renderiza el formulario real sin MySQL y comprueba HTML. NO prueba el hosting.
ob_start();
require dirname(__DIR__) . '/src/helpers.php';
require dirname(__DIR__) . '/src/security.php';
session_start();
$_SESSION = [];
$categories = [
    ['id'=>1,'nombre'=>'Familia'], ['id'=>2,'nombre'=>'Amigos'],
    ['id'=>3,'nombre'=>'Trabajo'], ['id'=>4,'nombre'=>'Universidad'], ['id'=>5,'nombre'=>'Otro'],
];
$errors=[];
$values=['nombre'=>'Ana <script>alert(1)</script>','telefono'=>'0987654321', 'correo'=>'ana@example.com',
    'ciudad'=>'Zamora', 'id_categoria'=>'4', 'favorito'=>'1'];
$action='contacto_editar.php?id=1';
$button_label='Guardar cambios';
ob_start();
require dirname(__DIR__) . '/templates/contact_form.php';
$html=ob_get_clean();
$checks = [
    'Formulario POST protegido con CSRF' => str_contains($html,'method="post"') && str_contains($html,'name="csrf_token"'),
    'Opción Familia disponible' => str_contains($html,'>Familia</option>'),
    'Opción Amigos disponible' => str_contains($html,'>Amigos</option>'),
    'Opción Trabajo disponible' => str_contains($html,'>Trabajo</option>'),
    'Opción Universidad seleccionada' => preg_match('/value="4"\s+selected/', $html)===1,
    'Opción Otro disponible' => str_contains($html,'>Otro</option>'),
    'Casilla Favorito marcada correctamente' => str_contains($html,'name="favorito"') && str_contains($html,'type="checkbox"') && str_contains($html,'checked'),
    'Escapado de XSS en campo nombre' => str_contains($html,'Ana &lt;script&gt;') && !str_contains($html,'value="Ana <script>'),
];
foreach ($checks as $name => $result) {
    echo ($result ? 'OK ' : 'FALLO ') . $name . "\n";
}
echo "\nRESULTADO: ".count(array_filter($checks)).'/'.count($checks)." comprobaciones de formulario.\n";
ob_end_flush();
exit(in_array(false,$checks,true) ? 1 : 0);
