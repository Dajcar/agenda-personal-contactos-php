<?php
require dirname(__DIR__) . '/config/bootstrap.php';
require_guest();

$page_title = 'Iniciar sesión';
$errors = [];
$email = '';

if (is_post()) {
    verify_csrf();
    $email = strtolower(form_text('correo'));
    $password = form_password('password');
    $blockedUntil = $_SESSION['login_blocked_until'] ?? 0;
    if (is_int($blockedUntil) && $blockedUntil > time()) {
        $errors['general'] = 'Demasiados intentos. Espera un minuto y vuelve a intentarlo.';
    } elseif (!valid_email($email) || $password === '') {
        $errors['general'] = 'Ingresa un correo y una contraseña válidos.';
    } else {
        $user = authenticate($email, $password);
        if ($user !== null) {
            log_in($user);
            redirect('dashboard.php');
        }
        $_SESSION['login_failures'] = (int) ($_SESSION['login_failures'] ?? 0) + 1;
        if ($_SESSION['login_failures'] >= 5) {
            $_SESSION['login_blocked_until'] = time() + 60;
            $_SESSION['login_failures'] = 0;
        }
        $errors['general'] = 'Correo o contraseña incorrectos.';
    }
}
require dirname(__DIR__) . '/templates/header.php';
?>
<div class="auth-layout">
  <section class="auth-intro">
    <span class="eyebrow">Organiza mejor tu día</span>
    <h1>Tus contactos, <span>siempre a mano.</span></h1>
    <p>Tu agenda personal para registrar, encontrar y actualizar la información de tus contactos.</p>
    <div class="intro-visual" aria-hidden="true">
      <div class="visual-circle circle-one"></div><div class="visual-circle circle-two"></div>
      <div class="visual-card card-back"><span class="visual-avatar">JL</span><span class="visual-lines">Jorge López<small>Loja, Ecuador</small></span></div>
      <div class="visual-card card-front"><span class="visual-avatar violet">MC</span><span class="visual-lines">María Cueva<small>Zamora, Ecuador</small></span><span class="visual-check">✓</span></div>
    </div>
  </section>
  <section class="panel auth-panel" aria-labelledby="titulo-login">
    <span class="eyebrow">Bienvenido de nuevo</span>
    <h2 id="titulo-login">Iniciar sesión</h2>
    <p class="muted">Accede con tu correo y contraseña.</p>
    <?php require dirname(__DIR__) . '/templates/flash.php'; ?>
    <?php if (isset($_GET['salida'])): ?><div class="alert alert-success" role="status">Has cerrado la sesión correctamente.</div><?php endif; ?>
    <?php if (isset($_GET['acceso'])): ?><div class="alert alert-error" role="status">Inicia sesión para acceder a tu agenda.</div><?php endif; ?>
    <?php if (!empty($_SESSION['expired'])): unset($_SESSION['expired']); ?><div class="alert alert-error" role="status">La sesión expiró por inactividad. Vuelve a ingresar.</div><?php endif; ?>
    <?php if (isset($errors['general'])): ?><div class="alert alert-error" role="alert"><?= e($errors['general']) ?></div><?php endif; ?>
    <form method="post" action="login.php" class="stack-form" novalidate>
      <?= csrf_field() ?>
      <div class="form-field">
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" required maxlength="190" autocomplete="username" value="<?= e($email) ?>" placeholder="tu@correo.com">
      </div>
      <div class="form-field">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña">
      </div>
      <button type="submit" class="button button-primary button-full">Ingresar a mi agenda</button>
    </form>
    <p class="auth-switch">¿Todavía no tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
  </section>
</div>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
