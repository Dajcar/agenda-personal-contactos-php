<?php
require dirname(__DIR__) . '/config/bootstrap.php';
$user = require_login();
$id = positive_id($_GET['id'] ?? null);
if ($id === null) {
    abort_request(404, 'Contacto no encontrado.');
}
$contact = find_contact($id, $user['id']);
if ($contact === null) {
    abort_request(404, 'Contacto no encontrado.');
}
if (is_post()) {
    verify_csrf();
    if (!remove_contact($id, $user['id'])) {
        abort_request(404, 'Contacto no encontrado.');
    }
    flash_set('success', 'Contacto eliminado correctamente.');
    redirect('dashboard.php');
}
$page_title = 'Confirmar eliminación';
require dirname(__DIR__) . '/templates/header.php';
?>
<div class="form-page-heading"><a class="back-link" href="dashboard.php">← Volver a mis contactos</a></div>
<section class="panel delete-panel" aria-labelledby="titulo-eliminar">
  <div class="delete-icon" aria-hidden="true">!</div>
  <span class="eyebrow">Confirmación obligatoria</span>
  <h1 id="titulo-eliminar">¿Eliminar este contacto?</h1>
  <p>Vas a eliminar a <strong><?= e($contact['nombre']) ?></strong> de tu agenda. Esta acción no se puede deshacer.</p>
  <form method="post" action="contacto_eliminar.php?id=<?= (int) $contact['id'] ?>" class="form-actions">
    <?= csrf_field() ?>
    <button class="button button-danger" type="submit">Sí, eliminar contacto</button>
    <a class="button button-secondary" href="dashboard.php">Cancelar</a>
  </form>
</section>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
