<div class="upd-lot-tags">
  <?php if ($lot->status == 'hidden'): ?>
	<div class="upd-lot-tag-hid"></div>
  <?php endif ?>	
  <?php if ($lot->is_new_object): ?>
    <div class="upd-lot-tag-new"></div>
  <?php endif ?>
  <?php if ($lot->is_new_price): ?>
    <div class="upd-lot-tag-pricenew"></div>
  <?php endif ?>
</div>