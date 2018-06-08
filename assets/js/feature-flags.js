jQuery(document).ready(function ($) {

  $("input#flagActivateButton").on('click', function (e) {

    var $button = e.target;
    var featureKey = $button.parentElement.parentElement.querySelector('pre').innerHTML;

    if (featureKey) {

      $.ajax({

        type: "POST",
        url: ajaxurl,
        data: { action: 'featureFlag_enable', featureKey: featureKey }

      }).done(function (msg) {

        window.location.reload();

      }).fail(function (error) {

        $(".notice-container").html('<div class="notice notice-error is-dismissible"><p>Error cannot process <code>' + error.responseJSON.response + '</code></p></div>')

      });

    } else {

      $(".notice-container").html('<div class="notice notice-error is-dismissible"><p>Error: missing featureKey</p></div>');

    }

  });

});