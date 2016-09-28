/**
 * @package  WHSuite
 * @author  WHSuite Dev Team <info@whsuite.com>
 * @copyright  Copyright (c) 2013, Turn 24 Ltd.
 * @license http://whsuite.com/license/ The WHSuite License Agreement
 * @link http://whsuite.com
 * @since  Version 1.0
 */
$(document).ready(function(e) {

    // an onload check to fill in the preview color
    var hex = $('.text-hex').val();
    if (hex.length > 0) {

        $('.color-picker div').css('backgroundColor', '#' + hex);
    }


    $('.color-picker').ColorPicker({
        color: '#27ae60',
        onShow: function (colpkr) {
            $(colpkr).fadeIn(500);
            return false;
        },
        onHide: function (colpkr) {
            $(colpkr).fadeOut(500);

            var hex = $(colpkr).find('.colorpicker_hex').children('input').val();
            $('.color-picker div').css('backgroundColor', '#' + hex);
            $('.text-hex').val(hex);

            return false;
        },
        onChange: function (hsb, hex, rgb) {
            $('.color-picker div').css('backgroundColor', '#' + hex);
            $('.text-hex').val(hex);
        },
        onBeforeShow: function () {
            var hex = $('.text-hex').val();
            $(this).ColorPickerSetColor(hex);
        }
    });

});