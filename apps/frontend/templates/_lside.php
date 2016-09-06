<div class="lside">
  <div class="padding">
    <?= include_component_slot('one') ?>
    <?= include_component_slot('two') ?>    
    <?= include_component_slot('three') ?>
    <?= include_component_slot('four') ?>
    <?= include_component_slot('five') ?>
   
    <?php if (has_slot('SeoText') && ($seotext = get_slot('SeoText')) 
            && sfContext::getInstance()->getRequest()->getParameter('action') != 'homepage'
            && sfContext::getInstance()->getRequest()->getParameter('action') != 'main'): ?>
    <?= $seotext ?>
    <?php endif ?>

  </div>
</div>