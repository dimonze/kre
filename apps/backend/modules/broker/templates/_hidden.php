<?= $broker->hidden_val ?>
<?php if(null === sfContext::getInstance()->getUser()->getAttribute('suptype')): ?>
  <?= link_to(($broker->hidden == true ? 'Показать' : 'Скрыть'), 'broker/showhide?id='.$broker->id,
    array('class' => 'sf_button_inline ui-priority-secondary sf_button ui-corner-all ui-state-default')) ?>
<?php endif; ?>