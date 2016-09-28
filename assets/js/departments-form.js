/**
 * @package  WHSuite
 * @author  WHSuite Dev Team <info@whsuite.com>
 * @copyright  Copyright (c) 2013, Turn 24 Ltd.
 * @license http://whsuite.com/license/ The WHSuite License Agreement
 * @link http://whsuite.com
 * @since  Version 1.0
 */
$(document).ready(function(e) {

    if ($('#dataSupportDepartmentPiping').length > 0) {

        //$('#dataSupportDepartmentPiping').bind('click', function(e) {
        $('input.piping-toggle').on('switchChange.bootstrapSwitch', function(e, s) {

            if ($(this).is(':checked') && $('.piping-settings').css('display') == 'none') {

                $('.piping-settings').slideDown();

            } else if (! $(this).is(':checked') && $('.piping-settings').css('display') != 'none') {

                $('.piping-settings').slideUp();
            }
        });
    }

});