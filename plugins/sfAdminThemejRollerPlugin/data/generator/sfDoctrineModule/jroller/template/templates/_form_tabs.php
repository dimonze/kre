<ul>
[?php foreach ($form_fields as $fieldset => $fields): ?]
  <li><a href="#sf_fieldset_[?php echo Tools::slugify($fieldset) ?]">[?php echo __($fieldset, array(), '<?php echo $this->getI18nCatalogue() ?>') ?]</a></li>
[?php endforeach; ?]
</ul>