/*globals window, document, $, jQuery, _, Backbone */
(function ($, _, Backbone) {
	"use strict";
	var media = wp.media,
		builderContent = '',
		placeholder = '<!--themify_builder_static--><!--/themify_builder_static-->',
		patterns = [/<!--themify_builder_static-->([\s\S]*?)<!--\/themify_builder_static-->/gi, /&lt;!--themify_builder_static--&gt;([\s\S]*?)&lt;!--\/themify_builder_static--&gt;/gi, /&amp;lt;!--themify_builder_static--&amp;gt;([\s\S]*?)&amp;lt;!--\/themify_builder_static--&amp;gt;/gi];

	wp.mce.views.register( 'themify-builder-static-badge', {
		template: media.template( 'themify-builder-static-badge' ),
		bindNode: function( editor, node ) {
			$(node).on('click', '.themify-builder-mce-view-frontend-btn', this.goToFront)
			.on('click', '.themify-builder-mce-view-backend-btn', this.goToBack);
		},
		getContent: function() {
			return this.template({});
		},
		match: function( content ) {
			var match = wp.mce.views._tb_static_content.isMatch( content );
			if ( match ) {
				return {
					index: match.index,
					content: this.contentPlaceholder( match[0] ),
					options: {}
				};
			}
		},
		View: {
			className: 'themify-builder-static-badge',
			template: media.template( 'themify-builder-static-badge' ),
			getHtml: function() {
				return this.template({});
			}
		},
		edit: function( node ) {
			this.goToFront();
		},
		goToFront: function(){
			$( '#themify_builder_switch_frontend' ).trigger( 'click' );
		},
		goToBack: function() { 
			themifybuilderapp._backendBuilderFocus();
		},
		contentPlaceholder: function( content ) {
			builderContent = builderContent || content;

			return placeholder + ( content.length > placeholder.length
				? ' '.repeat( content.length - placeholder.length ) : '' );
		}
	} );

	wp.mce.views._tb_static_content = {
		setContent: function( editor, content ) {
			if( tinyMCE && tinyMCE.activeEditor ) {
				if( tinyMCE.activeEditor.hidden ) {
					$('#content').val(content);
				} else {
					editor.setContent( content );
				}
			} else {
				editor.val(content);
			}
		},
		isMatch: function( content ) {
			return patterns[0].exec( content ) || patterns[1].exec( content ) || patterns[2].exec( content );
		}
	};

	$(document).on('tinymce-editor-init', function( event, editor ) {
		if (editor.wp && editor.wp._createToolbar) {
			var toolbar = editor.wp._createToolbar([
				'wp_view_edit'
			]);
		}

		if (toolbar) {
			//this creates the toolbar
			editor.on('wptoolbar', function (event) {
				if (editor.dom.hasClass(event.element, 'wpview') && 'themify-builder-static-badge' === editor.dom.getAttrib( event.element, 'data-wpview-type')) {
					event.toolbar = toolbar;
				}
			});
		}

		editor.setContent( wp.mce.views.setMarkers( editor.getContent() ) );

		editor.on('beforesetcontent', function( event ) {
			event.content = wp.mce.views.setMarkers( event.content );
		});
	});

	$('body').on('themify_builder_save_data', function( event, jqxhr, textStatus ){
		var editor;

		if ( _.isEmpty( jqxhr.data.static_content ) ) return true;

		if( tinyMCE && tinyMCE.activeEditor ) {
			editor = tinyMCE.activeEditor;
			var content = false === tinyMCE.activeEditor.hidden ? tinyMCE.activeEditor.getContent() : tinymce.DOM.get('content').value,
				match = wp.mce.views._tb_static_content.isMatch( content );
		} else {
			editor = $('#content');
			var content = editor.val(),
				match = wp.mce.views._tb_static_content.isMatch( content );
		}

		if ( _.isNull( match ) ) {
			wp.mce.views._tb_static_content.setContent( editor, content + jqxhr.data.static_content );
		} else {
			wp.mce.views._tb_static_content.setContent( editor, content.replace( match[0], jqxhr.data.static_content ) );
		}
	});

	// YOAST SEO
	var yoastReadBuilder = {
		timeout: undefined,
		// Initialize
		init: function () {
			if ( 'undefined' !== typeof YoastSEO ) {
				addEventListener( 'load', yoastReadBuilder.load );
			}
		},

		// Load plugin and add hooks.
		load: function () {
			YoastSEO.app.registerPlugin( 'TBuilderReader', {status: 'loading'} );

			YoastSEO.app.pluginReady( 'TBuilderReader' );
			YoastSEO.app.registerModification( 'content', yoastReadBuilder.readContent, 'TBuilderReader', 5 );

			// Make the Yoast SEO analyzer works for existing content when page loads.
			yoastReadBuilder.update();
		},

		// Read content to Yoast SEO Analyzer.
		readContent: function ( content ) {
			if( builderContent ) {
				content = content.replace( placeholder, builderContent ).replace( /(\r\n|\n|\r)/gm, '' );
			}

			return content;
		},

		// Update the YoastSEO result. Use debounce technique, which triggers only when keys stop being pressed.
		update: function () {
			clearTimeout( yoastReadBuilder.timeout );
			yoastReadBuilder.timeout = setTimeout( function () {
				YoastSEO.app.refresh();
			}, 250 );
		},
	};
	// Run on document ready.
	$( yoastReadBuilder.init );

}(jQuery, _, Backbone));