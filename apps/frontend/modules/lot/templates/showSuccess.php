<div class="cside c_full">
  <div class="padding">
    <?php include_component('menu', 'breadcrumbs') ?>
    <div class="separator"></div>
    <div id="content" class="content_full">
      <?php include_partial('single-item', array(
        'lot'       => $lot,
        'lots_alike'=> $lots_alike,
        '_params'   => $_params,
        'isparent'  => false,
        'mode'      => $lot->has_children ? 'supobject' : (!($lot->pid && $lot->Parent->is_visible) ? 'both' : 'object'),
        'acts'      => true,
      )) ?>
      <div class="clear"></div>

      <?php if ($lot->pid && $lot->Parent->is_visible): ?>
        <script>
          jQuery.get( '<?= url_for1('/lot/hideParent/' . $lot->Parent->id .'/', true) ?>', function( data ) {
            jQuery("#blockajax").html( data );
            jQuery(function(){
              InitializeColorBox();
            });
          });
        </script>
        <div class="separator separator_mrg"></div>
        <div id="blockajax"></div>
      <?php elseif ($lot->has_children && count($lot->Lots)): ?>
        <?php include_component('lot', 'children', array('lot' => $lot)) ?>
        <script>
          jQuery(function(){
            InitializeColorBox();
          })
        </script>
      <?php else: ?>
        <script>
          jQuery(function(){
            InitializeColorBox();
          })
        </script>
      <?php endif ?>

      <?php if ($lot->status != 'inactive' && $lot->has_children): ?>
        <?php include_partial('lots-alike', array('lot' => $lot, 'lots_alike' => $lots_alike, '_params' => $_params)) ?>
      <?php endif ?>
    </div>
  </div>
</div>