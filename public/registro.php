<?php
require dirname(__DIR__) . '/config/bootstrap.php';
require_guest();

$page_title = 'Crear cuenta';
$errors = [];
$values = ['nombre' => '', 'correo' => '', 'password' => '', 'confirmacion' => ''];

if (is_post()) {
    verify_csrf();
    $values = [
        'nombre' => form_text('nombre'),
        'correo' => strtolower(form_text('correo')),
        'password' => form_password('password'),
        'confirmacion' => form_password('confirmacion'),
    ];
    $errors = validate_registration($values);
    if (!$errors) {
        try {
            $stmt = db()->prepare('INSERT INTO usuarios (nombre, correo, password) VALUES (:nombre, :correo, :hash)');
            $stmt->execute([
                'nombre' => $values['nombre'],
                'correo' => $values['correo'],
                'hash' => password_hash($values['password'], PASSWORD_DEFAULT),
            ]);
            flash_set('success', 'Tu cuenta se creó correctamente. Ya puedes iniciar sesión.');
            redirect('login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors['correo'] = 'Ese correo ya está registrado. Prueba con otro.';
            } else {
                error_log('No se pudo registrar usuario: ' . $exception->getMessage());
                abort_request(500, 'No se pudo completar el registro. Inténtalo más tarde.');
            }
        }
    }
}
require dirname(__DIR__) . '/templates/header.php';
?>
<div class="auth-layout">
  <section class="auth-intro">
    <span class="eyebrow">Comienza aquí</span>
    <h1>Tu agenda, <span>tu espacio privado.</span></h1>
    <p>Crea una cuenta para guardar tus contactos y consultarlos de forma segura desde una sola aplicación.</p>
    <div class="feature-note"><span class="feature-icon">✓</span> Información separada para cada usuario.</div>
    <div class="feature-note"><span class="feature-icon">✓</span> Contraseñas protegidas con hash.</div>
  </section>
  <section class="panel auth-panel" aria-labelledby="titulo-registro">
    <span class="eyebrow">Registro de usuario</span>
    <h2 id="titulo-registro">Crear una cuenta</h2>
    <p class="muted">Completa tus datos para comenzar.</p>
    <form method="post" action="registro.php" class="stack-form" novalidate>
      <?= csrf_field() ?>
      <div class="form-field">
        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" type="text" maxlength="100" required autocomplete="name" value="<?= e($values['nombre']) ?>" placeholder="Tu nombre y apellido" aria-describedby="error-nombre">
        <?php if (isset($errors['nombre'])): ?><p id="error-nombre" class="field-error"><?= e($errors['nombre']) ?></p><?php endif; ?>
      </div>
      <div class="form-field">
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" maxlength="190" required autocomplete="email" value="<?= e($values['correo']) ?>" placeholder="tu@correo.com" aria-describedby="error-correo">
        <?php if (isset($errors['correo'])): ?><p id="error-correo" class="field-error"><?= e($errors['correo']) ?></p><?php endif; ?>
      </div>
      <div class="form-field">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Al menos 8 caracteres" aria-describedby="password-ayuda error-password">
        <p id="password-ayuda" class="hint">Entre 8 y 72 bytes; no se guarda en texto plano.</p>
        <?php if (isset($errors['password'])): ?><p id="error-password" class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
      </div>
      <div class="form-field">
        <label for="confirmacion">Confirmar contraseña</label>
        <input id="confirmacion" name="confirmacion" type="password" required autocomplete="new-password" placeholder="Repite tu contraseña" aria-describedby="error-confirmacion">
        <?php if (isset($errors['confirmacion'])): ?><p id="error-confirmacion" class="field-error"><?= e($errors['confirmacion']) ?></p><?php endif; ?>
      </div>
      <button type="submit" class="button button-primary button-full">Crear cuenta</button>
    </form>
    <p class="auth-switch">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
  </section>
</div>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
