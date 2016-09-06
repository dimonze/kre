/*@cc_on
(function(f){
 window.setTimeout =f(window.setTimeout);
 window.setInterval =f(window.setInterval);
})(function(f){return function(c,t){var a=[].slice.call(arguments,2);return f(function(){c.apply(this,a)},t)}});
@*/


function Connector() {
  var app = '';
  var loc_href = String(window.location.href);
  if (loc_href.indexOf('backend_dev') != -1) {
    app = '/backend_dev.php';
  } else if (loc_href.indexOf('backend') != -1) {
    app = '/backend.php';
  } else if (loc_href.indexOf('admin_dev') != -1) {
    app = '/admin_dev.php';
  } else if (loc_href.indexOf('admin') != -1) {
    app = '/admin.php';
  }
  app += '/filemanager-connector/';

  this.options = {
    url: app,
    method: 'get',
    browser: '#browser',
    toolbar: '#toolbar',
    path: '#path',
    status: '#status',
    type: ''
  };
  
  this.selected = {};
  this.history = [];
  this.buffer = false;

  this.init = function () {
    this.$browser = $(this.options.browser);
    this.$toolbar = $(this.options.toolbar);
    this.$path = $(this.options.path);
    this.$status = $(this.options.status);

    if (String(location.href).indexOf('insert=') != -1) {
      this.options.tinymceinsert = String(String(location.href).split('insert=')[1]).replace(/[#&]+.+$/, '')
    }

    if (String(location.href).indexOf('type=') != -1) {
      this.options.type = String(String(location.href).split('type=')[1]).replace(/[#&]+.+$/, '')
    }

    this.initToolbar();
    window.setInterval(this.historyWatcher, 100, this);

    $.SetImpromptuDefaults({
      opacity: 0.8,
      overlayspeed: 0,
      promptspeed: 0
    });

    this.initialJQ();
  };

  this.loadContent = function (path, nocache) {
    this.current_path = path;
    this.showLoading();
    $.ajax({
      url: this.options.url + 'content',
      method: this.options.method,
      cache: nocache ? false : true,
      data: 'path='+this.encodeData(path)+'&thumbs=64x64&format=json',
      dataType: 'json',
      obj: this,
      success: this.parseContent
    })
  };

  this.parseContent = function (json) {
    obj = this.obj;
    obj.$browser.html('');
    json = obj.resort(json);
    folders = files = intsize = 0;

    for (i in json) {
      cnode = json[i];

      if (cnode.name == '.') {
        obj.current_path_info = cnode;
        continue;
      }
      if (obj.options.type && obj.options.type != cnode.ico && cnode.type != 'folder') {
        continue;
      }

      link = '#' + obj.current_path + '/' + cnode.name;
      $div = $('<div class="item"/>');
      $div.addClass(cnode.ico);
      $div.attr('full_path', cnode.full_path);
      $div.attr('web_path', cnode.web_path);
      $div.attr('type', cnode.type);
      $div.attr('name', cnode.name);
      $div.attr('title', cnode.name);
      $div.attr('writable', cnode.writable ? 1 : 0);
      if (!cnode.writable) {
        $div.addClass('protected');
      }
      if (cnode.type == 'folder') {
        $div.append('<a href="'+link+'" class="ico"><span class="o"></span></a>');
        folders++;
      }
      else {
        $div.append('<span class="ico"><span class="o"></span></span>');
        files++;
        intsize += cnode.intsize;
      }
      if (cnode.thumb) {
        $img = $('<img />');
        $img.attr('src', cnode.thumb.path);
        $img.attr('alt', '');
        $img.css({
          background: 'white',
          padding: ((64 - cnode.thumb.height)/2) + 'px '+ ((64 - cnode.thumb.width)/2) +'px'
        })
        $div.find('span.o').append($img);
      }
      $div.append('<span class="name">'+cnode.name+'</span>');
      if (cnode.size) {
        $div.append('<span class="size">'+cnode.size+'</span>');
      }

      $div.appendTo(obj.$browser);
    }

    obj.bindActions();
    obj.updatePath();
    obj.updateStatus(folders, files, intsize);
    obj.showLoaded();
    obj.updateToolbar();
  };

  this.resort = function (data) {
    newdata = new Array();
    for (i in data) {
      if (data[i].type == 'folder') {
        newdata[newdata.length] = data[i];
      }
    }
    for (i in data) {
      if (data[i].type != 'folder') {
        newdata[newdata.length] = data[i];
      }
    }
    return newdata;
  };

  this.encodeData = function (data) {
    data = String(data);
    if (data.indexOf('%') == -1) {
      data = encodeURI(data);
    }
    return data;
  }

  this.bindActions = function () {
    this.$browser.selectable({
      filter: 'div.item',
      delay: 1,
      selected: this.selectItem,
      unselected: this.deselectItem,
      data: { obj: this }
    });

    this.$browser.find('.item[type=folder] a.ico')
      .each(function() {
        $(this).attr('ihref', $(this).attr('href')).removeAttr('href');
      })
      .bind('dblclick', function() {
        location.href = $(this).attr('ihref');
      });

    this.$browser.find('.item[type=file] .ico')
      .bind('dblclick', {obj: this}, function(event) {
        $this = $(this).closest('.item');
        try{
          if (event.data.obj.options.tinymceinsert == 'image') {
            tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img src="'+$this.attr('web_path')+'" alt="" />');
          }
          else {
            tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<a href="'+$this.attr('web_path')+'" taget="_blank">'+$this.attr('name')+'</a>');
          }
          tinyMCEPopup.close();
        }
        catch(e){
          window.open($(this).parent().attr('web_path'));
        }
      });

    this.$browser.find('.item .ico')
      .bind('click', {obj: this}, function(event) {
        obj = event.data.obj;
        $this = $(this).parent();

        !event.ctrlKey && $this.siblings().filter('.ui-selected').each(function() {
          obj.deselectItem(event, {unselected: $(this), options: {data: {obj: obj}} });
        });

        if ($this.is('.ui-selected')) {
          obj.deselectItem(event, {unselected: $this, options: {data: {obj: obj}} });
        }
        else {
          obj.selectItem(event, {selected: $this, options: {data: {obj: obj}} });
        }
      });

    this.updateToolbar();
    
  };

  this.historyWatcher = function (obj) {
    if (typeof obj.prevurl != 'string' || obj.prevurl != String(location.href)) {
      obj.loadContent(obj.getPath());
      obj.prevurl = String(location.href);
    }
  };

  this.getPath = function () {
    return String(location.href).match(/#.+/) ? String(location.href).replace(/^.+#/, '') : '';
  };

  this.selectItem = function (event, ui) {
    if (ui) {
      $(ui.selected).addClass('ui-selected');
      ui.options.data.obj.selected[$(ui.selected).attr('name')] = true;
      ui.options.data.obj.updateToolbar();
    }
    return true;
  }
  this.deselectItem = function (event, ui) {
    if (ui) {
      $(ui.unselected).removeClass('ui-selected');
      ui.options.data.obj.selected[$(ui.unselected).attr('name')] = false;
      ui.options.data.obj.updateToolbar();
    }
    return true;
  }

  this.showLoading = function() {
    this.loading = true;
    $('#browser-overlay').remove();
    $('.indicator', this.$toolbar).show();
    $('<div/>')
    .attr('id', 'browser-overlay')
    .css({
      background: 'white',
      position: 'absolute',
      'z-index': 100,
      opacity: 0.6,
      top: this.$browser.position()['top'] + 'px',
      left: this.$browser.position()['left'] + 'px'
    })
    .width(this.$browser.width())
    .appendTo('body');

  };
  this.showLoaded = function() {
    this.loading = false;
    $('#toolbar .indicator').hide();
    $('#browser-overlay').remove();
  };

  this.initToolbar = function() {
    $('a', this.$toolbar).bind('click', {
      obj: this
    }, function(event) {
      if (obj.loading || $(this).is('.disabled')) {
        return false;
      }

      obj = event.data.obj;

      switch ($(this).attr('action')) {
        case 'back':
          window.history.back();
          break;

        case 'forward':
          window.history.forward();
          break;

        case 'reload':
          obj.loadContent(obj.current_path, true);
          break;

        case 'up':
          path = String(obj.current_path).split('/');
          delete(path[path.length - 1]);
          path = path.join('/').replace(/\/$/, '');
          window.location.href = '#' + path;
          break;

        case 'upload':
          $('.panel.upload').toggle();
          break;

        case 'create-folder':
          $.prompt(
            'Введите название для папки:<br /><input type="text" name="name" />',
            {
              buttons: {'Отменить': 'cancel', 'Создать': 'ok'},
              obj: obj,
              callback: function(button, msg) {
                name = $('[name=name]', msg).val();
                if (button == 'ok' && name) {
                  obj = this.obj;
                  obj.showLoading();
                  $.ajax({
                    url: obj.options.url + 'createfolder',
                    method: 'post',
                    data: 'path='+obj.current_path+'&name='+obj.encodeData(name)+'&format=json',
                    dataType: 'json',
                    obj: obj,
                    success: function() {
                      obj.showLoaded();
                      this.obj.loadContent(this.obj.current_path, true);
                    }
                  });
                }
              }
            }
          );
          break;

        case 'move':
        case 'copy':
          if($(this).attr('action') == 'move') move = 1;
          else move = 0;
          data =
            'nodes[]=' +
            ($.map(obj.$browser.find('.item.ui-selected'), function(el){
              return $(el).attr('full_path');
            })).join('&nodes[]=');
          obj.showLoading();
          $.ajax({
            url: obj.options.url + 'tobuffer?format=json&move=' + move,
            method: 'post',
            data: data + '&path='+obj.current_path,
            dataType: 'json',
            success: function() {
              obj.showLoaded();
              obj.loadContent(obj.current_path, true);
              obj.buffer = true;
            }
          });
          break;
        case 'paste':
          obj.showLoading();
          $.ajax({
            url: obj.options.url + 'paste?format=json',
            method: 'post',
            data: 'path='+obj.current_path,
            dataType: 'json',
            success: function() {
              obj.showLoaded();
              obj.loadContent(obj.current_path, true);
              obj.buffer = false;
            }
          });
          break;

        case 'delete':
          $.prompt(
            'Вы действительно хотите удалить выбранные файлы/папки?',
            {
              buttons: {'Отменить': 'cancel', 'Удалить': 'ok'},
              obj: obj,
              callback: function(button, msg) {
                if (button == 'ok') {
                  obj = this.obj;
                  data = 
                    'nodes[]=' +
                    ($.map(obj.$browser.find('.item.ui-selected'), function(el){
                      return $(el).attr('full_path');
                    })).join('&nodes[]=');
                  obj.showLoading();
                  $.ajax({
                    obj: obj,
                    method: 'post',
                    url: obj.options.url + 'delete?format=json',
                    data: data,
                    dataType: 'json',
                    success: function() {
                      obj.showLoaded();
                      this.obj.loadContent(this.obj.current_path, true);
                    }
                  });
                }
              }
            }
          );
          break;
      }

      return false;
    });
  };

  this.updatePath = function() {
    $ul = this.$path.find('ul');
    urls = $.map($ul.find('li'), function(el){
      return decodeURI($('a', el).attr('href').replace('#', ''));
    });

    $(':input[name=path]').val(decodeURI(this.current_path));
    
    if ($.inArray(decodeURI(this.current_path), urls) != -1) {
      $ul
        .find('a')
        .removeClass('current')
        .filter('[href=#' + decodeURI(this.current_path) + ']')
        .addClass('current');
    }
    else {
      $ul.empty();
      cpath = decodeURI(this.current_path).split('/');
      chref = '#';
      for (i in cpath) {
        chref += (i == 0 ? '' : '/') + cpath[i];
        $('<a/>')
          .attr('href', chref)
          .addClass(i == (cpath.length - 1) ? 'current' : '')
          .text(i == 0 ? '/' : cpath[i])
          .appendTo('<li/>').parent().appendTo($ul);
      }
    }
  };

  this.updateToolbar = function() {
    editbuttons = '[action=delete],[action=move]';
    addbuttons = '[action=create-folder],[action=upload]';
    copybuttons = '[action=copy]';

    if (this.current_path_info) {
      if (!this.current_path_info.writable) {
        this.$toolbar.find(editbuttons+','+addbuttons).addClass('disabled');
        $('div.panel').filter(editbuttons+','+addbuttons).hide();
      }
      else {
        this.$toolbar.find(addbuttons).removeClass('disabled');
      }

      edit_enable = false;
      copy_enabled = false;
      this.$browser.find('.item.ui-selected').each(function() {
        copy_enabled = true;
        if ($(this).attr('writable') == 1) {
          edit_enable = true;
        }
        else {
          edit_enable = false;
        }
      });

      if (edit_enable) this.$toolbar.find(editbuttons).removeClass('disabled');
      else this.$toolbar.find(editbuttons).addClass('disabled');

      if (copy_enabled) this.$toolbar.find(copybuttons).removeClass('disabled');
      else this.$toolbar.find(copybuttons).addClass('disabled');

      if (obj.buffer) this.$toolbar.find('[action=paste]').removeClass('disabled');
      else this.$toolbar.find('[action=paste]').addClass('disabled');
      // @TODO: search not implemented
      this.$toolbar.find('[action=search]').addClass('disabled');
    }
  };

  this.updateStatus = function (folders, files, intsize) {
    suffix = ['B', 'kB', 'MB', 'GB', 'TB'];
    if (intsize > 0) {
      e = parseInt(Math.log(intsize) / Math.log(1024));
      size = Number(intsize / Math.pow(1024, e)).toFixed(2) + ' ' + suffix[e];
    }
    else {
      size = '0B';
    }
    this.$status.html('Папок: '+folders+', файлов: '+files+', размер файлов: '+size);
  };

  this.initialJQ = function() {
    $([this.browser, this.toolbar, this.path])
      .bind('selectstart', function(){ return false; });

    $('.panel.upload form')
      .attr('action', this.options.url+'upload?format=xml')
      .ajaxForm({
        obj: this,
        resetForm: true,
        beforeSubmit: function(data, $form) {
          if (!$form.find(':input[name=file]').val()) {
            return false;
          }
          $form.find(':submit').attr('disabled', true);
          this.obj.showLoading();
          return true;
        },
        success: function(r, s, $form) {
          $form.find(':input').removeAttr('disabled');
          this.obj.loadContent(obj.current_path, true);
        },
        error: function(data) {
          $('.panel.upload form :input').removeAttr('disabled');
        }
      })
      .find(':input').removeAttr('disabled');
  }
}


$(function() {
  c = new Connector();
  c.init();

  $('body').ajaxError(function(event, request, settings){
     $.prompt('<span class="error">Произошла ошибка.</span><br />');
     c.showLoaded();
   });
});