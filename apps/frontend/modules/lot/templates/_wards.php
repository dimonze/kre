<?php use_javascript('outoftownoffers') ?>
<?php
$wards = $sf_params->get('wards');
if ($wards instanceof sfOutputEscaperArrayDecorator) {
  $wards = $wards->getRawValue();
}
?>

<script type="text/javascript">
  window.wards = <?= json_encode(sfConfig::get('app_wards')) ?>;
</script>

<div id="map" style="display: none; ">
	<table>
		<tbody><tr>
			<td class="mp">
				<div id="map_place">
     			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash_map" width="421" align="middle" height="421">
      			<param name="allowScriptAccess" value="sameDomain">
      			<param name="movie" value="/flash/mapw.swf?nocash=1">
      			<param name="menu" value="false">
      			<param name="quality" value="high">
      			<param name="bgcolor" value="#FFFFFF">
  					<embed src="/flash/mapw.swf?nocash=1" allowscriptaccess="sameDomain" menu="false" quality="high" bgcolor="#FFFFFF" name="flash_map" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width="421" align="middle" height="421">
  				</object>
    		</div>
			</td>
			<td>
				<table>
					<tbody><tr>
						<td style="padding-right: 3px;">
							<!--<div class="m_sub">-->
       					<label for="district-1"><input id="district-1" <?= !$wards || in_array(1, $wards) ? 'checked="checked"' : '' ?> name="altufevskoe" onclick="districtChange(this)" type="checkbox">Алтуфьевское</label>
								<label for="district-2"><input id="district-2" <?= !$wards || in_array(2, $wards) ? 'checked="checked"' : '' ?> name="borovskoe" onclick="districtChange(this)" type="checkbox">Боровское</label>
								<label for="district-3"><input id="district-3" <?= !$wards || in_array(3, $wards) ? 'checked="checked"' : '' ?> name="varshavskoe" onclick="districtChange(this)" type="checkbox">Варшавское</label>
								<label for="district-4"><input id="district-4" <?= !$wards || in_array(4, $wards) ? 'checked="checked"' : '' ?> name="volokamskoe" onclick="districtChange(this)" type="checkbox">Волоколамское</label>
								<label for="district-5"><input id="district-5" <?= !$wards || in_array(5, $wards) ? 'checked="checked"' : '' ?> name="gorkovskoe" onclick="districtChange(this)" type="checkbox">Горьковское</label>
								<label for="district-6"><input id="district-6" <?= !$wards || in_array(6, $wards) ? 'checked="checked"' : '' ?> name="dmitrovskoe" onclick="districtChange(this)" type="checkbox">Дмитровское</label>
								<label style="display:none;"><input id="district-7" <?= !$wards || in_array(7, $wards) ? 'checked="checked"' : '' ?> name="egorievskaya" onclick="districtChange(this)" type="checkbox">Егорьевское</label>
								<label for="district-8"><input id="district-8" <?= !$wards || in_array(8, $wards) ? 'checked="checked"' : '' ?> name="ilinskoe" onclick="districtChange(this)" type="checkbox">Ильинское</label>
								<label for="district-9"><input id="district-9" <?= !$wards || in_array(9, $wards) ? 'checked="checked"' : '' ?> name="kalujskoe" onclick="districtChange(this)" type="checkbox">Калужское</label>
								<label for="district-10"><input id="district-10" <?= !$wards || in_array(10, $wards) ? 'checked="checked"' : '' ?> name="kashirskoe" onclick="districtChange(this)" type="checkbox">Каширское</label>
								<label for="district-11"><input id="district-11" <?= !$wards || in_array(11, $wards) ? 'checked="checked"' : '' ?> name="kievskoe" onclick="districtChange(this)" type="checkbox">Киевское</label>
								<label for="district-12"><input id="district-12" <?= !$wards || in_array(12, $wards) ? 'checked="checked"' : '' ?> name="kurkinskoe" onclick="districtChange(this)" type="checkbox">Куркинское</label>
								<label for="district-13"><input id="district-13" <?= !$wards || in_array(13, $wards) ? 'checked="checked"' : '' ?> name="leningradskoe" onclick="districtChange(this)" type="checkbox">Ленинградское</label>
							<label for="district-14"><input id="district-14" <?= !$wards || in_array(14, $wards) ? 'checked="checked"' : '' ?> name="minskoe" onclick="districtChange(this)" type="checkbox">Минское</label>
								<!--</div>-->
						</td>
						<th></th>
						<td>
      					<label for="district-15"><input id="district-15" <?= !$wards || in_array(15, $wards) ? 'checked="checked"' : '' ?> name="mojaiskoe" onclick="districtChange(this)" type="checkbox">Можайское</label>
								<label for="district-16"><input id="district-16" <?= !$wards || in_array(16, $wards) ? 'checked="checked"' : '' ?> name="novorizhskoe" onclick="districtChange(this)" type="checkbox">Новорижское</label>
								<label for="district-17"><input id="district-17" <?= !$wards || in_array(17, $wards) ? 'checked="checked"' : '' ?> name="novoryazanskoe" onclick="districtChange(this)" type="checkbox">Новорязанское</label>
								<label style="display:none;"><input id="district-18" <?= !$wards || in_array(18, $wards) ? 'checked="checked"' : '' ?> name="nosovihinskoe" onclick="districtChange(this)" type="checkbox">Носовихинское</label>
								<label for="district-19"><input id="district-19" <?= !$wards || in_array(19, $wards) ? 'checked="checked"' : '' ?> name="ostavshkovskoe" onclick="districtChange(this)" type="checkbox">Осташковское</label>
								<label for="district-20"><input id="district-20" <?= !$wards || in_array(20, $wards) ? 'checked="checked"' : '' ?> name="pyatnickoe" onclick="districtChange(this)" type="checkbox">Пятницкое</label>
								<label for="district-21"><input id="district-21" <?= !$wards || in_array(21, $wards) ? 'checked="checked"' : '' ?> name="rogachevskoe" onclick="districtChange(this)" type="checkbox">Рогачевское</label>
								<label for="district-22"><input id="district-22" <?= !$wards || in_array(22, $wards) ? 'checked="checked"' : '' ?> name="rublevo" onclick="districtChange(this)" type="checkbox">Рублево-Успенское</label>
								<label for="district-23"><input id="district-23" <?= !$wards || in_array(23, $wards) ? 'checked="checked"' : '' ?> name="ryazanaskoe" onclick="districtChange(this)" type="checkbox">Рязанское</label>
								<label for="district-24"><input id="district-24" <?= !$wards || in_array(24, $wards) ? 'checked="checked"' : '' ?> name="simferopolskoe" onclick="districtChange(this)" type="checkbox">Симферопольское</label>
								<label for="district-25"><input id="district-25" <?= !$wards || in_array(25, $wards) ? 'checked="checked"' : '' ?> name="skolkovskoe" onclick="districtChange(this)" type="checkbox">Сколковское</label>
								<label for="district-26"><input id="district-26" <?= !$wards || in_array(26, $wards) ? 'checked="checked"' : '' ?> name="shelkovskoe" onclick="districtChange(this)" type="checkbox">Щелковское</label>
								<label for="district-27"><input id="district-27" <?= !$wards || in_array(27, $wards) ? 'checked="checked"' : '' ?> name="yaroslavskoe" onclick="districtChange(this)" type="checkbox">Ярославское</label>
						</td>
					</tr>
					<tr><td colspan="3">&nbsp;</td></tr>
					<tr>
						<td style="vertical-align: middle;">
							<label for="district--1"><input type="checkbox" onclick="districtChange(this)" <?= !$wards || count($wards) > 24 ? 'checked="checked"' : '' ?> id="district--1">Выбрать все направления</label>
						</td>
						<th></th>
						<td>
							<div class="select select_btn select_right"><div class="select_r_bg">
								<span class="select_button">Готово</span>
								<input type="button" onclick="mapOk();" id="okbutton" value="Готово">
							</div></div>
						</td>
					</tr>
				</tbody></table>
			</td>
		</tr>
	</tbody></table>
</div>