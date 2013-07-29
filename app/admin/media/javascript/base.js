$(document).ready(function(){
	$('.wysiwyg').wysiwyg();

	var lightboxes = new Array();
	$("a.lightbox[rel]").each(function() {
		lightboxes.push("a[rel='"+$(this).attr('rel')+"']");
	});

	lightboxes = $.unique(lightboxes);
	for (var i = 0; i < lightboxes.length; i++) {
		$(lightboxes[i]).colorbox({
			maxWidth: '80%',
			maxHeight: '80%',
			photo: true,
		});
	}

	$('a.lightbox').not(':has[rel]').colorbox({
		maxWidth: '80%',
		maxHeight: '80%',
		photo: true,
	});

	init_dialogs();
});


function init_dialogs() {

	$.each($('.macro_link'), function(i) {
			$(this).bind('click', function() {
				id = $(this).attr('id').replace('trigger_', '');
				$('#form_' + id).submit();
				return false;
			});
		});

	var dialogs = {};

	$.each($('.macro_confirm, .macro_modal'), function(i) {
		var id = $(this).attr('id').replace('trigger_', '');

		if ($(this).hasClass('macro_confirm')) {

			dialogs[id] = $('#confirm_' + id).dialog({
				resizable: false,
				autoOpen: false,
				minWidth: 600,
				modal: true,
				title: "Confirm",
				buttons: {
					"Confirm": function() {
						if ($(this).hasClass('ajax')) {
							var callback = $('#form_' + id + ' input[name="callback_function"]').val();
							$('#form_' + id).ajaxSubmit({url: $('#form_' + id).attr('action'), type: 'get'});
							$( this ).dialog('close');
							if (callback != '') {
								$.globalEval(callback);
							}
						} else {
							$('#form_' + id).submit();
						}
					},
					"Cancel": function() {
						$( this ).dialog('close');
					},
				},
			});

			$(this).bind('click', function() {
				dialogs[id].dialog('open');
				return false;
			});

		} else if($(this).hasClass('macro_modal')) {

			dialogs[id] = $("#modal_" + id).dialog({
				resizable: false,
				autoOpen: false,
				minWidth: 600,
				modal: true
			});

			if (!$(this).hasClass('no_buttons')) {

				dialogs[id].dialog('option', 'buttons', [
					{
						text: "Close",
						click: function() {
							$( this ).dialog( "close" );
						}
					}]
				);
			}

			$(this).click(function() {
				dialogs[id].dialog('open');
				return false;
			});
		}
	});
}
