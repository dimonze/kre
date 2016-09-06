(function($) {

var narrow_window_width = 1200;
var narrow_class_name = 'page-narrow';

var ie7 = false;
if ($.browser.msie) {
  ie7 = (parseInt($.browser.version, 10) <= 7);
}
$(function() {
	change_layout();
	$(window).resize(change_layout);

  $('.upd-iwant-select-list').each(function() {
    var t = $(this);

    t.find('li:odd').addClass("upd-select-list-odd");
    t.find('li:last').addClass("upd-select-list-last").wrapInner('<div class="upd-select-list-last-wrap" />');
  })

	$(".upd-cat-info-all tr:odd").addClass("upd-cat-info-all-odd");

	$(".upd-iwant-select-list li")
		.css("cursor", "pointer")
		.click(function() {
			var select_obj = $(this).closest(".upd-iwant-select");
			$(".upd-iwant-select-control td", select_obj).text($('td', $(this)).text());
			$(".upd-iwant-select-control", select_obj).triggerHandler("click");
			var scroll_to = parseInt($(".upd-iwant-select-control").offset().top) - 50;
			if ($('body').scrollTop() > scroll_to || document.documentElement.scrollTop > scroll_to) {
				$('html, body').animate({
					scrollTop: scroll_to
				}, 'fast');
			}
			$("input", select_obj).val($(this).attr("id").split('_')[1]);
			return false;
	 	});

	$(".upd-iwant-select-control")
		.css("cursor", "pointer")
		.click(function() {
			var select_obj = $(this).closest(".upd-iwant-select");
			if (!select_obj.is(".upd-iwant-select-open")) {
				select_obj.addClass("upd-iwant-select-open");
				if (!ie7)
					$(this).next(".upd-iwant-select-list").slideDown('fast');
				else
					$(this).next(".upd-iwant-select-list").show(0);
			} else {
				$(this).next(".upd-iwant-select-list").slideUp('fast', function() {
					select_obj.removeClass("upd-iwant-select-open");
				});
			}
			return false;
		});

	$( document ).on('click', '.upd-cat-gallery-control', function() {
		$(this).next('.upd-cat-gallery-more').slideDown('fast');
		$(this).remove();
		return false;
	});


  $( document ).on('click', 'a.show-map', function() {
    var $link = $(this);
    var $map = $link.closest('div').next('.map');

    if (0 == $map.children().length) {
      $map.show();


      function init() {
        var map = new ymaps.Map($map[0], {
          center: [$link.attr('data-lat'), $link.attr('data-lng')],
          zoom: 15
        }),
        placemark = new ymaps.Placemark([$link.attr('data-lat'), $link.attr('data-lng')], {
          //iconContent: $link.attr('data-title')
        }, {
          preset: 'twirl#redStretchyIcon'
        });
        map.geoObjects.add(placemark);
        map.controls
          .add('zoomControl')
          .add('typeSelector')
          .add('mapTools');
      }
      ymaps.ready(init);
      $map.hide();
    }
    $map.toggle();
    return false;
  });

  InitializeColorBox = function (){
    $.each($.unique($('a.gallery').map(function(i, item) { return item.rel } ).toArray()), function(i, rel) {
      jQuery('a.gallery[rel=' + rel + ']').colorbox({
        opacity: 0.5,
        scalePhotos: true,
        current: "{current} / {total}",
        maxWidth: "100%",
        maxHeight: "100%",
        title: function(){
          var alt = $(this).find('img').attr('alt');
          if (alt){return alt} else {return ' '};
        }
      });
    });
  }

  $('.hDescHide a').bind('click', function() {
    var c = $(this).parent().parent();
    c.find('.hDesc').css('height', 'auto');
    c.find('.hDescHide').hide();
    c.find('.fadeout').hide();
    return false;
  });

  function show_more_link() {
    var block_h = 182;
    $('.hDesc').css('height', 'auto').each(function(){
      var c = $(this).parent();
      var h = $(this).outerHeight() - 16; // 16px - margin
      if (h>block_h) {
        c.find('.hDesc')[0].style.height= block_h + 'px';
        c.find('.hDescHide').show();
      } else {
        c.find('.hDesc')[0].style.height='auto';
        c.find('.hDescHide').hide();
        c.find('.fadeout').hide();
      }
    });
  }

  $('.hDesc')[0] && show_more_link() && $(window).resize(function(){
    show_more_link();
  });

  $('.short-desc').each(function(){
    if(!($(this).find('p').length < 2 && $(this).find('p:last').text() == '')) {
      var url = $(this).attr('rel');
      
      switch( $(this).find('p.last_short_link').prev()[0].tagName.toUpperCase() ) {
          case 'P':
          case 'SPAN':
          case 'DIV':
              $(this).find('p.last_short_link').prev().append('&nbsp;<a href="' + url + '" class="upd-pub-more"><img src="/layout/pics/upd-more.gif" alt=""></a>');
              break;
          case 'OL':
          case 'UL':
              $(this).find('li:last').append('&nbsp;<a href="' + url + '" class="upd-pub-more"><img src="/layout/pics/upd-more.gif" alt=""></a>');
              break;
          default:
              $(this).find('p.last_short_link').prev().after('<a href="' + url + '" class="upd-pub-more"><img src="/layout/pics/upd-more.gif" alt=""></a>');
              break;
      }
      $(this).find('p.last_short_link').remove();
    }
  });

  $('.mortgage_button').click(function(){
    var w = 450;
    var h = 400;
    var left = Math.round((screen.availWidth - w)/2);
    var top  = Math.round((screen.availHeight - h)/2);
    //console.log(w,h,left,top,screen.availWidth, screen.availHeight);
    window.open($(this).attr('href'), 'calc', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width='+w+', height='+h+', left='+left+',top='+top);
    return false;
  });

  var m_wait = $('.search-form-wait');
  var m_err  = $('.search-form-not-found');

  $('.upd-search-cond a').click(function(){
    $('.upd-search-cond-active').removeClass('upd-search-cond-active');
    $(this).parent().addClass('upd-search-cond-active');
    var _data  = $(this).attr('rel').split('|');
    var _input = $('.select_place input[name="value"]');
    _input.val() == _input.attr('rel') ? _input.val(_data[1]) : null;
    _input.attr('rel', _data[1]);
    $('.select_place input[name="field"]').val(_data[0]);
    m_err.hide();
    return false;
  });

  $('.select_place input[name="value"]')
    .focus(function(){
      $(this).val() == $(this).attr('rel') ? $(this).val('') : null;
    })
    .blur(function(){
      $(this).val() == '' ? $(this).val($(this).attr('rel')) : null;
    });

  $('.form.search-form').submit(function(){
    if ($('.select_place input[name="value"]').val() == $('.select_place input[name="value"]').attr('rel')) {
      return false;
    }

    if($('.select_place input[name="field"]').val() == 'lot') {
      m_err.hide();
      m_wait.show();
      $.getJSON('/offers/search/', {field: 'lot', value: $('.select_place input[name="value"]').val()}, function(result){

        if(result.url != false) {
          m_wait.find('p').text('Происходит перенаправление...');
          window.location = result.url
        }
        else{
          m_wait.hide();
          m_err.show();
        }
      });
      return false;
    }
  });

  function get_cookie_name()
  {
    var cookie_name = 'selected_ids_';
    if (-1 != String(location.href).indexOf('offers/search')) {
      var loc = String(location.href).split('?')[1].split('#')[0];
      cookie_name += 'search_' +
        loc.split('field=')[1].split('&')[0] + '_' +
        loc.split('value=')[1].split('&')[0];
    }
    else {
      cookie_name += $('#header').attr('class');
    }
    return cookie_name;
  }

  function update_selected_link()
  {
    var _cc = $.cookie(get_cookie_name());
    var url;

    if (String(_cc).split(',').length > 0) {
      var cookie_name = get_cookie_name();
      if (-1 != cookie_name.indexOf('_search_')) {
        url = '/offers/search/?field=' + cookie_name.split('_')[3] +
                             '&value=' + cookie_name.split('_')[4] +
                             '&ids='   + _cc;
      }
      else {
        url = '/offers/' + $('#header').attr('class') + '/?lots=' + _cc;
      }
    }
    else {
      url = '#';
    }

    $('a.some-lots').attr('href', url)
  }
  $('a.some-lots').length && update_selected_link();

  $('.upd-form-checkbox input').each(function(){
    var _cc = $.cookie(get_cookie_name());
    var _cnt = $('#selectedOffersCount').text() * 1;
    if(_cc == 'null' || _cc == null){
      return;
    }

    if(in_array($(this).attr('rel'), _cc.split(','))) {
      $(this).attr('checked', true);
      _cnt++;
    }
    $('#selectedOffersCount').text(_cnt);
  });

  $('.upd-form-checkbox input').click(function(){
    var _cnt = $('#selectedOffersCount').text() * 1;
    var _cc = $.cookie(get_cookie_name());
    if (_cc == 'null' || _cc == null) {
      _cc = '';
    }
    var _rel = $(this).attr('rel');
    if($(this).attr('checked')) {
      _cnt++;
      _cc += _rel + ',';
    }
    else {
      _cnt--;
      var _cc0 = '';
      var ids = _cc.split(',');
      for(i=0;i<(ids.length-1);i++){
        if(ids[i]!=_rel){
          _cc0 += ids[i] + ',';
        }
      }
      _cc = _cc0;
    }

    $.cookie(get_cookie_name(), _cc, {path: '/'});
    update_selected_link();
    $('#selectedOffersCount').text(_cnt);
   });

  $('.clearSelected').click(function(){
    $('#selectedOffersCount').text(0);
    $('.upd-form-checkbox input').attr('checked', false);
    $.cookie(get_cookie_name(), '', {path: '/'});
    update_selected_link();
    return false;
  });

  if (jQuery().autocomplete) {
    $('.ac_input').result(function (data, event){
      var elem = $(this).attr('id').split('_').pop();
      var val  = $(this).val();
      var list = $('.ac-list.for-' + elem);
      var input = $('input[name="' + elem + '-list"]');
      var delim = elem == 'street' ? '|' : ',';
      var values;
      var text = '';

      if(list.length == 0) {
        $(this).closest('.select').after('<div class="ac-list for-' + elem + '"></div>');
      }

      if(input.length == 0) {
        $(this).closest('.select').after('<input type="hidden" name="' + elem + '-list">');
        values = new Array();
      }
      else {
        values = input.val().split(delim)
      }

      if(!in_array(val, values)){
        values.push(val);
      }

      $(this).val('');
      drawList(elem, values);
    })
    .blur(function(){
      var elem = $(this).attr('id').split('_').pop();
      $('input[name="' + elem + '"]').val($('input[name="' + elem + '-list"]').val());
    });
  }

  $('.ac_input').each(function(){
    var hidden = $(this).prev();
    var elem = hidden.attr('id');
    var list = $('.ac-list.for-' + elem);
    var input = $('input[name="' + elem + '-list"]');
    var delim = elem == 'street' ? '|' : ',';
    if(hidden.val()){
      if(list.length == 0) {
        $(this).closest('.select').after('<div class="ac-list for-' + elem + '"></div>');
      }

      if(input.length == 0) {
        $(this).closest('.select').after('<input type="hidden" name="' + elem + '-list">');
      }
    }
    drawList(elem, hidden.val().split(delim));
    $(this).val('');
  });

  $('.upd-form input[type="submit"]').click(function(){
    $('.ac-list').each(function(){
      $(this).parent().find('input[type="hidden"][name$="-list"]').remove();
    });
  });

  $('.upd-form input[type="submit"]').click(function(){
    if($('input#id[name="id"]').val()) {

      m_err.hide();
      m_wait.show();
      $.getJSON('/offers/search/', {field: 'lot', value: $('input#id[name="id"]').val()}, function(result){

        if(result.url != false) {
          m_wait.find('p').text('Происходит перенаправление...');
          window.location = result.url
        }
        else{
          m_wait.hide();
          m_err.show();
        }
      });
      return false;
    }
  });

});

function change_layout() {
	if ($(window).width() <= 1100) {
		$("body").addClass(narrow_class_name);
	} else {
		$("body").removeClass(narrow_class_name);
	}
}

$(function(){
  $("a").focus(function(){
    this.blur();
  });
});
})(jQuery);


function drawList(where, list) {
  var text = '';
  var elem;
  var list2 = new Array();
  var delim = where == 'street' ? '|' : ',';
  for(i = 0; i < list.length; i++) {
    elem = list[i];
    if(elem == '') {continue;}
    list2.push(elem);
    text += " " + elem + '<span class="like-link del-' + where + i + '" data-content="' + elem + '" data-target="'
      + where + '" onclick="remove_elem(\'' + where + i + '\')">[x]</span>';
    if(i+1 < list.length) {
      text += ',';
    }
  }
  jQuery('.ac-list.for-' + where).html(text);
  jQuery('input[name="' + where + '"]').val(list2.join(delim));
  jQuery('input[name="' + where + '-list"]').val(list2.join(delim));
}

function remove_elem(cl) {
  var elem = jQuery('.del-' + cl).data('target');
  var delim = elem == 'street' ? '|' : ',';
  var values = jQuery('input[name="' + elem + '-list"]').val().split(delim);
  var value = jQuery('.del-' + cl).data('content');
  var values2 = new Array();

  for(k=0;k<values.length;k++) {
    if(value != values[k]) {
      values2.push(values[k]);
    }
  }
  drawList(elem, values2);
  return false;
}

function hideMenu(){
if (jQuery(".upd-ul-sublist li").size() > 5){
	jQuery(".upd-ul-sublist").append("<p id=show-all><b>Показать все</b><i> &or;</i></p>");
	var liToShow = jQuery(".upd-ul-sublist").attr("value");
	var liHide = jQuery(".upd-ul-sublist li:gt(" + liToShow + ")");
	liHide.hide();
	jQuery("#show-all").click(function(){
		if (liHide.is(":hidden")){
			liHide.show();
			jQuery("#show-all").html("<b>Скрыть</b><i> &and;</in>");
		}
		else{
			liHide.hide();
			jQuery("#show-all").html("<b>Показать все</b><i> &or;</i>");
		}
	}
	)
	}
}

function smallLastGallery(){
	jQuery('.upd-cat-gallery-more .cat_gallery').each(function(){
	var td = jQuery(this).find('td');
	if (td.size() % 2 == 1){
		td.last().css('width','56%');
	}
	})
}

function in_array (needle, haystack, argStrict) {
  var key = '',
    strict = !! argStrict;

  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true;
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) {
        return true;
      }
    }
  }

  return false;
}

function ordDownBudget(){
  var _list = jQuery('.sel-order-cat-budget');

  var hidden_val = _list.parent().find('input[type=hidden]').val();
  if (hidden_val != '') {
    var _html = _list.find('#sel_id1_' + hidden_val).html();
    _list.find('li:eq(0)').html(_html);
  }

  _list.find('li:gt(0)').addClass('ord-sub-li');
  _list.each(function(){
    jQuery(this).find('li:gt(0)').hide();
    jQuery(this).find('li').last().addClass('ord-sub-li-last');
  })

  _list.find('li:eq(0)').click(function(){
  if (jQuery(this).parent().find('li:gt(0)').is(':hidden')){
    jQuery(this).parent().find('li:gt(0)').slideDown(100);
    jQuery(this).parent().parent().find('span').addClass('ord-up-arrow');
    }
  else{
    jQuery(this).parent().find('li:gt(0)').hide();
    jQuery(this).parent().parent().find('span').removeClass('ord-up-arrow');
  }
  })

  _list.find('li:gt(0)').click(function(){
    jQuery('.sel_type_upd1').removeClass('ord-up-arrow');
    var _html = jQuery(this).html();
    _list.find('li:gt(0)').hide();
    jQuery(this).parent().find('li:eq(0)').html(_html);
    jQuery(this).parent().parent().find('input').val(jQuery(this).find('span').attr('rel'));
  })

  jQuery('#ord-cat-form .select_button').click(function(){
    jQuery('#ord-cat-form form').trigger('submit');
  })
}

function ordDownVersion(){
  var _list = jQuery('.sel-order-cat-version');

  var hidden_val = _list.parent().find('input[type=hidden]').val();
  if (hidden_val != '') {
    var _html = _list.find('#sel_id2_' + hidden_val).html();
    _list.find('li:eq(0)').html(_html);
  }

  _list.find('li:gt(0)').addClass('ord-sub-li');
  _list.each(function(){
    jQuery(this).find('li:gt(0)').hide();
    jQuery(this).find('li').last().addClass('ord-sub-li-last');
  })

  _list.find('li:eq(0)').click(function(){
  if (jQuery(this).parent().find('li:gt(0)').is(':hidden')){
    jQuery(this).parent().find('li:gt(0)').slideDown(100);
    jQuery(this).parent().parent().find('span').addClass('ord-up-arrow');
    }
  else{
    jQuery(this).parent().find('li:gt(0)').hide();
    jQuery(this).parent().parent().find('span').removeClass('ord-up-arrow');
  }
  })

  jQuery('.sel_type_upd1').click(function(){
  if (jQuery(this).prev().find('li:gt(0)').is(':hidden')){
    jQuery(this).prev().find('li:gt(0)').slideDown(100);
    jQuery(this).addClass('ord-up-arrow');
    }
  else{
    jQuery(this).prev().find('li:gt(0)').hide();
    jQuery(this).removeClass('ord-up-arrow');
  }
  })

  _list.find('li:gt(0)').click(function(){
    jQuery('.sel_type_upd1').removeClass('ord-up-arrow');
    var _html = jQuery(this).html();
    _list.find('li:gt(0)').hide();
    jQuery(this).parent().find('li:eq(0)').html(_html);
    jQuery(this).parent().parent().find('input').val(jQuery(this).find('span').attr('rel'));
  })

  jQuery('#ord-cat-form .select_button').click(function(){
    jQuery('#ord-cat-form form').trigger('submit');
  })
}

jQuery(document).ready(function() {
	hideMenu();
	smallLastGallery();
	ordDownBudget();
	ordDownVersion();

	jQuery('#sel_id1 li').click(function(){
		_vl = jQuery(this).attr("rel");
		jQuery('#claim_type').val(_vl);
	})
	jQuery('form[name="iwant"]').find('.select_button').click(function(){
		jQuery('form[name="iwant"]').trigger('submit');
	})

	jQuery('.vacancy-name').click(function(){
		if (jQuery(this).next().is(':animated')){
		 return false;
		} else {
		jQuery(this).next().slideToggle();
		}
	})

 	jQuery('.offer-anons').each(function(){
	if (jQuery(this).children().is('p')){
		jQuery(this).children('a').last().css('margin-left','5px');
		jQuery(this).children('p').last().append(jQuery(this).children('a').last());
		jQuery(this).children('a').remove();
		}
	})
}
);