;/**
 * Off-Canvas Sidebars plugin settings
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.4.2
 * @global ocsOffCanvasSidebarsSettings
 * @preserve
 */

if ( 'undefined' === typeof ocsOffCanvasSidebarsSettings ) {
	var ocsOffCanvasSidebarsSettings = {
		general_key: 'off_canvas_sidebars_options',
		plugin_key: 'off-canvas-sidebars-settings',
		css_prefix: 'ocs',
		__required_fields_not_set: 'Some required fields are not set!'
	};
}

( function( $ ) {

	var $document = $(document);
	ocsOffCanvasSidebarsSettings.init = function() {

		var tab = $('#ocs_tab');
		var postbox = $('.postbox');

		// Close postboxes that should be closed.
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// Postboxes setup.
		postboxes.add_postbox_toggles( ocsOffCanvasSidebarsSettings.plugin_key );


		if ( 'ocs-sidebars' === tab.val() ) {
			postbox.each( function() {
				var prefix = 'off_canvas_sidebars_options_sidebars_',
					sidebar_id = $(this).attr('id').replace('section_sidebar_', ''),
					sidebar_prefix = prefix + sidebar_id;

				ocs_show_hide_options_radio(
					'.' + sidebar_prefix + '_background_color_type',
					'.' + sidebar_prefix + '_background_color_wrapper',
					'color',
					false
				);
				ocs_show_hide_options_radio(
					'.' + sidebar_prefix + '_location',
					'#' + sidebar_prefix + '_style_reveal, #' + sidebar_prefix + '_style_shift',
					[ 'left', 'right' ],
					'label'
				);

				ocs_show_hide_options( '.' + sidebar_prefix + '_overwrite_global_settings', '.' + sidebar_prefix + '_site_close', 'tr' );
				ocs_show_hide_options( '.' + sidebar_prefix + '_overwrite_global_settings', '.' + sidebar_prefix + '_disable_over', 'tr' );
				ocs_show_hide_options( '.' + sidebar_prefix + '_overwrite_global_settings', '.' + sidebar_prefix + '_hide_control_classes', 'tr' );
				ocs_show_hide_options( '.' + sidebar_prefix + '_overwrite_global_settings', '.' + sidebar_prefix + '_scroll_lock', 'tr' );
			} );
		} else {
			ocs_show_hide_options_radio(
				'.off_canvas_sidebars_options_background_color_type',
				'.off_canvas_sidebars_options_background_color_wrapper',
				'color',
				false
			);
		}

		/**
		 * Auto show/hide handler for checkbox elements.
		 * @todo Rename?
		 * @param  {string}               trigger  The trigger element selector.
		 * @param  {string}               target   The target element selector.
		 * @param  {string|boolean|null}  parent   The parent element selector.
		 * @return {null}  Nothing.
		 */
		function ocs_show_hide_options( trigger, target, parent ) {
			if ( parent ) {
				target = $( target ).closest( parent );
			}
			if ( ! $( trigger ).is(':checked') ) {
				$( target ).slideUp('fast');
			}
			$( trigger ).change( function() {
				if ( $(this).is(':checked') ) {
					$( target ).slideDown('fast');
				} else {
					$( target ).slideUp('fast');
				}
			} );
		}

		/**
		 * Auto show/hide handler for radio elements.
		 * @param  {string}               trigger  The trigger element selector.
		 * @param  {string}               target   The target element selector.
		 * @param  {string|object}        compare  The compare value.
		 * @param  {string|boolean|null}  parent   The parent element selector.
		 * @return {null}  Nothing.
		 */
		function ocs_show_hide_options_radio( trigger, target, compare, parent ) {
			if ( ! $.isArray( compare ) ) {
				compare = [ compare ];
			}
			if ( parent ) {
				parent += ', ' + parent + ' + br';
				target = $( target ).closest( parent );
			}
			$( trigger ).change( function() {
				if ( 0 <= $.inArray( $( trigger + ':checked' ).val(), compare ) ) {
					$( target ).slideDown('fast');
				} else {
					$( target ).slideUp('fast');
				}
			} ).trigger( 'change' );
		}

		// Enable the WP Color Picker.
		$('input.color-picker').wpColorPicker();

		// Validate required fields.
		$('input.required').each( function() {
			var $this = $(this);
			$this.on( 'change', function() {
				if ( ! $this.val() ) {
					$this.parents('tr').addClass('form-invalid');
				} else {
					$this.parents('tr').removeClass('form-invalid');
				}
			} );
		} );

		// Validate form submit.
		$( '#' + ocsOffCanvasSidebarsSettings.general_key ).submit( function( e ) {
			var valid = true;
			//var errors = {};
			$( 'input.required', this ).each( function() {
				if ( ! $(this).val() ) {
					$(this).trigger('change');
					valid = false;
				}
			} );
			if ( ! valid ) {
				e.preventDefault();
				alert( ocsOffCanvasSidebarsSettings.__required_fields_not_set );
			}
		} );

		if ( 'ocs-sidebars' === tab.val() ) {

			// Dynamic sidebar ID.
			if ( $('.js-dynamic-id').length ) {
				postbox.each( function() {
					var sidebar = this;
					$( '.js-dynamic-id', sidebar ).text( $( 'input.off_canvas_sidebars_options_sidebars_id', sidebar ).val() );
					$('.sidebar_classes').show();
					$( 'input.off_canvas_sidebars_options_sidebars_id', this ).on('keyup', function() {
						$( '.js-dynamic-id', sidebar ).text( $(this).val() );
					} );
				} );
			}

			// Half opacity for closed disabled sidebars.
			// @todo Use classes instead of CSS.
			postbox.each( function() {
				var sidebar = this,
					$sidebar = $( sidebar );

				$sidebar.css({'border-left':'5px solid #eee'});
				if ( ! $( 'input.off_canvas_sidebars_options_sidebars_enable', sidebar ).is(':checked') ) {
					if ( $sidebar.hasClass('closed') ) {
						$sidebar.css('opacity', '0.75');
					}
					$sidebar.css('border-left-color','#ffb900');
				} else {
					$sidebar.css('border-left-color','#46b450');
				}
				$( 'input.off_canvas_sidebars_options_sidebars_enable', sidebar ).on( 'change', function() {
					if ( ! $(this).is(':checked') ) {
						$sidebar.css('border-left-color','#ffb900');
						if ( $sidebar.hasClass('closed') ) {
							$sidebar.css('opacity', '0.75');
						} else {
							$sidebar.css('opacity', '');
						}
					} else {
						$sidebar.css('border-left-color','#46b450');
						$sidebar.css('opacity', '');
					}
				} );
				$sidebar.on('click', function() {
					if ( ! $( 'input.off_canvas_sidebars_options_sidebars_enable', sidebar ).is(':checked') && $sidebar.hasClass('closed') ) {
						$sidebar.css('opacity', '0.75');
					} else {
						$sidebar.css('opacity', '');
					}
				} );
			} );

			// Hide options when set to delete.
			$document.on( 'change', '.off_canvas_sidebars_options_sidebars_delete', function() {
				var $this = $(this),
					sidebar = $this.parents('.postbox'),
					$sidebar = $( sidebar );

				if ( $this.is(':checked') ) {
					var parent_row = $this.parents('tr');
					$( 'tr', sidebar ).hide( 'fast', function() {
						$( 'tr', sidebar ).each(function(){
							if ( $(this).is( parent_row ) ) {
								$(this).show( 'fast' );
							}
						});
					} );
					$sidebar.css('opacity', '0.5');
					$sidebar.css('border-left-color','#dc3232');
				} else {
					$sidebar.css('opacity', '');
					$( 'tr', sidebar ).show( 'fast' );
					$('input.off_canvas_sidebars_options_sidebars_enable', sidebar).trigger('change');
				}
			} );

		}

		if ( 'ocs-shortcode' === tab.val() ) {

			var fields = [ 'id', 'text', 'action', 'element', 'class', 'attr', 'nested' ];

			for ( var i = 0, l = fields.length; i < l; i++ ) {
				$( '#off_canvas_sidebars_options_' + fields[ i ] ).on( 'change keyup', function() {
					create_shortcode();
				});
			}

		}

		/**
		 * Formats the data to a shortcode.
		 * @since  0.4
		 * @return {null}  Nothing.
		 */
		function create_shortcode() {
			var field_data = {};
			for ( var i = 0, l = fields.length; i < l; i++ ) {
				field_data[ fields[ i ] ] = $( '#off_canvas_sidebars_options_' + fields[ i ] );
			}

			var shortcode = 'ocs_trigger';

			// Start the shortcode tag.
			var shortcode_str = '[' + shortcode;

			// Loop through our known fields.
			for ( var field in field_data ) {
				if ( 'undefined' !== typeof field_data[ field ] ) {
					if ( 'text' !== field && 'nested' !== field ) {
						if ( field_data[ field ].is(':checked') ) {
							shortcode_str += ' ' + field + '="1"';
						} else if ( field_data[ field ].val().length ) {
							shortcode_str += ' ' + field + '="' + field_data[ field ].val().replace( /(\r\n|\n|\r)/gm, '' ) + '"';
						}
					}
				}
			}

			// If the test contains a double quote, force it to be nested for compatibility.
			if ( field_data.text.val().length && -1 < field_data.text.val().indexOf( '"' ) ) {
				field_data.nested = true;
			}

			// Add panel text.
			if ( field_data.nested.is(':checked') ) {
				shortcode_str += ']' + field_data.text.val() + '[/' + shortcode + ']';
			} else {
				if ( field_data.text.val().length ) {
					shortcode_str += ' text="' + field_data.text.val() + '"';
				}
				shortcode_str += ']';
			}

			$('textarea#ocs_shortcode').val( shortcode_str );

			create_shortcode_preview( field_data );
		}

		/**
		 * Parses the shortcode data for a HTML preview.
		 * @since  0.4
		 * @param  {object}  field_data  The shortcode data.
		 * @return {null}  Nothing.
		 */
		function create_shortcode_preview( field_data ) {

			var element = ( field_data.element.val() ) ? field_data.element.val() : 'button',
				attributes = ( field_data.attr.val() ) ? attrStringToObject( field_data.attr.val() ) : {},
				prefix = ocsOffCanvasSidebarsSettings.css_prefix,
				action = ( field_data.action.val() ) ? field_data.action.val() : 'toggle',
				classes = prefix + '-trigger ' + prefix + '-' + action,
				singleton = false,
				html = '';

			if ( field_data.id.val() ) {
				classes += ' ' + prefix + '-' + action + '-' + field_data.id.val();
			}
			if ( field_data.class.val() ) {
				classes += ' ' + field_data.class.val();
			}
			if ( attributes.class ) {
				classes += ' ' + attributes.class;
			}
			attributes.class = classes;

			if ( 'input' === element || 'img' === element ) {
				singleton = true;
				attributes.value = field_data.text.val();
			}

			if ( singleton ) {
				html = '<' + element + ' ' + attrObjectToHTML( attributes ) + '>';
			} else {
				html = '<' + element + ' ' + attrObjectToHTML( attributes ) + '>' + field_data.text.val() + '</' + element + '>';
			}

			$( '#ocs_shortcode_preview' ).html( html );

			$( '#ocs_shortcode_html' ).val( html );

		}

		/**
		 * Convert HTML formatted attribute string to object.
		 * In: key="value" key="value"
		 * Out: { key: value, key: value }
		 *
		 * @since  0.4
		 * @param  {string}  attrString  The attribute string.
		 * @return {object}  The attribute object.
		 */
		function attrHTMLToObject( attrString ) {
			var arr = attrString.trim().split( '" ' ),
				atts = {};
			for ( var key in arr ) {
				if ( arr.hasOwnProperty( key ) ) {
					arr[ key ] = arr[ key ].split( '="' );
					if ( arr[ key ][ 0 ].trim().length ) {
						atts[ arr[ key ][ 0 ].trim() ] = getAttr( attrString, arr[ key ][ 0 ], false );
					}
				}
			}
			return atts;
		}

		/**
		 * Convert OCS formatted attribute string to object.
		 *
		 * In: key:value;key:value
		 * Out: { key: value, key: value }
		 *
		 * @since  0.4
		 * @param  {string}  attrString  The attribute string.
		 * @return {object}  The attribute object.
		 */
		function attrStringToObject( attrString ) {
			var arr = attrString.split( ';' ),
				atts = {};
			for ( var key in arr ) {
				if ( arr.hasOwnProperty( key ) ) {
					arr[ key ] = arr[ key ].split( ':' );
					if ( arr[ key ][ 0 ].trim().length ) {
						var name = arr[ key ][ 0 ].trim();
						arr[ key ].splice( 0, 1 );
						atts[ name ] = arr[ key ].join( ':' );
					}
				}
			}
			return atts;
		}

		/**
		 * Convert object to OCS formatted attribute string.
		 *
		 * In: { key: value, key: value }
		 * Out: key="value" key="value"
		 *
		 * @since  0.4
		 * @param  {object}  attrObj  The attribute object.
		 * @return {string}  The attribute string.
		 */
		function attrObjectToHTML( attrObj ) {
			var atts = [];
			for ( var name in attrObj ) {
				if ( attrObj.hasOwnProperty( name ) ) {
					atts.push( name + '="' + attrObj[ name ] + '"' );
				}
			}
			return atts.join( ' ' );
		}

		/**
		 * Convert object to HTML formatted attribute string.
		 *
		 * In: { key: value, key: value }
		 * Out: key:value;key:value
		 *
		 * @since  0.4
		 * @param  {object}  attrObj  The attribute object.
		 * @return {string}  The attribute string.
		 */
		function attrObjectToString( attrObj ) {
			var atts = [];
			for ( var name in attrObj ) {
				if ( attrObj.hasOwnProperty( name ) ) {
					atts.push( name + ':' + attrObj[ name ] );
				}
			}
			return atts.join( ';' );
		}

		/**
		 * @since  0.4
		 * @param  {string}   s  The string.
		 * @param  {string}   a  The attribute to find.
		 * @param  {boolean}  f  @todo
		 * @return {string|boolean}  The attribute value or false.
		 */
		function getAttr( s, a, f ) {
			var n = new RegExp( a + '=\"([^\"]+)\"', 'g' ).exec( s );
			if ( true === f && !n && -1 === s.indexOf( a + '="' ) ) {
				// Attribute does not exist
				return false;
			}
			return n ? window.decodeURIComponent( n[ 1 ] ).trim() : '';
		}

	};

	ocsOffCanvasSidebarsSettings.init();

} ( jQuery ) );
