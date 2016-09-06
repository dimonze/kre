var narrow_window_width = 1200;
var narrow_class_name = 'page-narrow';

var ie7 = false;
if ($.browser.msie) {
  ie7 = (parseInt($.browser.version, 10) <= 7);
}
$(function() {
	change_layout();
	$(window).resize(change_layout);
	
	$(".upd-iwant-select-list li:odd").addClass("upd-select-list-odd");
	$(".upd-iwant-select-list li:last").addClass("upd-select-list-last").wrapInner('<div class="upd-select-list-last-wrap" />');
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
			$("input", select_obj).val(parseInt($(this).attr("id").split('_')[1]));
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
		
	$(".upd-cat-gallery-control").click(function() {
		$(this).next(".upd-cat-gallery-more").slideDown('fast');
		$(this).remove();
		return false;
	});
});

function change_layout() {
	if ($(window).width() <= narrow_window_width) {
		$("body").addClass(narrow_class_name);
	} else {
		$("body").removeClass(narrow_class_name);
	}
}

var hideMe = function(){
	$('#hDesc p').filter(':last').addClass("noBottomMargin");
	if ($('#hDesc').outerHeight() > 196){
		$('#hDesc').css('height','180px');
		$('#hDescHide').show();
	} else {
		$('#hDesc').css('height','auto');
		$('#hDescHide').attr('style', 'display:none;');
		$('#hDesc p').filter(':last').removeClass("noBottomMargin");
	}
}
var showMe = function(){
	$('#hDescHide').click(function(){
		$('#hDesc').css('height','auto');
		$('#hDescHide').attr('style', 'display:none;');
		$('#hDesc p').filter(':last').removeClass("noBottomMargin");
		return false;
	})
}
	
$(document).ready(function(){
	hideMe();
	showMe();
})