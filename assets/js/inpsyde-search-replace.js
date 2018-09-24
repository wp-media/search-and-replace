/**
 * javascript for inpsyde search and replace plugin
 */
"use strict";

;(function ( $ ) {

	window.addEventListener( 'load', function () {

		function toggle_disabled_attribute() {

			var grayed_out_areas = $( '.maybe_disabled' );
			grayed_out_areas.toggleClass( 'disabled' );

			var inputs = $( '.maybe_disabled input' );

			if ( inputs.attr( 'disabled' ) ) {
				inputs.removeAttr( 'disabled' );
			} else {
				inputs.attr( 'disabled', true );
			}
		}

		function toggle_select_all_tables( e ) {

			if ( table_select_checkbox.is( ':checked' ) ) {
				$( '#select_tables option' ).attr( 'selected', true );
			} else {
				$( '#select_tables option' ).attr( 'selected', false );
			}
		}

		function show_changes_modal() {

			$( '#changes-modal, #changes-modal-background' ).show();
			$(document.body).toggleClass('search-replace-modal-open');

			//add listener for close with "esc" - key
			$( document ).bind( 'keydown', keydown_event_handler );
		}

		function hide_changes_modal() {

			$( '#changes-modal, #changes-modal-background' ).hide();
			$(document.body).toggleClass('search-replace-modal-open');
		}

		function keydown_event_handler( e ) {

			// ESC
			if ( e.keyCode === 27 ) {
				hide_changes_modal();
				$( document ).unbind( 'keydown', keydown_event_handler )
			}
		}

		//search-replace-tab:  greys out export/save to db option when dry run is selected
		$( '#dry_run' ).click( toggle_disabled_attribute );

		//replace-domain-tab: greys out replace db_prefix option
		$( '#change_db_prefix' ).click( toggle_disabled_attribute );

		//click on checkbox selects all tables in table select on search and replace tab
		var table_select_checkbox = $( '#select_all_tables' );
		table_select_checkbox.change( toggle_select_all_tables );

		// GZ compression only available for exports
		$( '#radio2' ).click( function () {
			$( '#compress' ).attr( 'checked', false );
			$( ' #compress' ).attr( 'disabled', true );
		} );

		$( '#radio1' ).click( function () {
			$( ' #compress' ).removeAttr( 'disabled' );
		} );

		//event listener for changes modal
		$( '#changes-modal-button' ).click( show_changes_modal );
		$( '#changes-modal-close' ).click( hide_changes_modal );

		// Trying to search for site URL will give warning
		// But only if it is not an email address
		$( '#search-submit' ).click( function () {
			if ( $( '#radio2' ).is( ':checked' )
			     && $( '#search' ).val().indexOf( insr_data_obj.site_url ) != - 1
			     && $( '#search' ).val().indexOf( '@' ) == - 1 ) {
				return confirm( insr_data_obj.search_matches_site_url );
			}
			return true;
		} );

		// Auto resize textarea search field if the content is multilines
		var search_textarea = $( 'textarea#search' );
		search_textarea.on('input change drop keydown cut paste', function() {
  			search_textarea.height('auto');
			search_textarea.height(search_textarea.prop('scrollHeight'));
		}).trigger('input');

	} );

}( window.jQuery ));
