/**
 * javascript for inpsyde search and replace plugin
 */
"use strict";

jQuery( document ).ready( function() {

	(
		function( $ ) {
			//search-replace-tab:  greys out export/save to db option when dry run is selected
			$( '#dry_run' ).click( toggle_disabled_attribute );

			//replace-domain-tab: greys out replace db_prefix option
			$ ('#change_db_prefix' ).click (toggle_disabled_attribute);

			function toggle_disabled_attribute() {

				var grayed_out_areas = $( '.maybe_disabled' );
				grayed_out_areas.toggleClass( 'disabled' );
				var inputs = (
					$( '.maybe_disabled input' )
				);

				if ( inputs.attr( 'disabled' ) ) {
					inputs.removeAttr( 'disabled' );
				}

				else {
					inputs.attr( 'disabled', true );
				}

			}

			//click on checkbox selects all tables in table select on search and replace tab
			var table_select_checkbox = $( '#select_all_tables' );
			table_select_checkbox.change( toggle_select_all_tables );

			function toggle_select_all_tables( e ) {
				console.log( e );
				if ( table_select_checkbox.is( ':checked' ) ) {
					$( '#select_tables option' ).attr( 'selected', true );
				}
				else {
					$( '#select_tables option' ).attr( 'selected', false );
				}

			}

			//event listener for changes modal
			$( '#changes-modal-button' ).click( show_changes_modal );
			$( '#changes-modal-close' ).click( hide_changes_modal );

			function show_changes_modal() {
				$( '#changes-modal, #changes-modal-background' ).show();
				//add listener for close with "esc" - key
				$( document ).bind( 'keydown', keydown_event_handler );
			}

			function hide_changes_modal() {
				$( '#changes-modal, #changes-modal-background' ).hide();
			}

			function keydown_event_handler( e ) {
				if ( e.keyCode === 27 ) {
					hide_changes_modal();
					$( document ).unbind( 'keydown', keydown_event_handler )
				}
			}
		}
	)( jQuery );

} );