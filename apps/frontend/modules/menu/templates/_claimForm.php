<h2 class="title_h2 h2_i_want">Я хочу</h2>
<div class="separator"></div>
<form action="<?= url_for('@claim')?>" method="get" name="iwant" class="form" style="margin-right: 15px;">
	<fieldset>
		<div class="upd-iwant-select select_place sb">
			<div class="upd-iwant-select-rbg">	
				 <input type="hidden" id="claim_type" name="types[]" value="1" />
				 <div class="upd-iwant-select-control"><table><tr><td>Купить квартиру</td></tr></table></div>
				 <ul id="sel_id1" class="upd-iwant-select-list upd-select-list" style="display: none;">
					<?php foreach (Claim::$_types as $id => $type): ?>
					  <li id="sel_id1_<?= $id ?>" rel="<?= $id ?>">
						<table><tr><td><?= $type ?></td></tr></table>
					  </li>
					<?php endforeach ?>
				 </ul>
			 </div>
		</div>
		<div class="select_place">
			<div class="select select_btn select_left">
				<div class="select_r_bg">
				  <span class="select_button" style="color: #fff;"  onclick="yaCounter19895512.reachGoal('ADD'); return true;">Оставить заявку</span>
				</div>
			</div>
		</div>
	</fieldset>
</form>