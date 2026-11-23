<?php $flash = flash_take(); ?>
<?php if ($flash): ?>
  <div role="status" class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
    <?= e($flash['message']) ?>
  </div>
<?php endif; ?>
