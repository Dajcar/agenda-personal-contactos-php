<?php
require dirname(__DIR__) . '/config/bootstrap.php';
$user = require_login();
$page_title = 'Nuevo contacto';
$categories = list_categories();
$allowedCategoryIds = array_map('intval', array_column($categories, 'id'));
$errors = [];
$values = ['nombre' => '', 'telefono' => '', 'correo' => '', 'ciudad' => '', 'id_categoria' => '5', 'favorito' => '0'];
if (is_post()) {
    verify_csrf();
    foreach (['nombre','telefono','correo','ciudad','id_categoria'] as $key) {
        $values[$key] = form_text($key);
    }
    $values['favorito'] = array_key_exists('favorito', $_POST) ? form_text('favorito') : '0';
    $values['correo'] = strtolower($values['correo']);
    $errors = validate_contact($values, $allowedCategoryIds);
    if (!$errors) {
        add_contact($user['id'], $values);
        flash_set('success', 'Contacto agregado correctamente.');
        redirect('dashboard.php');
    }
}
$action = 'contacto_nuevo.php';
$button_label = 'Guardar contacto';
require dirname(__DIR__) . '/templates/header.php';
?>
<div class="form-page-heading"><a class="back-link" href="dashboard.php">← Volver a mis contactos</a><p class="eyebrow">Agenda personal</p><h1>Nuevo contacto</h1><p class="muted">Organiza tu directorio por categorías y favoritos.</p></div>
<section class="panel form-panel" aria-label="Formulario para nuevo contacto">
  <?php require dirname(__DIR__) . '/templates/contact_form.php'; ?>
</section>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
