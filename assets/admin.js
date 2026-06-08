/**
 * RockyJam Addons — Admin Scripts
 */
( function ( $ ) {
	'use strict';

	var $modal = $( '#rj-modal-create' );

	// ---- Open / close modal ----
	$( '#rj-open-create' ).on( 'click', function () {
		$modal.fadeIn( 150 );
		$( '#new_addon_id' ).trigger( 'focus' );
	} );

	$( document ).on( 'click', '.rj-modal-close', function () {
		$modal.fadeOut( 150 );
	} );

	$( document ).on( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			$modal.fadeOut( 150 );
		}
	} );

	// ---- Auto-fill ID from Name ----
	$( '#new_addon_name' ).on( 'input', function () {
		var $idField = $( '#new_addon_id' );
		// Only auto-fill if the user hasn't manually changed the ID field.
		if ( $idField.data( 'manual' ) ) {
			return;
		}
		var slug = $( this ).val()
			.toLowerCase()
			.trim()
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-+|-+$/g, '' );
		$idField.val( slug );
	} );

	$( '#new_addon_id' ).on( 'input', function () {
		$( this ).data( 'manual', $( this ).val().length > 0 );
	} );

	// ---- Icon live preview ----
	$( '#new_addon_icon' ).on( 'input', function () {
		var val   = $( this ).val().trim();
		var $prev = $( '#rj-icon-preview' );
		$prev.attr( 'class', 'dashicons ' + val );
	} );

	// ---- Delete confirmation ----
	$( document ).on( 'click', '.rj-btn-delete', function () {
		var addonId = $( this ).data( 'addon' );
		var nonce   = $( this ).data( 'nonce' );

		if ( ! window.confirm( window.RockyJamAdmin.confirmDelete ) ) {
			return;
		}

		$( '#rj-delete-addon-id' ).val( addonId );
		$( '#rj-delete-nonce' ).val( nonce );
		$( '#rj-delete-form' ).trigger( 'submit' );
	} );

	// ---- Optimistic card dim on toggle ----
	$( document ).on( 'change', '.rj-toggle__input', function () {
		var $card = $( this ).closest( '.rj-card' );
		if ( $( this ).is( ':checked' ) ) {
			$card.removeClass( 'rj-card--inactive' ).addClass( 'rj-card--active' );
		} else {
			$card.removeClass( 'rj-card--active' ).addClass( 'rj-card--inactive' );
		}
	} );

} )( jQuery );
