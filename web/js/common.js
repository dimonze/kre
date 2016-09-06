function prnt(){
var newurl=document.location.pathname+(document.location.pathname.charAt(document.location.pathname.length-1)=='/'?'':'/')+'print/';
document.location.href=newurl;
}

function imgPopup(url,width,height){
  var left=Math.round((screen.availWidth-width)/2);
  var top=Math.round((screen.availHeight-height)/2);
  window.open('/viewimage/?i='+url,"","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width="+width+", height="+height+", left="+left+",top="+top);
}

function openCalc(sum) {
	var left=Math.round((screen.availWidth-450)/2);
	var top=Math.round((screen.availHeight-400)/2);
	window.open('/offers/calc/?s='+sum,"calc","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width="+450+", height="+400+", left="+left+",top="+top);
}
function waypoint(obj) {
	type = document.getElementById('sel_id1_v').value;
	act = document.getElementById('sel_id2_v').value;
      switch (act) {
    	case "0": //купить
      		switch (type) {
        		case "0": //элитная квартира
          		obj.action='/offers/1/';
          	break;
        		case "1": //элитная новостройка
		          obj.action='/offers/2/';
          	break;
        		case "2": //пентхаус
		          obj.action='/offers/3/';
          	break;
        		case "3": //загородный дом
		          obj.action='/offers/5/';
          	break;
        		case "4": //коммерческая недвижимость
		          obj.action='/offers/6/';
          	break;
         	}
     	break;
	    case "1": //продать
	    case "2": //сдать
		      obj.action='/claim/';
	    break;
		case "3": //снять
      		switch (type) {
        		case "0": //квартира
          		obj.action='/offers/4/';
          	break;
        		case "1": //коммерческая недвижимость
		          obj.action='/offers/7/';
          	break;
         	}
		break;
    	}
    obj.submit();
}

jQuery(document).ready(function() {
	jQuery('#cboxPrevious').attr('title', 'Предыдущее изображение');
	jQuery('#cboxNext').attr('title', 'Следующее изображение');

  jQuery('.vacancy_box > a').click(function(){
		var nh = jQuery(this).html() == 'Открыть' ? 'Закрыть' : 'Открыть';
		if (!jQuery(this).siblings('.vinfo').is(':animated')){
		jQuery(this).html(nh);
		jQuery(this).siblings('.vinfo').slideToggle();
		}
    return false;
	});

  jQuery('.vacancy_box form').submit(function(){
    var $form = jQuery(this);
    $form.find('i.error').remove();

    if (typeof window.FormData == 'undefined') {
      $form.ajaxSubmit({
        dataType: 'json',
        success: function(responseText, statusText, xhr, $form){
          vacancyFormResponse(responseText, $form);
        }
      });
    }
    else {
      var formData = new FormData($form[0]);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', $form.attr('action'), false);
      xhr.onreadystatechange = function(){
        if (xhr.readyState == 4) {
          vacancyFormResponse(jQuery.parseJSON(xhr.responseText), $form)
        }
      };
      xhr.send(formData);
    }

    return false;
  });
});


function vacancyFormResponse(data, $form)
{
  if (data.success) {
    $form.trigger('reset');
	alert('Ваша заявка принята.');
  }
  else {
    jQuery.each(data.errors, function(k,v){
      $form.find('.input-'+k)
          .closest('td')
          .siblings()
          .eq(0)
          .append(jQuery('<i>').addClass('error').text(v));
    });
  }
}