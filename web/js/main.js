var count = new Array();
count[1] = 0;
count[2] = 0;

function sMainTypeChange(){
	obj = document.getElementById('sel_id1_v');
    sel = document.getElementById('sel_id1');
	act = document.getElementById('sel_id2_v').value;
	but = document.getElementById('sel_type');
	var aObj = $(sel);
    switch (act){
    	case "0": //купить
      		obj.value = '0';
		    sel.innerHTML = '<li id="sel_id1_0">Элитную квартиру</li><li id="sel_id1_1">Элитную квартиру в новостройке</li><li id="sel_id1_2">Пентхаус</li><li id="sel_id1_3">Загородный дом, участок</li><li id="sel_id1_4">Коммерческую недвижимость</li>';   	
			but.innerHTML = '<span class="select_up" onclick="sUp(\'sel_id1\',\'2\',5)">&nbsp;</span><span class="select_down" onclick="sDown(\'sel_id1\',\'2\',5)">&nbsp;</span></span>';
		break;
	    case "1": //продать
	    case "2": //сдать
     	case "3": //снять 
       		obj.value = '0';
		    sel.innerHTML = '<li id="sel_id1_0">Квартиру</li><li id="sel_id1_1">Коммерческую недвижимость</li>';
			but.innerHTML = '<span class="select_up" onclick="sUp(\'sel_id1\',\'2\',2)">&nbsp;</span><span class="select_down" onclick="sDown(\'sel_id1\',\'2\',2)">&nbsp;</span></span>';
	    break;
    }
	aObj.style.top = '0px';
	count[2] = 0;
};

function sUp(obj,cItem,obs){
	var aObj = $(obj);
	var objSize = obs-1;
	if (count[cItem] != 0){
		aObj.style.top = (parseInt(aObj.style.top))+27+'px';
		count[cItem]--;
		$(obj+'_v').value = count[cItem];
	} else if (count[cItem] == 0){
		aObj.style.top = -27*objSize+'px';
		count[cItem] = objSize;
		$(obj+'_v').value = count[cItem];
	};
	if (obj=='sel_id2') sMainTypeChange();
};

function sDown(obj,cItem,obs){
	var aObj = $(obj);
	var objSize = obs-1;
	if (count[cItem] != objSize){
		aObj.style.top = (parseInt(aObj.style.top))-27+'px';
		count[cItem]++;
		$(obj+'_v').value = count[cItem];
	} else if (count[cItem] == objSize){
		aObj.style.top = 0+'px';
		count[cItem] = 0;
		$(obj+'_v').value = count[cItem];
	};
	if (obj=='sel_id2') sMainTypeChange();
};

function sUpT(obj,cItem,obs){
	var aObj = $(obj);
	var objSize = obs-1;
	if (count[cItem] != 0){
		aObj.style.top = (parseInt(aObj.style.top))+44+'px';
		count[cItem]--;
		$(obj+'_v').value = count[cItem];
	} else if (count[cItem] == 0){
		aObj.style.top = -44*objSize+'px';
		count[cItem] = objSize;
		$(obj+'_v').value = count[cItem];
	};
	if (obj=='sel_id2') sMainTypeChange();
};

function sDownT(obj,cItem,obs){
	var aObj = $(obj);
	var objSize = obs-1;
  var claim_type = document.getElementById('claim_type');
  
	if (count[cItem] != objSize){
		aObj.style.top = (parseInt(aObj.style.top))-44+'px';
		count[cItem]++;
    $(obj+'_v').value = count[cItem];
		claim_type.value = count[cItem] + 1;
	} else if (count[cItem] == objSize){
		aObj.style.top = 0+'px';
		count[cItem] = 0;
    $(obj+'_v').value = count[cItem];
    claim_type.value = count[cItem] + 1;
	};
	if (obj=='sel_id2') sMainTypeChange();
};

function showMap(){
	$('map').show();
};
/* function sUpS(obj,cItem,obs){
	var aObj = $(obj);
	var objSize = obs-1;
	if (count[cItem] != 0){
		aObj.style.top = (parseInt(aObj.style.top))+27+'px';
		count[cItem]--;
		$(obj+'_v').value = count[cItem];
	} else if (count[cItem] == 0){
		aObj.style.top = -27*objSize+'px';
		count[cItem] = objSize;
		$(obj+'_v').value = count[cItem];
//    jQuery.('')
	};
};

function sDownS(obj,cItem,obs){
	var aObj = $(obj);
	var objSize = obs-1;
	if (count[cItem] != objSize){
		aObj.style.top = (parseInt(aObj.style.top))-27+'px';
		count[cItem]++;
		$(obj+'_v').value = count[cItem];
	} else if (count[cItem] == objSize){
		aObj.style.top = 0+'px';
		count[cItem] = 0;
		$(obj+'_v').value = count[cItem];
	};
}; */

jQuery(function(){
  if(device.mobile())
  {
    jQuery( "<a class='tel' href='tel:+74959567799'><span class='area-code'>+7 (495)</span> <span class='number'>956-77-99</span></a>" ).appendTo( ".mobile-top span" ); 
  }
  else
  {
    jQuery( "<span class='tel'><span class='area-code'>+7 (495)</span> <span class='number'>956-77-99</span></span>" ).appendTo( ".mobile-top span" ); 
  }  
});