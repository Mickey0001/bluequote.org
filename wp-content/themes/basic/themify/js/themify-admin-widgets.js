'use strict';
( function( $ ) {
	var p, e = $('#themify_news div.inside:visible').find('.widget-loading');
	if ( e.length ) {
		p = e.parent();
		setTimeout( function(){
			p.load( ajaxurl + '?action=themify_admin_widgets&widget=themify_news&pagenow=' + pagenow, '', function() {
				p.hide().slideDown('normal', function(){
					$(this).css('display', '');
				});
			});
		}, 0 );
	}

} )( jQuery );