<?php
/** @var string $page_title */
$user_for_menu = current_user();
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title><?= e($page_title) ?> · Agenda Personal</title>
  <meta name="description" content="Agenda personal de contactos con autenticación segura en PHP.">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <header class="site-header">
    <div class="shell topbar">
      <a href="<?= $user_for_menu ? 'dashboard.php' : 'login.php' ?>" class="brand" aria-label="Agenda Personal, ir al inicio">
        <span class="brand-symbol" aria-hidden="true">A</span>
        <span>Agenda<span class="brand-subtitle">Personal</span></span>
      </a>
      <nav class="main-nav" aria-label="Navegación principal">
        <?php if ($user_for_menu): ?>
          <span class="user-chip" title="Sesión iniciada"> <span aria-hidden="true">●</span> <?= e($user_for_menu['nombre']) ?></span>
          <form method="post" action="logout.php">
            <?= csrf_field() ?>
            <button type="submit" class="nav-button">Cerrar sesión</button>
          </form>
        <?php else: ?>
          <a href="login.php">Iniciar sesión</a>
          <a href="registro.php" class="nav-button">Crear cuenta</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main id="contenido" class="shell main-content">
