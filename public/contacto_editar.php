<?php
require dirname(__DIR__) . '/config/bootstrap.php';
$user = require_login();
$id = positive_id($_GET['id'] ?? null);
if ($id === null) abort_request(404, 'Contacto no encontrado.');
$contact = find_contact($id, $user['id']);
if ($contact === null) abort_request(404, 'Contacto no encontrado.');
$categories = list_categories();
$allowedCategoryIds = array_map('intval', array_column($categories, 'id'));
$page_title = 'Editar contacto';
$values = [
    'nombre' => $contact['nombre'], 'telefono' => $contact['telefono'],
    'correo' => $contact['correo'], 'ciudad' => $contact['ciudad'],
    'id_categoria' => (string)$contact['id_categoria'], 'favorito' => (string)$contact['favorito'],
];
$errors = [];
if (is_post()) {
    verify_csrf();
    foreach (['nombre','telefono','correo','ciudad','id_categoria'] as $key) {
        $values[$key] = form_text($key);
    }
    $values['favorito'] = array_key_exists('favorito', $_POST) ? form_text('favorito') : '0';
    $values['correo'] = strtolower($values['correo']);
    $errors = validate_contact($values, $allowedCategoryIds);
    if (!$errors) {
        edit_contact($id, $user['id'], $values);
        flash_set('success', 'Contacto actualizado correctamente.');
        redirect('dashboard.php');
    }
}
$action = 'contacto_editar.php?id=' . $id;
$button_label = 'Guardar cambios';
require dirname(__DIR__) . '/templates/header.php';
?>
<div class="form-page-heading"><a class="back-link" href="dashboard.php">← Volver a mis contactos</a><p class="eyebrow">Agenda personal</p><h1>Editar contacto</h1><p class="muted">Actualiza los datos, la categoría o el favorito.</p></div>
<section class="panel form-panel" aria-label="Formulario para editar contacto">
  <?php require dirname(__DIR__) . '/templates/contact_form.php'; ?>
</section>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
