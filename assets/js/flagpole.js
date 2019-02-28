'use strict';

/* global jQuery, ffwp */
jQuery(document).ready(function ($) {

    $('input[name^="featureFlagsBtn_"]').on('click', function (e) {

        let $button = e.target;
        let featureKey = $button.parentElement.parentElement.querySelector('code').innerHTML;
        let action = $button.getAttribute('data-action') || false;

        let payload = {
            action: action,
            featureKey: featureKey,
            security: ffwp.ajax_nonce
        };

        if (featureKey && action) {

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: payload
            }).done(function () {

                window.location.reload();
            }).fail(function (error) {

                $('.notice-container').html('<div class="notice notice-error is-dismissible"><p>Error cannot process <code>' + error.responseJSON.response + '</code></p></div>');
            });
        } else {
            $('.notice-container').html('<div class="notice notice-error is-dismissible"><p>Error: missing featureKey</p></div>');
        }
    });

});
