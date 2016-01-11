/**
 * javascript for inspyde search and replace plugin
 */
"use strict"

jQuery( document ).ready( function() {

	(
		function( $ ) {
			//toggles "disabled" attribute on click on #change_url checkbox on sql-export-page
			$( '#change_url' ).click( toggle_disabled_attribute );

			function toggle_disabled_attribute() {

				var d;
				d = $( '.maybe_disabled' ).attr( 'disabled' );

				if ( d == "disabled" ) {
					$( '.maybe_disabled' ).attr( 'disabled', false )
				}
				else {
					$( '.maybe_disabled' ).attr( 'disabled', true );
				}

			}

			//event listener for changes modal
			$( '#changes-modal-button' ).click( show_changes_modal );
			$( '#changes-modal-close' ).click( hide_changes_modal );

			function show_changes_modal() {
				$( '#changes-modal, #changes-modal-background' ).show();
			}

			function hide_changes_modal() {
				$( '#changes-modal, #changes-modal-background' ).hide();
			}
		}
	)( jQuery );
} );



