<?php
  if(is_null($index = sfContext::getInstance()->getRequest()->getParameter('id'))){
    $index = sfContext::getInstance()->getRequest()->getParameter('action');
  }
?>
<ul class="backend_nav" style="padding:5px 10px;">
    <li class="<?= in_array($index, array('bad', 'index')) ? 'current' : '' ?>"><a href="<?= url_for('@stat?module=stat&action=index') ?>">Общяя информация</a></li>
    <li class="<?= $index == 'cian' ? 'current' : '' ?>"><a href="<?= url_for('@statId?module=stat&action=lot&id=cian') ?>">Циан</a></li>      
    <li class="<?= $index == 'tba' ? 'current' : '' ?>"><a href="<?= url_for('@statId?module=stat&action=lot&id=tba') ?>">ТБА</a></li>  
  </ul>

