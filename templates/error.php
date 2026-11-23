<?php require __DIR__ . '/header.php'; ?>
<section class="panel message-page">
  <div class="error-badge" aria-hidden="true">!</div>
  <p class="eyebrow">No se pudo completar la solicitud</p>
  <h1><?= e((string) http_response_code()) ?></h1>
  <p><?= e($error_message) ?></p>
  <a class="button button-primary" href="<?= current_user() ? 'dashboard.php' : 'login.php' ?>">Volver al inicio</a>
</section>
<?php require __DIR__ . '/footer.php'; ?>
