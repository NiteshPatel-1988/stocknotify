/* global jQuery, stocknotifyPublic */
( function ( $ ) {
	'use strict';

	/**
	 * Show or hide a subscription form and keep its variation id in sync.
	 *
	 * @param {jQuery} $wrap       Form wrapper element.
	 * @param {boolean} show       Whether the form should be visible.
	 * @param {number} variationId Variation id to store on the form, or 0.
	 */
	function toggleForm( $wrap, show, variationId ) {
		if ( ! $wrap.length ) {
			return;
		}

		$wrap.find( '.stocknotify-variation-id' ).val( variationId || 0 );
		$wrap.toggleClass( 'stocknotify-hidden', ! show );
	}

	$( document ).on( 'found_variation', '.variations_form', function ( event, variation ) {
		var $wrap = $( this ).closest( '.summary' ).find( '.stocknotify-form-wrap' );
		toggleForm( $wrap, ! variation.is_in_stock, variation.variation_id );
	} );

	$( document ).on( 'reset_data', '.variations_form', function () {
		var $wrap = $( this ).closest( '.summary' ).find( '.stocknotify-form-wrap' );
		toggleForm( $wrap, false, 0 );
	} );

	$( document ).on( 'submit', '.stocknotify-form', function ( event ) {
		event.preventDefault();

		var $form = $( this );
		var $message = $form.find( '.stocknotify-message' );
		var $button = $form.find( '.stocknotify-submit' );
		var email = $form.find( '.stocknotify-email' ).val();
		var originalLabel = $button.text();

		$message.removeClass( 'stocknotify-success stocknotify-error' ).text( '' );

		if ( ! email ) {
			$message.addClass( 'stocknotify-error' ).text( stocknotifyPublic.i18n.emailRequired );
			return;
		}

		$button.prop( 'disabled', true ).text( stocknotifyPublic.i18n.submitting );

		$.post( stocknotifyPublic.ajaxUrl, {
			action: 'stocknotify_subscribe',
			nonce: $form.find( 'input[name="stocknotify_nonce"]' ).val(),
			product_id: $form.find( '.stocknotify-product-id' ).val(),
			variation_id: $form.find( '.stocknotify-variation-id' ).val(),
			email: email
		} ).done( function ( response ) {
			if ( response && response.success ) {
				$message.addClass( 'stocknotify-success' ).text( response.data.message );
				$form.find( '.stocknotify-email' ).val( '' );
			} else {
				var errorMessage = ( response && response.data && response.data.message ) || stocknotifyPublic.i18n.genericError;
				$message.addClass( 'stocknotify-error' ).text( errorMessage );
			}
		} ).fail( function () {
			$message.addClass( 'stocknotify-error' ).text( stocknotifyPublic.i18n.genericError );
		} ).always( function () {
			$button.prop( 'disabled', false ).text( originalLabel );
		} );
	} );
} )( jQuery );
