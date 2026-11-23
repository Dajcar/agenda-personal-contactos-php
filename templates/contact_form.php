<?php
/** @var array<string,string> $values */
/** @var array<string,string> $errors */
/** @var string $button_label */
/** @var string $action */
?>
<form method="post" action="<?= e($action) ?>" class="form-grid" novalidate>
  <?= csrf_field() ?>
  <div class="form-field">
    <label for="nombre">Nombre completo <span aria-hidden="true">*</span></label>
    <input id="nombre" name="nombre" type="text" value="<?= e($values['nombre']) ?>" maxlength="100" required autocomplete="name" placeholder="Ej. María Torres" aria-describedby="error-nombre">
    <?php if (isset($errors['nombre'])): ?><p id="error-nombre" class="field-error"><?= e($errors['nombre']) ?></p><?php endif; ?>
  </div>
  <div class="form-field">
    <label for="telefono">Teléfono <span aria-hidden="true">*</span></label>
    <input id="telefono" name="telefono" type="tel" value="<?= e($values['telefono']) ?>" maxlength="30" required autocomplete="tel" placeholder="Ej. 0987654321" aria-describedby="error-telefono">
    <?php if (isset($errors['telefono'])): ?><p id="error-telefono" class="field-error"><?= e($errors['telefono']) ?></p><?php endif; ?>
  </div>
  <div class="form-field">
    <label for="correo">Correo electrónico <span aria-hidden="true">*</span></label>
    <input id="correo" name="correo" type="email" value="<?= e($values['correo']) ?>" maxlength="190" required autocomplete="email" placeholder="ejemplo@correo.com" aria-describedby="error-correo">
    <?php if (isset($errors['correo'])): ?><p id="error-correo" class="field-error"><?= e($errors['correo']) ?></p><?php endif; ?>
  </div>
  <div class="form-field">
    <label for="ciudad">Ciudad <span aria-hidden="true">*</span></label>
    <input id="ciudad" name="ciudad" type="text" value="<?= e($values['ciudad']) ?>" maxlength="100" required autocomplete="address-level2" placeholder="Ej. Zamora" aria-describedby="error-ciudad">
    <?php if (isset($errors['ciudad'])): ?><p id="error-ciudad" class="field-error"><?= e($errors['ciudad']) ?></p><?php endif; ?>
  </div>
  <div class="form-field">
    <label for="id_categoria">Categoría <span aria-hidden="true">*</span></label>
    <select id="id_categoria" name="id_categoria" required aria-describedby="error-categoria">
      <?php foreach ($categories as $category): ?>
        <option value="<?= (int) $category['id'] ?>" <?= (string)$values['id_categoria'] === (string)$category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($errors['id_categoria'])): ?><p id="error-categoria" class="field-error"><?= e($errors['id_categoria']) ?></p><?php endif; ?>
  </div>
  <div class="form-field favorite-field">
    <label class="checkbox-label" for="favorito">
      <input id="favorito" name="favorito" type="checkbox" value="1" <?= $values['favorito'] === '1' ? 'checked' : '' ?>>
      <span>★ Marcar como favorito</span>
    </label>
    <?php if (isset($errors['favorito'])): ?><p class="field-error"><?= e($errors['favorito']) ?></p><?php endif; ?>
  </div>
  <p class="required-note">Los campos marcados con * son obligatorios.</p>
  <div class="form-actions">
    <button type="submit" class="button button-primary"><?= e($button_label) ?></button>
    <a class="button button-secondary" href="dashboard.php">Cancelar</a>
  </div>
</form>
