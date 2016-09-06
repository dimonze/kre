jQuery(document).ready(function($) {


  // delete presentation fields
  (function ($) {

    var
    prBox   = $('.pr_editable'),
    prDescr = prBox.find('.description'),
    prInfo  = prBox.find('#lot-info-table p'),
    prEls,
    delBtn  = '<div class="pr-del-btn__box" contenteditable="false">' +
                '<span class="pr-del-btn"></span>' +
              '</div>';

    prEls = prBox
              .find('td:first-child')
              .not('.separator_image')
              .add(prDescr)
              .add(prInfo);

    prEls.prepend(delBtn);

    prEls.on('click', '.pr-del-btn', function() {
      var
      t = $(this),
      tTable = t.closest('.table-content');

      t.closest('tr').remove();
      t.closest('p').remove();
      t.closest('.description').remove();

      if (!tTable.find('td').not('.separator_image').length) { // empty table

        tTable.parents('.presentation-no-break').remove();
        tTable.remove();

      }

    })


  })(jQuery)




  $('input#all').change(function(){
    var state = $(this).attr('checked') || false;
    $('.select-images input[type="checkbox"]').each(function(){
      var this_state = $(this).attr('checked') || false;
      state != this_state && $(this).attr('checked', state).change();
    });
  });


  $('.select-images .check input').change(function(){
    if($(this).attr('checked')) {
      window.opener.i_add($(this).attr('rel'), $(this).attr('id'));
    }
    else {
      window.opener.i_del($(this).attr('rel'));
    }
		window.opener.imgOdd();
		window.opener.checkImg()
  });

  $('.icons.i_photos').click(function(){
    var data = $(this).attr('rel').split('|');
    var left=Math.round(($('body').width()-600)/2);
    var top=Math.round((screen.availHeight-400)/2);
    window.open("/offers/"+data[1]+"/images/"+data[0]+'/?s=' + function(){
      var ids = '';
      $('.for-images img').each(function(){
        ids += $(this).attr('rel') + ',';
      });
      return ids;
    }(),
      "photo",
      "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width="+600+", height="+400+", left="+left+", top="+top);
    return false;
  });


  $('.trigger').click(function(){
    $(this).toggleClass('act');
    if($(this).hasClass('no-headers')){
      $(this).hasClass('act') ? $('.h-hide').hide() : $('.h-hide').show();
    }

    if ($(this).hasClass('no-watermarks')){
      if($(this).hasClass('act')) {
        $('#content p img, .for-images img').each(function(){
          $(this).attr('src', $(this).attr('src').replace(/\/pres\//, '/pres_/'));
          $(this).attr('src', $(this).attr('src').replace(/\/full\//, '/full_/'));
        });
      }
      else {
        $('#content p img, .for-images img').each(function(){
          $(this).attr('src', $(this).attr('src').replace(/\/pres_\//, '/pres/'));
          $(this).attr('src', $(this).attr('src').replace(/\/full_\//, '/full/'));
        });
      }
    }
    return false;
  });

  $('div.presentation.description').keyup(function() {
    var elem = $(this);
    var limit = 500;
    if (elem.hasClass('auth')) {
      limit = 750;
    }
    elem.text().length > limit && elem.text(substr(elem.text(),0,limit));
  });

  //check presentation description
  var elem = $('div.presentation.description');
  var limit = 500;
  if (elem.hasClass('auth')) {
    limit = 750;
  }
  elem.text().length > limit && elem.text(substr(elem.text(),0,limit));

  $('#savelink, #openlink').click(function(){
    var link = $(this);
    var _id = $('p.lot').attr('value');
    var headers = !$('a.no-headers').hasClass('act');

    $('.pr-del-btn__box').remove();
    $('.lot').remove();
    $('.not-for-pdf, tr:has(td.separator_image)').remove();
    $('.separator').remove();
    $('.in_cat tr:last td').css('padding-bottom','0');
    $('p.pres-image img').css('margin-left','30px');
    $('.table-content')
      .prepend('<tr><td colspan="5" class="bottom_separator"></td></tr>')
    $('.table-content:not(.table-content:last)')
      .append('<tr><td colspan="5" class="bottom_separator"></td></tr>');
    $('.for-images').removeClass('print-images');
    $('#content').removeClass('print-pres');

    if ($('img').is('.for-images img')){
      $('.for-images').before('<pagebreak />');
    } else{
      $('.for-images').remove();
      $('.clear').remove();
    }
    $('img[rel]').attr('style', null);
    $('.h-hide').remove();
    $('.clear29').remove();
    $('#print_header td').css('padding-left','30px' );
    $('#lot-info-table').removeClass('lot-info-table');
    $('.for-images img').css({'width' : '100%', 'margin-bottom' : '10px', 'display' : 'block', 'vertical-align' : 'top'});

    $.post('/offers/print/pdf/', {html: $('html').html(), headers: headers, id: _id}, function(data){
      if (link.attr('id') == 'savelink') {
        window.location.href = link.attr('href') +'?pdf='+ data.replace(/^.+\//, '');
      }
      else {
        window.location.href = data;
      }
    });

    $('body').html('<div style="text-align:center; padding-top:15em;">Идет подготовка...</div>');
    return false;
  });
});

function i_add(source, iid) {
	$('.for-images').html($('.for-images').html() + '<img rel="' + iid  + '"  src="' + source + '"/> '); //margin
        if ($('.no-watermarks').length > 0){
           if ($('.act').length > 0){
            $('#content p img, .for-images img').each(function(){
              $(this).attr('src', $(this).attr('src').replace(/\/pres\//, '/pres_/'));
              $(this).attr('src', $(this).attr('src').replace(/\/full\//, '/full_/'));
            });
          }
          else {
            $('#content p img, .for-images img').each(function(){
              $(this).attr('src', $(this).attr('src').replace(/\/pres_\//, '/pres/'));
              $(this).attr('src', $(this).attr('src').replace(/\/full_\//, '/full/'));
            });
          }
        }
}

function i_del(source) {
  $('.for-images img[src="' + source + '"]').remove();
}
function imgOdd() {
	$('.for-images img').removeClass('odd-img');
	$('.for-images img:odd').addClass('odd-img');
}
function checkImg() {
	if ($('.for-images img').size() > 0){
		$('.for-images').addClass('hasIMG');
	}
}

function substr (str, start, len) {
    // Returns part of a string
    //
    // version: 909.322
    // discuss at: http://phpjs.org/functions/substr
    // +     original by: Martijn Wieringa
    // +     bugfixed by: T.Wild
    // +      tweaked by: Onno Marsman
    // +      revised by: Theriault
    // +      improved by: Brett Zamir (http://brett-zamir.me)
    // %    note 1: Handles rare Unicode characters if 'unicode.semantics' ini (PHP6) is set to 'on'
    // *       example 1: substr('abcdef', 0, -1);
    // *       returns 1: 'abcde'
    // *       example 2: substr(2, 0, -6);
    // *       returns 2: false
    // *       example 3: ini_set('unicode.semantics',  'on');
    // *       example 3: substr('a\uD801\uDC00', 0, -1);
    // *       returns 3: 'a'
    // *       example 4: ini_set('unicode.semantics',  'on');
    // *       example 4: substr('a\uD801\uDC00', 0, 2);
    // *       returns 4: 'a\uD801\uDC00'
    // *       example 5: ini_set('unicode.semantics',  'on');
    // *       example 5: substr('a\uD801\uDC00', -1, 1);
    // *       returns 5: '\uD801\uDC00'
    // *       example 6: ini_set('unicode.semantics',  'on');
    // *       example 6: substr('a\uD801\uDC00z\uD801\uDC00', -3, 2);
    // *       returns 6: '\uD801\uDC00z'
    // *       example 7: ini_set('unicode.semantics',  'on');
    // *       example 7: substr('a\uD801\uDC00z\uD801\uDC00', -3, -1)
    // *       returns 7: '\uD801\uDC00z'
    // Add: (?) Use unicode.runtime_encoding (e.g., with string wrapped in "binary" or "Binary" class) to
    // allow access of binary (see file_get_contents()) by: charCodeAt(x) & 0xFF (see https://developer.mozilla.org/En/Using_XMLHttpRequest ) or require conversion first?
    var i = 0,
        allBMP = true,
        es = 0,
        el = 0,
        se = 0,
        ret = '';
    str += '';
    var end = str.length;

    // BEGIN REDUNDANT
    this.php_js = this.php_js || {};
    this.php_js.ini = this.php_js.ini || {};
    // END REDUNDANT
    switch ((this.php_js.ini['unicode.semantics'] && this.php_js.ini['unicode.semantics'].local_value.toLowerCase())) {
    case 'on':
        // Full-blown Unicode including non-Basic-Multilingual-Plane characters
        // strlen()
        for (i = 0; i < str.length; i++) {
            if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                allBMP = false;
                break;
            }
        }

        if (!allBMP) {
            if (start < 0) {
                for (i = end - 1, es = (start += end); i >= es; i--) {
                    if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                        start--;
                        es--;
                    }
                }
            } else {
                var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
                while ((surrogatePairs.exec(str)) != null) {
                    var li = surrogatePairs.lastIndex;
                    if (li - 2 < start) {
                        start++;
                    } else {
                        break;
                    }
                }
            }

            if (start >= end || start < 0) {
                return false;
            }
            if (len < 0) {
                for (i = end - 1, el = (end += len); i >= el; i--) {
                    if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                        end--;
                        el--;
                    }
                }
                if (start > end) {
                    return false;
                }
                return str.slice(start, end);
            } else {
                se = start + len;
                for (i = start; i < se; i++) {
                    ret += str.charAt(i);
                    if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                        se++; // Go one further, since one of the "characters" is part of a surrogate pair
                    }
                }
                return ret;
            }
            break;
        }
        // Fall-through
    case 'off':
        // assumes there are no non-BMP characters;
        //    if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
    default:
        if (start < 0) {
            start += end;
        }
        end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
        // PHP returns false if start does not fall within the string.
        // PHP returns false if the calculated end comes before the calculated start.
        // PHP returns an empty string if start and end are the same.
        // Otherwise, PHP returns the portion of the string from start to end.
        return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
    }
    return undefined; // Please Netbeans
}