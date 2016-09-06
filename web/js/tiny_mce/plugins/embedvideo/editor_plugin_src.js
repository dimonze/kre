
(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('embedvideo');

	tinymce.create('tinymce.plugins.EmbedVideo', {

		init : function(ed, url) {
			ed.addCommand('mceEmbedVideo', function() {
				ed.windowManager.open({
					file : url + '/dialog.htm',
					width : 480 + parseInt(ed.getLang('embedvideo.delta_width', 0)),
					height : 320 + parseInt(ed.getLang('embedvideo.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register example button
			ed.addButton('embedvideo', {
			    title: 'embedvideo.desc',
				cmd : 'mceEmbedVideo',
				image : url + '/img/embed.jpg'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
			    cm.setActive('embedvideo', n.nodeName == 'IMG');
			});
		},

		createControl : function(n, cm) {
			return null;
		},

		getInfo : function() {
			return {
				longname : 'Embed Video Plugin',
				author : 'agent 007',
				authorurl : 'eldar',
				infourl: 'eldar',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('embedvideo', tinymce.plugins.EmbedVideo);
})();