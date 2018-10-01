jQuery(document).ready(function ($) {

    $("input#flagActivateButton").on('click', function (e) {

        let $button = e.target;
        let featureKey = $button.parentElement.parentElement.querySelector('pre').innerHTML;

        if (featureKey) {

            $.ajax({

                type: "POST",
                url: ajaxurl,
                data: {action: 'featureFlagEnable', featureKey: featureKey}

            }).done(function () {

                window.location.reload();

            }).fail(function (error) {

                $(".notice-container").html('<div class="notice notice-error is-dismissible"><p>Error cannot process <code>' + error.responseJSON.response + '</code></p></div>')

            });

        } else {
            $(".notice-container").html('<div class="notice notice-error is-dismissible"><p>Error: missing featureKey</p></div>');
        }

    });

});
