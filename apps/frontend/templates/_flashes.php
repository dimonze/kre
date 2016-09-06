<?php use_helper('I18N') ?>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="ui-widget">
    <div class="notice ui-state-highlight ui-corner-all"><?=  __($sf_user->getFlash('notice'), array(), 'sf_admin') ?></div>
  </div>
<?php endif ?>

<?php if ($sf_user->hasFlash('error')): ?>
  <div class="ui-widget">
    <div class="error ui-state-error ui-corner-all" style="background: #f33; color: white; padding: 4px; font-weight: bold"><?= __($sf_user->getFlash('error'), array(), 'sf_admin') ?></div>
  </div>
<?php endif ?>
