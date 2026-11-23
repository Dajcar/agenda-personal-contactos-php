<?php
require dirname(__DIR__) . '/config/bootstrap.php';
$user = require_login();
$filters = contact_filters_from_query($_GET);
$categories = list_categories();
$contacts = list_contacts($user['id'], $filters);
$counts = contact_counts($user['id']);
$hasFilters = $filters['q'] !== '' || $filters['categoria'] > 0 || $filters['favoritos'];
$page_title = 'Mis contactos';
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="dashboard-top">
  <div>
    <p class="eyebrow">Panel personal · Versión 2.0</p>
    <h1>Hola, <?= e($user['nombre']) ?> <span class="wave" aria-hidden="true">✳</span></h1>
    <p class="muted">Tu correo: <strong><?= e($user['correo']) ?></strong></p>
  </div>
  <a class="button button-primary" href="contacto_nuevo.php"><span aria-hidden="true">＋</span> Nuevo contacto</a>
</section>
<?php require dirname(__DIR__) . '/templates/flash.php'; ?>
<section class="summary-grid" aria-label="Resumen de agenda">
  <div class="summary-tile"><div class="summary-icon">◎</div><div><span class="summary-number"><?= $counts['total'] ?></span><span class="summary-label">Contactos guardados</span></div></div>
  <div class="summary-tile"><div class="summary-icon summary-icon-light">★</div><div><span class="summary-number"><?= $counts['favoritos'] ?></span><span class="summary-label">Favoritos</span></div></div>
</section>
<section class="panel filter-panel" aria-labelledby="titulo-filtros">
  <div class="section-heading"><div><span class="eyebrow">Explora tu agenda</span><h2 id="titulo-filtros">Buscar y organizar</h2></div></div>
  <form method="get" action="dashboard.php" class="filter-grid">
    <div class="form-field filter-search">
      <label for="q">Buscar por nombre, correo o ciudad</label>
      <input id="q" name="q" type="search" maxlength="100" value="<?= e($filters['q']) ?>" placeholder="Ej. Zamora, Ana, correo...">
    </div>
    <div class="form-field">
      <label for="categoria">Categoría</label>
      <select name="categoria" id="categoria">
        <option value="">Todas</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= (int)$category['id'] ?>" <?= $filters['categoria'] === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-field">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden">
        <?php foreach (['nombre'=>'Nombre','ciudad'=>'Ciudad','fecha'=>'Fecha de creación'] as $key=>$label): ?>
          <option value="<?= e($key) ?>" <?= $filters['orden'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-field">
      <label for="direccion">Dirección</label>
      <select name="direccion" id="direccion">
        <option value="asc" <?= $filters['direccion'] === 'asc' ? 'selected' : '' ?>>Ascendente</option>
        <option value="desc" <?= $filters['direccion'] === 'desc' ? 'selected' : '' ?>>Descendente</option>
      </select>
    </div>
    <label class="checkbox-label filter-favorite" for="favoritos">
      <input id="favoritos" type="checkbox" name="favoritos" value="1" <?= $filters['favoritos'] ? 'checked' : '' ?>>
      <span>★ Mostrar solo favoritos</span>
    </label>
    <div class="filter-buttons">
      <button type="submit" class="button button-primary">Aplicar filtros</button>
      <a href="dashboard.php" class="button button-secondary">Restablecer</a>
    </div>
  </form>
</section>
<section class="contacts-section" aria-labelledby="titulo-contactos">
  <div class="section-heading"><div><span class="eyebrow">Tu directorio privado</span><h2 id="titulo-contactos">Mis contactos</h2></div><span class="pill"><?= count($contacts) ?> <?= $hasFilters ? 'resultados' : 'registrados' ?></span></div>
  <?php if ($contacts === []): ?>
    <div class="panel empty-state">
      <div class="empty-icon" aria-hidden="true">♧</div>
      <?php if ($counts['total'] === 0): ?>
        <h3>Tu agenda está lista para comenzar.</h3>
        <p>Aún no tienes contactos registrados. Agrega el primero para empezar a organizarte.</p>
        <a class="button button-primary" href="contacto_nuevo.php">Agregar mi primer contacto</a>
      <?php else: ?>
        <h3>No hay contactos con esos filtros.</h3>
        <p>Prueba otra búsqueda, categoría o selección de favoritos.</p>
        <a class="button button-secondary" href="dashboard.php">Ver todos los contactos</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="contact-grid">
      <?php foreach ($contacts as $contact): ?>
        <article class="panel contact-card">
          <div class="contact-top">
            <span class="contact-avatar" aria-hidden="true"><?= e(preg_match('/\A./us', $contact['nombre'], $initial) ? $initial[0] : '?') ?></span>
            <span class="location-tag"><?= e($contact['ciudad']) ?></span>
          </div>
          <div class="contact-heading"><h3><?= e($contact['nombre']) ?></h3><span class="category-tag"><?= e($contact['categoria']) ?></span></div>
          <dl class="contact-details">
            <div><dt>Teléfono</dt><dd><?= e($contact['telefono']) ?></dd></div>
            <div><dt>Correo</dt><dd class="email-break"><?= e($contact['correo']) ?></dd></div>
            <div><dt>Ciudad</dt><dd><?= e($contact['ciudad']) ?></dd></div>
          </dl>
          <div class="contact-actions">
            <form action="contacto_favorito.php" method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$contact['id'] ?>">
              <input type="hidden" name="favorito" value="<?= (int)$contact['favorito'] === 1 ? '0' : '1' ?>">
              <button type="submit" class="button button-secondary button-small" aria-label="<?= (int)$contact['favorito'] === 1 ? 'Quitar de favoritos' : 'Agregar a favoritos' ?> a <?= e($contact['nombre']) ?>"><?= (int)$contact['favorito'] === 1 ? '★ Favorito' : '☆ Favorito' ?></button>
            </form>
            <a class="button button-secondary button-small" href="contacto_editar.php?id=<?= (int)$contact['id'] ?>">Editar</a>
            <a class="button button-danger-outline button-small" href="contacto_eliminar.php?id=<?= (int)$contact['id'] ?>">Eliminar</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
