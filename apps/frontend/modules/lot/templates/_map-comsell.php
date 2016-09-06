<?php use_javascript('stdoffers') ?>
<?php
$dists = $sf_params->get('districts');
if ($dists instanceof sfOutputEscaperArrayDecorator) {
  $dists = $dists->getRawValue();
}
?>

<script type="text/javascript">
  window.districts = <?= json_encode(sfConfig::get('app_districts')) ?>;
</script>

<div id="map" style="display: none;">
	<table>
		<tr>
			<td class="mp">
				<div id="map_place">
     			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="421" height="421" id="map_id" align="middle">
      			<param name="allowScriptAccess" value="sameDomain" />
      			<param name="movie" value="/flash/map_new2.swf?nocash=1" />
      			<param name="menu" value="false" />
      			<param name="quality" value="high" />
      			<param name="bgcolor" value="#FFFFFF" />
  					<embed src="/flash/map_new2.swf?nocash=1"	allowScriptAccess="sameDomain" menu="false" quality="high" bgcolor="#FFFFFF" width="421" height="421" align="middle" name="map_id" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
  				</object>
        </div>
			</td>
			<td>
				<table>
					<tr>
            <td style="padding-right: 3px;">
							<label for="district-0"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || !array_diff(array(1,3,31,6,7,8,10,12,13,16,17), $dists) ? 'checked="checked"' : '' ?> id="district-0"/>Центральный АО</label>
							<div class="m_sub">
								<label for="district-1"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(1, $dists) ? 'checked="checked"' : '' ?> id="district-1"/>Арбат</label>
								<label for="district-3"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(3, $dists) ? 'checked="checked"' : '' ?> id="district-3"/>Тверской</label>
                <label for="district-4"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(31, $dists) ? 'checked="checked"' : '' ?> id="district-4" rel="31"/>Красносельский</label>
								<label for="district-6"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(6, $dists) ? 'checked="checked"' : '' ?> id="district-6"/>Таганский</label>
								<label for="district-7"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(7, $dists) ? 'checked="checked"' : '' ?> id="district-7"/>Замоскворечье</label>
								<label for="district-8"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(8, $dists) ? 'checked="checked"' : '' ?> id="district-8"/>Якиманка</label>
								<label for="district-10"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(10, $dists) ? 'checked="checked"' : '' ?> id="district-10"/>Китай город</label>
								<label for="district-12"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(12, $dists) ? 'checked="checked"' : '' ?> id="district-12"/>Хамовники</label>
								<label for="district-13"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(13, $dists) ? 'checked="checked"' : '' ?> id="district-13"/>Пресненский и Москва Сити</label>
								<label for="district-15"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(16, $dists) ? 'checked="checked"' : '' ?> id="district-15" rel="16"/>Мещанский</label>
								<label for="district-16"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(17, $dists) ? 'checked="checked"' : '' ?> id="district-16" rel="17"/>Басманный</label>
							</div>
						</td>
						<th></th>
						<td>
							<label for="district-20"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(20, $dists) ? 'checked="checked"' : '' ?> id="district-20"/>Северо-Западный АО</label>
							<label for="district-21"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(21, $dists) ? 'checked="checked"' : '' ?> id="district-21"/>Северный АО</label>
							<label for="district-22"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(22, $dists) ? 'checked="checked"' : '' ?> id="district-22"/>Северо-Восточный АО</label>
							<label for="district-23"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(23, $dists) ? 'checked="checked"' : '' ?> id="district-23"/>Восточный АО</label>
							<label for="district-24"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(24, $dists) ? 'checked="checked"' : '' ?> id="district-24"/>Юго-Восточный АО</label>
							<label for="district-25"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(25, $dists) ? 'checked="checked"' : '' ?> id="district-25"/>Южный АО</label>
							<label for="district-26"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(26, $dists) ? 'checked="checked"' : '' ?> id="district-26"/>Юго-Западный АО</label>
							<label for="district-28"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(28, $dists) ? 'checked="checked"' : '' ?> id="district-28"/>Западный АО</label>
              <label for="district-32"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || in_array(32, $dists) ? 'checked="checked"' : '' ?> id="district-32"/>Зеленоградский АО</label>
						</td>
					</tr>
					<tr><td colspan="3">&nbsp;</td></tr>
					<tr>
						<td style="vertical-align: middle;">
							<label for="district--1"><input type="checkbox" onclick="districtChange(this)" <?= !$dists || count($dists) > 20 ? 'checked="checked"' : '' ?> id="district--1"/>Выбрать все районы</label>
						</td>
						<th></th>
						<td>
							<div class="select select_btn select_right"><div class="select_r_bg">
								<span class="select_button">Готово</span>
								<input type="button" onclick="mapOk();" id="okbutton" value="Готово"/>
							</div></div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>