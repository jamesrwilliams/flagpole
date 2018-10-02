'use strict';

/* global jQuery, ffwp */
jQuery( document ).ready( function( $ ) {

	$( '#flagActivateButton' ).on( 'click', function( e ) {

		var $button    = e.target;
		var featureKey = $button.parentElement.parentElement.querySelector( 'pre' ).innerHTML;

		var payload = {
			action: 'featureFlag_enable',
			featureKey: featureKey,
			security: ffwp.ajax_nonce
		};

		if ( featureKey ) {

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: payload
			}).done( function() {

				window.location.reload();
			}).fail( function( error ) {

				$( '.notice-container' ).html( '<div class="notice notice-error is-dismissible"><p>Error cannot process <code>' + error.responseJSON.response + '</code></p></div>' );
			});
		} else {
			$( '.notice-container' ).html( '<div class="notice notice-error is-dismissible"><p>Error: missing featureKey</p></div>' );
		}
	});
});
