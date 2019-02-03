/**
 * Beautyfort plugin Saving process
 */
jQuery(document).ready(function () {

    // Create date time
    var now = new Date();
    var dateTime = [now.getFullYear(), AddZero(now.getMonth() + 1), AddZero(now.getDate())].join("-") + "T" + [AddZero(now.getHours()), AddZero(now.getMinutes()), AddZero(now.getSeconds())].join(":") + ".000Z";

    // Set nonce field
    jQuery('#beautyfort_nonce').val(makeNonce());

    // Set date time field
    jQuery('#beautyfort_created').val(dateTime);

    // Set username field
    jQuery('#beautyfort_username').val('joetest');

    // Set secret field
    jQuery('#beautyfort_secret').val('jcRZVsWP2XdDt5iJIM0mS64hCr3f');

    jQuery(document).on('submit', '#beautyfort-admin-form', function (e) {

        e.preventDefault();

        // We inject some extra fields required for security
        jQuery(this).append('<input type="hidden" name="action" value="store_admin_data" />');
        jQuery(this).append('<input type="hidden" name="security" value="' + beautyfort_exchanger._nonce + '" />');

        console.log(jQuery(this).serialize());

        // We make our call
        jQuery.ajax({
            url: beautyfort_exchanger.ajax_url,
            type: 'post',
            data: jQuery(this).serialize(),
            success: function (response) {
                alert(response);
            }
        });
    });
});

// Generate nonce string
function makeNonce() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for (var i = 0; i < 10; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;
}

// Pad given value to the left with "0"
function AddZero(num) {
    return num >= 0 && num < 10 ? "0" + num : num + "";
}