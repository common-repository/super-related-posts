var strict;

jQuery(document).ready(function ($) {
    /**
     * DEACTIVATION FEEDBACK FORM
     */
    // show overlay when clicked on "deactivate"
    suprp_deactivate_link = $('.wp-admin.plugins-php tr[data-slug="super-related-posts"] .row-actions .deactivate a');
    suprp_deactivate_link_url = suprp_deactivate_link.attr('href');

    suprp_deactivate_link.click(function (e) {
        e.preventDefault();

        // only show feedback form once per 30 days
            var c_value = suprp_admin_get_cookie("suprp_hide_deactivate_feedback");


        if (c_value === undefined) {
            $('#suprp-reloaded-feedback-overlay').show();
        } else {
            // click on the link
            window.location.href = suprp_deactivate_link_url;
        }
    });
    // show text fields
    $('#suprp-reloaded-feedback-content input[type="radio"]').click(function () {
        // show text field if there is one
        $(this).parents('li').next('li').children('input[type="text"], textarea').show();
    });
    // send form or close it
    $('#suprp-reloaded-feedback-content .button').click(function (e) {
        e.preventDefault();
        // set cookie for 30 days
        var exdate = new Date();
        exdate.setSeconds(exdate.getSeconds() + 2592000);
        document.cookie = "suprp_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";

        $('#suprp-reloaded-feedback-overlay').hide();
        if ('suprp-reloaded-feedback-submit' === this.id) {
            // Send form data
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'suprp_send_feedback',
                    data: $('#suprp-reloaded-feedback-content form').serialize()
                },
                complete: function (MLHttpRequest, textStatus, errorThrown) {
                    // deactivate the plugin and close the popup
                    $('#suprp-reloaded-feedback-overlay').remove();
                    window.location.href = suprp_deactivate_link_url;

                }
            });
        } else {
            $('#suprp-reloaded-feedback-overlay').remove();
            window.location.href = suprp_deactivate_link_url;
        }
    });
    // close form without doing anything
    $('.suprp-feedback-not-deactivate').click(function (e) {
        $('#suprp-reloaded-feedback-overlay').hide();
    });

    function suprp_admin_get_cookie (name) {
	var i, x, y, suprp_cookies = document.cookie.split( ";" );
	for (i = 0; i < suprp_cookies.length; i++)
	{
		x = suprp_cookies[i].substr( 0, suprp_cookies[i].indexOf( "=" ) );
		y = suprp_cookies[i].substr( suprp_cookies[i].indexOf( "=" ) + 1 );
		x = x.replace( /^\s+|\s+$/g, "" );
		if (x === name)
		{
			return unescape( y );
		}
	}
}

}); // document ready 