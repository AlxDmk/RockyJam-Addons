/**
 * Key Features — Admin script
 * Handles adding / removing rows and drag-to-reorder in the meta-box.
 */
( function ( $ ) {
	'use strict';

	$( document ).ready( function () {
		const list    = $( '#rjt-kf-list' );
		const addBtn  = $( '#rjt-kf-add-btn' );
		const empty   = $( '#rjt-kf-empty' );

		// ── Sortable (jQuery UI) ────────────────────────────────────────────
		list.sortable( {
			handle:      '.rjt-kf-list__handle',
			placeholder: 'rjt-kf-list__item sortable-ghost',
			axis:        'y',
			tolerance:   'pointer',
		} );

		// ── Add row ─────────────────────────────────────────────────────────
		addBtn.on( 'click', function () {
			const item = buildRow( '' );
			list.append( item );
			item.find( '.rjt-kf-list__input' ).trigger( 'focus' );
			empty.hide();
		} );

		// ── Remove row (delegated) ───────────────────────────────────────────
		list.on( 'click', '.rjt-kf-list__remove', function () {
			$( this ).closest( '.rjt-kf-list__item' ).remove();
			if ( list.children( '.rjt-kf-list__item' ).length === 0 ) {
				empty.show();
			}
		} );

		// ── Allow pressing Enter to add a new row ────────────────────────────
		list.on( 'keydown', '.rjt-kf-list__input', function ( e ) {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				addBtn.trigger( 'click' );
			}
		} );

		// ── Helper: build a new list item ────────────────────────────────────
		function buildRow( value ) {
			const li = $( '<li class="rjt-kf-list__item"></li>' );

			const handle = $( '<span></span>' )
				.addClass( 'rjt-kf-list__handle dashicons dashicons-menu' )
				.attr( 'title', 'Drag to reorder' );

			const input = $( '<input type="text" />' )
				.attr( 'name', 'rockyjam_key_features[]' )
				.attr( 'placeholder', 'Feature description\u2026' )
				.addClass( 'rjt-kf-list__input regular-text' )
				.val( value );

			const removeBtn = $( '<button type="button"></button>' )
				.addClass( 'button rjt-kf-list__remove' )
				.attr( 'title', 'Remove' )
				.append( $( '<span></span>' ).addClass( 'dashicons dashicons-no-alt' ) );

			li.append( handle, input, removeBtn );
			return li;
		}
	} );

} )( jQuery );
