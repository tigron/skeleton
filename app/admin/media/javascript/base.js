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
});

