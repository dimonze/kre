(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('filemanager');

	tinymce.create('tinymce.plugins.FilemanagerPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

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

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceFilemanagerImages', function() {
				ed.windowManager.open({
					file : app + '/filemanager/index?iframe=1&type=image&insert=image',
					width : 660,
					height : 500,
					inline : 1,
          type: 'images'
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			ed.addCommand('mceFilemanagerFiles', function() {
				ed.windowManager.open({
					file : app + '/filemanager/index?iframe=1&insert=file',
					width : 660,
					height : 500,
					inline : 1,
          type: 'files'
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

      ed.addButton('filemanager_files', {
				title : 'filemanager.files',
				cmd : 'mceFilemanagerFiles',
				image : url + '/img/insertfile.gif'
			});

      ed.addButton('filemanager_images', {
				title : 'filemanager.images',
				cmd : 'mceFilemanagerImages',
				image : url + '/img/insertimage.gif'
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Filemanager plugin',
				author : 'Eugeniy Belyaev',
				authorurl : 'http://garin-studio.ru/',
				infourl : '',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('filemanager', tinymce.plugins.FilemanagerPlugin);
})();
