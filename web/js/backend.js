$(function(){

  function init_limited_textarea(selector) {
    var sb = $('.sf_admin_actions li[class^="sf_admin_action_save"] button');
    sb.removeClass('disabled');
    $(selector).find('textarea[limit]').each(function() {
      if(!$(this).data('init')){
        var limit = $(this).attr('limit');
        var $span = $(this).after('<span class="chars"/>').next()

        $(this).bind('keyup', function() {
          var nb = $(this).val().length;
          $span.text('Осталось ' + (limit - nb) + ' из ' + limit);
          (limit - nb >= 0)
            ? $span.removeClass('error') && ($('span.chars.error').length == 0 && sb.removeClass('disabled'))
            : $span.addClass('error')    && sb.addClass('disabled');
        }).trigger('keyup');
        $(this).data('init', 1);
      }
    });
  }

  init_limited_textarea('body');

  $('.sf_admin_actions li .sf_button').click(function(){
    if(!$(this).hasClass('disabled')){
      $(this).addClass('disabled');
    }
    else{
      return false;
    }
  });


  $('#global-loading').bind('ajaxSend', function(){
    $(this).show();
  }).bind('ajaxComplete', function(){
    $(this).hide();
    init_limited_textarea('body');
  }).hide();

  //handle photo position value - refs #10804
  $('select.param-type-select').live('click', function(e){
    $(this).change(function(){ //fuck you IE!! #11055
      var $select1 = $(this);
      var photo_id = $select1.attr('name').match(/lot\[Photos\]\[(\d+)\]/)[1];
      var position = 0;
      $('#lot_photos').find('select.param-type-select').each(function(){
        var $select2 = $(this);
        if ($select2.attr('id') != $select1.attr('id') && $select2.val() == $select1.val()) {
          var position2 = $('#lot_Photos_'+ $select2.attr('name').match(/lot\[Photos\]\[(\d+)\]/)[1] +'_position').val();
          position2 = parseInt(position2);
          position = position2 > position ? position2 : position;
        }
      });

      $('#lot_Photos_'+ photo_id +'_position').val(++position);
      $(this).unbind('change');
    });
  });

  //lot photos promote/demote/delete
  $('a', '#lot_photos').live('click', function(){
    $.get(this.href, {}, function(data){
      $('#lot_photos').replaceWith(data);
    }, 'html');

    return false;
  });

  //lot params promote/demote/delete
  $('td.link-actions a', '#lot_params').live('click', function(){
    $.get(this.href, {}, function(data){
      $('#lot_params').replaceWith(data);
    }, 'html');

    return false;
  });

  //adding new lot param
  $('a.add-lot-param', '#lot_params_new').live('click', function(){
    var $tr = $(this).parents('tr').clone();
    var n = $tr.children('td').eq(1).children('input').attr('name').match(/\d+/);
    n = parseInt(n)+1;

    $tr.children('td').eq(0).children('input[type=hidden]')
            .attr('name', 'lot[LotParamsNew]['+ n +'][param_id]')
            .attr('id', 'lot_LotParamsNew_'+ n +'_param_id')
            .val('');
    $tr.children('td').eq(0).children('input[type=text]')
            .attr('name', 'autocomplete_lot[LotParamsNew]['+ n +'][param_id]')
            .attr('id', 'autocomplete_lot_LotParamsNew_'+ n +'_param_id')
            .val('');
    $tr.children('td').eq(1).children('input[type=text]')
            .attr('name', 'lot[LotParamsNew]['+ n +'][value]')
            .attr('id', 'lot_LotParamsNew_'+ n +'_value')
            .val('');
    $tr.children('td').eq(2).children('select')
            .attr('name', 'lot[LotParamsNew]['+ n +'][param_type_id]')
            .attr('id', 'lot_LotParamsNew_'+ n +'_param_type_id')
            .val('');

    $tr.children('td').eq(0).children('script').remove();

    $('#lot_params_new table tbody').append($tr);
    $(this).remove();

    $('#autocomplete_lot_LotParamsNew_'+ n +'_param_id')
      .autocomplete('/backend.php/lot/matchParam', jQuery.extend({}, {
        dataType: 'json',
        parse:    function(data) {
          var parsed = [];
          for (key in data) {
            parsed[parsed.length] = {data: [data[key], key], value: data[key], result: data[key]};
          }
          return parsed;
        }
      }, { }))
      .result(function(event, data) {$('#lot_LotParamsNew_'+ n +'_param_id').val(data[1]);})
      .keyup(function() {
        if(this.value.length == 0) {
          $('#lot_LotParamsNew_'+ n +'_param_id').val('');
        }
    });

    return false;
  });


  $('select[name=lot\[type_real\]]').bind('change init', function(){
    var type = $(this).val();
    var lot_id = $('input[name=lot\[id\]]').val();
    var parent_id = $('select[name=lot\[pid\]]').val();

    $('#lot_pid').attr('last_val', $('#lot_pid').val()).find('option').remove();
    $('#lot_params').remove();

    if('cottage' == type || 'outoftown' == type){
      $('#lot_district_id, #lot_metro_id').parent().hide();
      $('#lot_ward, #lot_ward2').parent().show();
    }
    else {
      $('#lot_ward, #lot_ward2').parent().hide();
      $('#lot_district_id, #lot_metro_id').parent().show();
    }

    if ('eliteflat' == type) {
      $('#lot_is_penthouse').removeAttr('disabled');
    }
    else {
      $('#lot_is_penthouse').removeAttr('checked').attr('disabled', true);
    }

    if ('' == type || 'eliteflat' == type || 'flatrent' == type) {
      $('#lot_has_children').removeAttr('checked').attr('disabled', true);
    }
    else {
      $('#lot_has_children').removeAttr('disabled');
    }

    if ('elitenew' == type) {
      $('#lot_has_children').removeAttr('disabled').attr('checked', true).attr('readonly', true);
    }
    else {
      $('#lot_has_children').removeAttr('readonly');
    }


    if (type) {
      $.post('/backend.php/lot/paramsForm', {type: type, id: lot_id, pid: parent_id}, function(data){
        $('#sf_fieldset_parametry').append(data);
        addCalc();
      }, 'html');

      if (!$('#lot_has_children').attr('checked')){
        $.post('/backend.php/lot/pidsValues', {type: type, id: lot_id, pid: parent_id}, function(data){
          $('#lot_pid').append(data).val($('#lot_pid').attr('last_val'));
        }, 'html');
      }
    }
  }).trigger('init');


  $('#lot_has_children').bind('click', function() {
    $(this).attr('readonly') && $(this).attr('checked', true);
  });



  // Price-total autocalc
  //// total = price * area
  //// price = total / area

  $('input[name="lot[price_all][]"]:last').after(' <button class="calc calcPriceAll">');
  $('input[name="lot[price][]"]:last').after(' <button class="calc calcPrice">');
  $('button.calc').text('Рассчитать').addClass('sf_button ui-corner-all ui-state-default');
  
  var addCalc = function() {
    $('input[name="LotParams[price_land_to]"]')
      .after(' <button class="calc calcLand">')
      .siblings('.calc')
      .text('Рассчитать')
      .addClass('sf_button ui-corner-all ui-state-default');
  };
  

  $('button.calc').live('click', function(e){
  
    e.preventDefault();
  
    if ($(this).hasClass('calcPrice')) // Цена за квадратный метр
    {
      var me = 'lot[price][]',
      first  = 'lot[price_all][]',
      second = 'lot[area][]',
      action = 'divise';
      calcForms(me, first, second, action);
    }
    else if ($(this).hasClass('calcPriceAll')) // Цена общая
    {
      var me = 'lot[price_all][]',
      action = 'multiply',
      first  = 'lot[price][]', // Цена за квадратный метр
      second = 'lot[area][]'; // Площадь в квадратных метрах
      
      if (calcForms(me, first, second, action)){
        calcForms(me, first, second, action);
      } else {
        first = 'LotParams[price_land_', // Цена за сотку
        second = 'LotParams[spaceplot]'; // Площадь участка
        calcForms(me, first, second, action);
      }
    }
    else if ($(this).hasClass('calcLand')) // Стоимость за сотку
    {
      var me = 'LotParams[price_land_',
      first = 'lot[price_all][]',
      second = 'LotParams[spaceplot]',
      action = 'divise'
      calcForms(me, first, second, action);
    }

    
    function calcForms(me, first, second, action){
      
      $('.calcTo').removeClass('calcTo');
      $('.calcFrom').removeClass('calcFrom');
      
      var
      a_f = $('input[name^="' + first + '"]:first'),
      a_l = $('input[name^="' + first + '"]:last'),
      b_f = $('input[name^="' + second + '"]:first'),
      b_l = $('input[name^="' + second + '"]:last');

      a_f.addClass('calcFrom');
      a_l.addClass('calcFrom');
      b_f.addClass('calcFrom');
      b_l.addClass('calcFrom');
      
      a_f = a_f.val(),
      a_l = a_l.val(),
      b_f = b_f.val(),
      b_l = b_l.val();

      if (action == 'divise')
      {
        var first_val = (b_f > 0)  ? a_f / b_f : 0;
        var last_val  = (b_l > 0)  ? a_l / b_l : 0;
      }
      else if (action == 'multiply')
      {
        var first_val = a_f * b_f;
        var last_val  = a_l * b_l;
      }
      
      var calcSuccess = false;
      if (first_val > 0) {
        $('input[name^="' + me + '"]:first').val(Math.round(first_val)).addClass('calcTo');
        calcSuccess = true;
      }
      if (last_val  > 0) {
        $('input[name^="' + me + '"]:last').val(Math.round(last_val)).addClass('calcTo');
        calcSuccess = true;
      }
      
      return calcSuccess;
    }
  });

  $('input#lot_shortcut').after(' <button class="checkShortcut"> ');
  $('input#lot_shortcut').keydown(function(){
    $(this).removeClass('good bad');
  });
  $('button.checkShortcut').text('Проверить').addClass('sf_button ui-corner-all ui-state-default').click(function(){
    var inp = $('input#lot_shortcut');
    $.getJSON('/backend.php/lot/checkShortcut', {shortcut: inp.val(), id: $('input#lot_id').val()}, function(response){
      if(response.unique) {
        inp.removeClass('bad').addClass('good');
      }
      else {
        inp.removeClass('good').addClass('bad');
      }
    });
    return false;
  });

  $('.sf_admin_action_clone a').click(function(){
    $('#lot_clone_me').val(1);
    $('.sf_admin_action_save button').click();
    return false;
  });

  function toggleMarketFilter() {
    var
    marketFilter = $('#lot_filters_market'),
    typeFilter = $('select[id^="lot_filters_type_"]');
    
    toggleMarketFilterEngine();
    typeFilter.change(toggleMarketFilterEngine);

    function toggleMarketFilterEngine() {
      var
      valArr = typeFilter.val();

      if (valArr) {
        len = valArr.length;
        checkTypeArr(valArr);
      }

      function checkTypeArr(arr) {
        var len = arr.length;
        while (len--) {
          var el = arr[len]
          if (el === 'eliteflat' || el === 'penthouse' || el === 'outoftown') {
            marketFilter.removeAttr('disabled'); 
          } else {
            marketFilter.attr('disabled', 'disabled');
            break;
          }
        }
      };
    
    }

  };
  
  toggleMarketFilter();

});






