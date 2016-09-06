var districtIds = new Array();
var prevParams = '';
var perPage = 50;
var selectedOffers;
var slink = false;
function getCookie(name){
    var start = document.cookie.indexOf(name + '=');
    var len = start + name.length + 1;
    if ((!start) && (name != document.cookie.substring(0, name.length)))
        return null;
    if (start == -1)
        return null;
    var end = document.cookie.indexOf(';', len);
    if (end == -1)
        end = document.cookie.length;
    if (end == start) {
        return '';
    }
    return unescape(document.cookie.substring(len, end));
}

function setCookie(name, value){
    // set time, it's in milliseconds
    var expires_date = new Date();
    expires_date = new Date(expires_date.getFullYear(), expires_date.getMonth(), expires_date.getDate() + 7);
    var path = '/';
    document.cookie = name + "=" + escape(value) +
    ((expires_date) ? ";expires=" + expires_date.toGMTString() : "") +
    ((path) ? ";path=" + path : "");
}

function showSearch(){
    $('searchForm').style.display = '';
    //$('searchphrase').innerHTML='.';
    $('searchphrase').style.display = 'none';
}

function clearForm(){

    $('district--1').checked = true;
    districtChange($('district--1'), true);
    mapOk();

    $('spacefrom').value = '';
    $('spaceto').value = '';
    $('roomsfrom').value = '';
    $('roomsto').value = '';
    $('pricefrom').value = '';
    $('priceto').value = '';
    $('lot').value = '';
    $('estate').value = '';
    $('street').value = '';
    if (type=='eliteflat'){
		$('decoration').checked = false;
		$('nodecoration').checked = false;
	}
    //setPerPage(10);
    search();
}

function showList(){
    //	$('searchForm').style.display='none';
    //	$('searchphrase').innerHTML=' или воспользоваться <a href="#"  onclick="showSearch(); return false;">поиском по параметрам</a>';
    window.location.reload();
}

function setPerPage(cnt){
    perPage = cnt;
    var ret = '';
    (new Array(10, 20, 50)).each(function(i){
        if (i == cnt) {
            ret += '<strong>' + i + '</strong> / ';
            setCookie('perPage', i);
        }
        else {
            ret += '<a href="#" onclick="return setPerPage(' + i + ')" >' + i + '</a> / ';
        }
    });
    $('perPages').innerHTML = ret.substring(0, ret.length - 3);
   $('progress').style.display = '';

    var myAjax = new Ajax.Request('/ajax/' + type, {
        method: 'post',
        parameters: prevParams + '&pg=0&perPage=' + perPage,
        onComplete: searchReturn
    });
    return false;
}

function setDistrict(id, checked){
    $('district-' + id).checked = checked;
    districtChange($('district-' + id), true);
}

function thisMovie(movieName){
    if (navigator.appName.indexOf("Microsoft") != -1) {
        return window[movieName]
    }
    else {
        return document[movieName]
    }
}

function setFlashDistrict(id, checked){
    if (thisMovie('map_id').setDistrict) {
        thisMovie('map_id').setDistrict(id, checked);
    }
}

function districtChange(el, updateFlash){
    var id = parseInt(el.id.replace("district-", ""));

    var father   = jQuery('#district-' + id);
    var children = jQuery(".m_sub[rel='district-" + id + "']");

    if(children && father) {
      children.find('input[type="checkbox"]').attr('checked', !!father.attr('checked'));
    }

    if (!updateFlash && id != -1)
        setFlashDistrict(id, el.checked);

    if (id == -1) {
        for (var i = 0; i <= 32; i++) {
          if($('district-' + i)) {
            $('district-' + i).checked = el.checked;
          }
          if (!updateFlash)
              setFlashDistrict(i, el.checked);
        }
    }
    else
        if (id == 0) {
          jQuery('.m_sub').eq(0).find('input').attr('checked', el.checked);
          if (!updateFlash) setFlashDistrict(i, el.checked);
        
        }

    var mainCh = true;
    var allCh = true;
    var ch = 0;
    for (var i = 1; i <= 32; i++) {
        if ($('district-' + i) && !$('district-' + i).checked) {
            if (i <= 18) {
                mainCh = false;
            }
            allCh = false;
        }
        else {
            ch++;
        }
    }
    //alert(id);
    $('district-0').checked = mainCh;
    setFlashDistrict(0, mainCh);
    $('district--1').checked = allCh;
    $('okbutton').disabled = (ch == 0);
}

function Init(){
    if (type == 'eliteflat') {
        //$('decorationtd').style.display = '';
    }
    else
        if (type == 'elitenew') {
            $('pricelabel').innerHTML = 'Цена за м&sup2;';
        }
        else
            if (type == 'flatrent') {
                $('inputspace').style.display = 'none';
                $('inputrooms').style.display = '';
            }
   // $('bottomButtons').style.display = '';
   // $('lamptable').style.display = '';
    var pp = parseInt(getCookie("perPage"));
    if (pp > 0) {
        var ret = '';
        perPage = pp;
        (new Array(10, 20, 50)).each(function(i){
            if (i == pp) {
                ret += i + ' / ';
            }
            else {
                ret += '<a href="#" onclick="return setPerPage(' + i + ')" >' + i + '</a> / ';
            }
        });
        $('perPages').innerHTML = ret.substring(0, ret.length - 3);
    }
    try {
        selectedOffers = getCookie('selectedOffers' + type).split('-').compact();
        if (!(selectedOffers[0] > 0)) {
            delete selectedOffers[0];
            selectedOffers = selectedOffers.compact();
        }
    }
    catch (e) {
        selectedOffers = new Array();
    }
    updateOfferLink();

    if (!(selectedOffers[0] > 0)) {
		if (location.hash == '#search' || type == 3) {
			showSearch();
		}
		search();
	} else  {
		if (slink) viewSelectedOffers();
		else {
			if (location.hash == '#search' || type == 3) {
				showSearch();
			}
			search();
		}
	}
}

function mapCancel(){
    $('map').style.display = 'none';
  //  $('currency').style.visibility = 'visible';
}

function mapOk(){
    var ret = new Array;
    districtIds = new Array;
    var start = 0;
    var inputs = [];
    if ($('district--1').checked) {
        ret.push("Все районы");
    }
    else {
        if ($('district-0').checked) {
            ret.push("Центральный АО");
            districtIds.push(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18);
            start = 19;
        }
        else {
            start = 1;
        }
        for (var i = start; i <= 32; i++) {
            if ($('district-' + i) && $('district-' + i).checked) {
                var key = $('district-' + i).getAttribute('rel') || i;
                ret.push(districts[key]);
                districtIds.push(key);
            }
        }


        for (var i in districtIds) {
          if (districtIds.hasOwnProperty(i)) {
            inputs.push('<input type="hidden" name="districts[]" value="' + districtIds[i] + '" />');
          }
        }
    }
    $('districtS').innerHTML = ret.join(", ") + inputs.join("");

    mapCancel();

}

function showMap(){
  //  $('currency').style.visibility = 'hidden';
    $('map').style.display = 'block';
}

function openDistrict(id){
    var left = Math.round((screen.availWidth - 850) / 2);
    var top = Math.round((screen.availHeight - 650) / 2);
    window.open('/analytics/12/#' + id, "district", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=" + 850 + ", height=" + 650 + ", left=" + left + ",top=" + top);
}


function search(){
    $('progress').style.display = '';

    if (type != 'elitenew') {
        prevParams = 'priceallfrom=' + $F('pricefrom') + '&priceallto=' + $F('priceto');
    }
    else {
        prevParams = 'pricefrom=' + $F('pricefrom') + '&priceto=' + $F('priceto');
    }

    if (type == 'eliteflat') {
		if ($('decoration').checked && $('nodecoration').checked) {
			prevParams += '';
			// alert('both')
		} else {
			if ($('decoration').checked) prevParams += '&decoration=1';
			if ($('nodecoration').checked) prevParams += '&nodecoration=1';
		}
    }

	if ($('withoutprice').checked) {
        prevParams += '&withoutprice=1';
    }

	if ($('nprice').checked) {
        prevParams += '&nprice=1';
    }
	if ($('nobj').checked) {
        prevParams += '&nobj=1';
    }

    prevParams += '&spacefrom=' + $F('spacefrom') + '&spaceto=' + $F('spaceto') + '&roomsfrom=' + $F('roomsfrom') + '&roomsto=' + $F('roomsto') + '&districts=' + districtIds.join(',') + '&street=' + $F('street') + '&estate=' + $F('estate') + '&lot=' + $F('lot') + '&currency=' + $F('sel_id1_v') +'&sortP=' + $('sPrice').value;

	if (type == 'flatrent') {
		prevParams += '&sortR=' + $('sRooms').value;
	} else {
		prevParams += '&sortS=' + $('sSpace').value;
	}

	//if ($('nodecoration').checked) alert(prevParams);
	//if ($('decoration').checked) alert(prevParams);

    var myAjax = new Ajax.Request('/ajax/' + type, {
        method: 'post',
        parameters: prevParams + '&pg=0&perPage=' + perPage,
        onComplete: searchReturn
    });
}

function gotopage(pg){
    $('progress').style.display = '';
    var myAjax = new Ajax.Request('/ajax/' + type, {
        method: 'post',
        parameters: prevParams + '&pg=' + pg + '&perPage=' + perPage,
        onComplete: searchReturn
    });
    scroll(0, 150);
    return false;
}

function format(s){
    //s=Math.round(s*100)/100;
    s = Math.round(s);
    s = new String(s);
    s = s.replace(/(?=([0-9]{3})+$)/g, " ");
    return s;
}

function selectOffer(id, el){
    var i = selectedOffers.indexOf(id);

    if (el.checked) {
        if (i == -1) {
            selectedOffers.push(id);
        }
    }
    else {
        delete selectedOffers[i];
        selectedOffers = selectedOffers.compact();
    }

    updateOfferLink();
    setCookie('selectedOffers' + type, selectedOffers.join('-'));
}

function clearSelected(){
    setCookie('selectedOffers'+type, '');
    window.location.reload();
}

function updateOfferLink(){
    $('selectedOffersCount').innerHTML = selectedOffers.length;
}

function viewSelectedOffers(){
    var cnt = selectedOffers.length;
    var ids = selectedOffers.join('-');
    if (cnt == 0) {
        alert('Не выбрано ни одного объекта');
        return;
    }
    $('searchForm').style.display = 'none';
    $('searchphrase').style.display = '';
    $('progress').style.display = '';

    var myAjax = new Ajax.Request('/ajax/' + type, {
        method: 'post',
        parameters: '&ids=' + ids+'&perPage=' + cnt,
        onComplete: searchReturn
    });
}

function offprint(){
    var left = Math.round((screen.availWidth - 850) / 2);
    var top = Math.round((screen.availHeight - 650) / 2);
    window.open('/offersprint/', "offersprint", "toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, scrollbars=yes, resizable=yes");
}

function getHeader(){
    return 'test';
}

function isSelectedOffer(id){
    return selectedOffers.indexOf(id) >= 0;
}

function numWord(n,p) {
	n = n*1;
	if (typeof n == 'number') {
		if (!p || p.length < 1)
			p = new Array("");
		n = n.toString();
		if (n.indexOf(".") > 0 || n.indexOf(",") > 0)
			return p[1];
		var r1=0, r2=0;
		var l = n.length;
		r1 = new Number((l < 2)? 0 : n.substr(l-2,1));
		r2 = new Number(n.substr(l-1,1));
		if (r1 != 1) {
			if (r2 > 1 && r2 < 5) {
				return p[1];
			} else if (r2 > 4 && r2 <= 9 || r2 == 0) {
				return p[2];
			} else if (r2 == 1) {
				return p[0];
			}
		} else {
			return p[2];
		}
	} else {
		return "";
	}
}

function printcount(num){
	var words = new Array("предложение","предложения","предложений");
	retval = '<strong>Найдено <b style="color:#9D1C20;">' + num + '</b> ' + numWord(num,words)+'.</strong>';
	return retval;
}

function searchReturn(res){
    eval("var data=" + res.responseText);
    var html = '';
  //s  $('find').innerHTML = data.total;
    data.rows.each(function(k){
        var param = '';
		var paramv = '';
		var priceb = new Array();
		var pricebe = new Array();
		for (var i=1; i<=3; i++) {
			priceb[i] = (k.currency==i)?'<strong>':'';
			pricebe[i] = (k.currency==i)?'</strong>':'';
			}
        if (k.spacefrom > 0) {
            param += "<td>Площад" + (k.spacefrom != k.spaceto ? 'и' : 'ь') + " (м&sup2;)</td>";
			var spaces=k.space.split("-");
			if (spaces.length==2) {
				paramv+="<td><strong>от "+(spaces[0])+" до "+(spaces[1])+"</strong></td>";
			}
			else{
				paramv += "<td><strong>"+k.space.replace(/ /g, 'м&sup2;, ')+"</strong></td>";
			}
            //param += "</h3></td>";
        }

        if (k.roomsfrom > 0) {
            param += "<td>Комнат</td>";
            if (k.roomsto > 0 && k.roomsto != k.roomsfrom) {
                paramv += "<td><strong>"+"от " + k.roomsfrom + " до " + k.roomsto+"</strong></td>";
            }
            else {
                paramv += "<td><strong>"+k.roomsfrom+"</strong></td>";
            }
            //param += "</h3></td>";
        }
        if (k.pricefrom > 0){
            param += "<td>Цена за м&sup2;</td>";
            if (k.priceto > 0 && k.priceto != k.pricefrom) {
                var rvalfrom = k.pricefrom * data.currency[k.currency].value;
				var rvalto = k.priceto * data.currency[k.currency].value;
				paramv +="<td><nobr>"+ priceb[3]+'от ' + format(rvalfrom) + data.currency[3].name + ' до ' + format(rvalto) + data.currency[3].name+pricebe[3]+'</nobr>';
				paramv += '<br><nobr>' + priceb[1]+'от ' + format(rvalfrom/data.currency[1].value) + data.currency[1].name + ' до ' + format(rvalto/data.currency[1].value) + data.currency[1].name+pricebe[1]+'</nobr>';
				paramv += '<br><nobr>' + priceb[2]+'от ' + format(rvalfrom/data.currency[2].value) + data.currency[2].name + ' до ' + format(rvalto/data.currency[2].value) + data.currency[2].name+pricebe[2]+'</nobr></td>';
            }
            else {
				var rvalfrom = k.pricefrom * data.currency[k.currency].value;
                paramv += "<td><nobr>"+priceb[3]+format(rvalfrom) + data.currency[3].name+pricebe[3]+'</nobr>';
                paramv += '<br><nobr>' + priceb[1]+format(rvalfrom/data.currency[1].value) + data.currency[1].name+pricebe[1]+'</nobr>';
                paramv += '<br><nobr>' + priceb[2]+format(rvalfrom/data.currency[2].value) + data.currency[2].name+pricebe[2]+'</nobr></td>';
            }
            //param += "</h3></td>";
        }

        var calcLink = '';
        if (type != 'flatrent') {
            calcLink = '<a href="#"  onclick="openCalc(' + k.priceallfrom + '); return false;">Купить с помощью ипотеки</a>';
        }
        if (k.priceallfrom > 0) {
            if (type == 'eliteflat') {
                param += "<td>Цена квартиры</td>";
            }
            else
                if (type == 'flatrent') {
                    param += "<td>Цена за месяц</td>";
                }
                else {
                    param += "<td>Цена</td>";
                }
            if (k.priceallto > 0 && k.priceallto != k.priceallfrom) {
				var rvalfrom = k.priceallfrom * data.currency[k.currency].value;
				var rvalto = k.priceallto * data.currency[k.currency].value;
				paramv += '<td><nobr>'+priceb[3]+'от ' + format(rvalfrom) + data.currency[3].name + ' до ' + format(rvalto) + data.currency[3].name+pricebe[3]+'</nobr>';
				paramv += '<br><nobr>' + priceb[1]+'от ' + format(rvalfrom/data.currency[1].value) + data.currency[1].name + ' до ' + format(rvalto/data.currency[1].value) + data.currency[1].name+pricebe[1]+'</nobr>';
				paramv += '<br><nobr>' + priceb[2]+'от ' + format(rvalfrom/data.currency[2].value) + data.currency[2].name + ' до ' + format(rvalto/data.currency[2].value) + data.currency[2].name+pricebe[2]+'</nobr></td>';
            }
            else {
                var rvalfrom = k.priceallfrom * data.currency[k.currency].value;
                paramv += '<td><nobr>'+priceb[3]+format(rvalfrom) + data.currency[3].name+pricebe[3]+'</nobr>';
                paramv += '<br><nobr>' + priceb[1]+format(rvalfrom/data.currency[1].value) + data.currency[1].name+pricebe[1]+'</nobr>';
                paramv += '<br><nobr>' + priceb[2]+format(rvalfrom/data.currency[2].value) + data.currency[2].name+pricebe[2]+'</nobr></td>';
            }
           // param += "</h3></td>";
        }

		icons = '';
		 if (((k.odays != null)&&(k.odays<=15))||((k.pdays != null)&&(k.pdays<=15))) {
			icons +='<span class="label_new">';
		 }
		 if (icons!='') {
			if ((k.odays != null)&&(k.odays<=15)) icons += '<img title="Новый объект" alt="" src="/pics/new_object.jpg?anticache='+(Math.floor(Math.random()*(9999-1000+1))+1000)+'" />';
			if ((k.pdays != null)&&(k.pdays<=15)) icons += '<img title="У объекта изменилась цена" alt="" src="/pics/new_price.jpg?anticache='+(Math.floor(Math.random()*(9999-1000+1))+1000)+'" />';
			icons +='</span>';
		 }

		ph = "";
		if ((k.phone > 0) && (k.phone2 > 0)) {
            ph = "<p class='cat_phone'><span class='cp1'>" + phones[k.phone2]+"</span><span class='cp2'>" + phones[k.phone] + "</span></p>";
        }
        else if (k.phone2 > 0) {
            ph = "<p class='cat_phone'><span class='cp1'>" + phones[k.phone2]+"</span></p>";
        }
        else if (k.phone > 0) {
            ph = "<p class='cat_phone'><span class='cp2'>" + phones[k.phone]+"</span></p>";
        }

		var main_alt = (k.main_alt==null)?k.header:k.main_alt;
		main_alt = main_alt.replace(/"/ig,'&quot;');
		 html += '<div class="cat_info">';
         html += '<div class="l_col_s">';
		 html += '<a target="_blank" href="/offers/'+type+'/details/' + k.id + '"><img src="/files/photo_'+type+'_'+ k.id + '_main' + '.jpg?anticache='+(Math.floor(Math.random()*(9999-1000+1))+1000)+'" alt="' + main_alt + '"></a>';
		 html += ph + '</div>';
		 html += '<div class="r_col_s">';
		 html += '<label class="cat_summ" for="id' + k.lot + '"><input type="checkbox" id="id' + k.lot + '" onclick="selectOffer(' + k.id + ', this)" ' + (isSelectedOffer(k.id) ? 'checked' : '') + ' title="Выбрать для просмотра отдельно" />Лот: ' + k.lot + '</label>';

		 html += icons;

		 html += '<h3><a target="_blank" href="/offers/'+type+'/details/' + k.id + '">';

         if (k.objecttype > 0) {
            html += objecttypes[k.objecttype] + ', ';
         }
		 html += k.header + '</a></h3>';
		 html += '<p style="margin:25px 100px 20px 0;">';
         if (k.district > 0) {
            html += 'Район: <a href="/analytics/12/#' + k.district + '">' + districts[k.district] + '</a>';
         }
         if (k.metro != -1) {
            html += '<span class="cat_metro">' + metro[k.metro] + '</span>';
         }
         html += '</p>';

		if (k.ward > 0) {
            html += '<p class="cat_phone">Направление: <span>' + wards[k.ward] + '</span></p>';
        }
        if (k.distance_mkad > 0) {
            html += '<p class="cat_phone">Удаленность от МКАД (км): <span>' + k.distance_mkad + ' км</span></p>';
        }
         html += '<table class="table-content in_cat"><tr>' + param + '</tr><tr>' + paramv + '</tr></table>';

		 html+= k.lead + '<p class="ipo">' + calcLink + '<a target="_blank" href="/offers/'+type+'/details/' + k.id + '" class="ipo_r">Подробнее</a></p>';
		 html += '</div><div class="clear"></div></div>'+ '<hr/>';
    });
    if (data.total == 0) {
        $('results').innerHTML = '<b>По заданным параметрам поиска объектов не найдено. Вы можете снова <a href="#"  onclick="showList(); return false;">просмотреть объекты списком</a>, задать другие параметры поиска или <a href="/claim/" >оставить заявку</a>.</b>';
		$('sort').style.display = 'none';
    }
    else {
        $('results').innerHTML = 'Постоянная <a href="'+data.slink+'">ссылка на эту страницу</a><hr/>' + html;
		$('sort').style.display = '';
    }
    var pages = data.total / data.perpage;
    if (parseInt(pages) != pages)
        pages = parseInt(pages) + 1;
    else
        pages = parseInt(pages);
    var ret = '';
	if (pages > 1) {
		ret += '<div class="paging">';
        for (i = 0; i < pages; i++) {
            var tmp;
            if ((i + 1) * data.perpage > data.total) {
                tmp = i * data.perpage + 1 + '-' + data.total;
            }
            else {
                tmp = i * data.perpage + 1 + '-' + (i + 1) * data.perpage;
            }
            if (data.curpage == i) {
                tmp = '<span>' + tmp + '</span>';
            }
            else {
                tmp = '<a href="#" onclick="return gotopage(' + (i) + ');">' + tmp + '</a>';
            }
            ret += tmp;
        }
		ret += '<div class="clear"></div></div><hr/>';
    }
//	alert(data.slink);
    $('pages').innerHTML = ret;
    $('pages2').innerHTML = ret;
    $('datacount').innerHTML = printcount(data.total);
    $('progress').style.display = 'none';
}
function resetSpace() {
	$('sSpace').value = 0;
	$('aSpace').className = '';
	//$('aSpace').removeClassName('sort_down');
}
function resetPrice() {
	$('sPrice').value = 0;
	$('aPrice').className = '';
//	$('aPrice').removeClassName('sort_down');
}
function resetRooms() {
	$('sRooms').value = 0;
	$('aRooms').className = '';
	//$('aRooms').removeClassName('sort_down');
}
function ordPrice() {
	if ($('sSpace')) resetSpace();
	if ($('sRooms')) resetRooms();
	var pr = $('sPrice').value;
	if (pr == 0) {
		$('sPrice').value = 1;
	//	$('aPrice').removeClassName('sort_down');
		$('aPrice').className = 'sort_up';;
	} else 	if (pr == 1) {
		$('sPrice').value = 2;
	//	$('aPrice').removeClassName('sort_up');
		$('aPrice').className = 'sort_down';;
	} else if (pr == 2) {
		$('sPrice').value = 1;
	//	$('aPrice').removeClassName('sort_down');
		$('aPrice').className = 'sort_up';;
	}
	search();
}
function ordSpace() {
	resetPrice();
	var pr = $('sSpace').value;
	if (pr == 0) {
		$('sSpace').value = 1;
	//	$('aSpace').removeClassName('sort_down');
		$('aSpace').className = 'sort_up';;
	} else 	if (pr == 1) {
		$('sSpace').value = 2;
	//	$('aSpace').removeClassName('sort_up');
		$('aSpace').className = 'sort_down';;
	} else if (pr == 2) {
		$('sSpace').value = 1;
	//	$('aSpace').removeClassName('sort_down');
		$('aSpace').className = 'sort_up';;
	}
	search();
}
function ordRooms() {
	resetPrice();
	var pr = $('sRooms').value;
	if (pr == 0) {
		$('sRooms').value = 1;
	//	$('aRooms').removeClassName('sort_down');
		$('aRooms').className = 'sort_up';;
	} else 	if (pr == 1) {
		$('sRooms').value = 2;
	//	$('aRooms').removeClassName('sort_up');
		$('aRooms').className = 'sort_down';;
	} else if (pr == 2) {
		$('sRooms').value = 1;
	//	$('aRooms').removeClassName('sort_down');
		$('aRooms').className = 'sort_up';;
	}
	search();
}