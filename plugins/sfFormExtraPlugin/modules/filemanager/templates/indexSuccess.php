<?php if ($sf_request->hasParameter('insert')): ?>
  <?php use_javascript('/sfFormExtraPlugin/js/tiny_mce/tiny_mce_popup.js') ?>
<?php endif ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.js') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.selectable.js') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.impromptu.js') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.form.js') ?>
<?php use_javascript('/sfFormExtraPlugin/js/filemanager.js') ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.impromptu.css') ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/filemanager.css') ?>

<div class="panel" id="toolbar">
  <a href="#" title="Назад" class="bt" action="back"><img src="/sfFormExtraPlugin/images/bt/back.png" /></a>
  <a href="#" title="Вперед" class="bt" action="forward"><img src="/sfFormExtraPlugin/images/bt/forward.png" /></a>
  <a href="#" title="На уровень выше" class="bt" action="up"><img src="/sfFormExtraPlugin/images/bt/up.png" /></a>
  <a href="#" title="Перезагрузить" class="bt" action="reload"><img src="/sfFormExtraPlugin/images/bt/reload.png" /></a>
  <div class="separator"></div>
  <a href="#" title="Загрузить файл" class="bt" action="upload"><img src="/sfFormExtraPlugin/images/bt/upload.png" /></a>
  <a href="#" title="Создать папку" class="bt" action="create-folder"><img src="/sfFormExtraPlugin/images/bt/folder_new.png" /></a>
  <a href="#" title="Копировать" class="bt" action="copy"><img src="/sfFormExtraPlugin/images/bt/edit_copy.png" /></a>
  <a href="#" title="Переместить" class="bt" action="move"><img src="/sfFormExtraPlugin/images/bt/edit_move.png" /></a>
  <a href="#" title="Вставить" class="bt" action="paste"><img src="/sfFormExtraPlugin/images/bt/edit_move.png" /></a>
  <a href="#" title="Удалить" class="bt" action="delete"><img src="/sfFormExtraPlugin/images/bt/edit_remove.png" /></a>
  <div class="separator"></div>
  <a href="#" title="Найти" class="bt" action="search"><img src="/sfFormExtraPlugin/images/bt/find.png" /></a>
  <div class="indicator"></div>
</div>

<div id="path" class="panel"><ul></ul></div>

<div class="panel upload" action="upload">
  <form action="#" method="post" enctype="multipart/form-data">
    Загрузить файл:
    <input type="hidden" name="path" value="" />
    <input type="file" name="file" />
    <input type="submit" value="Ок" />
  </form>
</div>

<div class="panel" id="browser-wrapper">
  <div class="icons-64" id="browser"></div>
</div>

<div class="panel" id="status"></div>