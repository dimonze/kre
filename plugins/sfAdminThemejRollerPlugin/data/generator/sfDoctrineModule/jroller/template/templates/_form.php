[?php include_stylesheets_for_form($form) ?]
[?php include_javascripts_for_form($form) ?]

<div class="sf_admin_form">
  [?php echo form_tag_for($form, '@<?php echo $this->params['route_prefix'] ?>') ?]

	<div class="sf_admin_actions_block ui-widget">
		[?php include_partial('<?php echo $this->getModuleName() ?>/form_actions', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?]
	</div>
	
    [?php echo $form->renderHiddenFields() ?]

    [?php if ($form->hasGlobalErrors()): ?]
      [?php echo $form->renderGlobalErrors() ?]
    [?php endif; ?]

		
   	[?php 
		$count = 0;
		foreach ($configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') as $fieldset => $fields): 
			$count++;
    endforeach; 
		?]


		<div id="sf_admin_form_tab_menu">
			[?php if ($count > 1): ?]
        [?php include_partial('<?php echo $this->getModuleName() ?>/form_tabs', array('form_fields' => $configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit'))) ?]
			[?php endif ?]
			
	    [?php foreach ($configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') as $fieldset => $fields): ?]
	      [?php include_partial('<?php echo $this->getModuleName() ?>/form_fieldset', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'form' => $form, 'fields' => $fields, 'fieldset' => $fieldset)) ?]
	    [?php endforeach; ?]
		</div>
  </form>
</div>
