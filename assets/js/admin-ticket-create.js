/**
 *
 * @package  WHSuite
 * @author  WHSuite Dev Team <info@whsuite.com>
 * @copyright  Copyright (c) 2013, Turn 24 Ltd.
 * @license http://whsuite.com/license/ The WHSuite License Agreement
 * @link http://whsuite.com
 * @since  Version 1.0
 * @todo   Refactor this into a plugin?
 */

$(document).ready(function() {

    /**
     * On keyup of the client search, check what we are searching on and get matching clients
     *
     */
    $('#dataClientSearch').bind('keyup', function(e) {

        var search_term = $(this).val();
        var search_url = $(this).data('search-url');

        // check for id
        if (parseInt(search_term) && search_term > 0) {

            search_term = parseInt(search_term);
            var isId = true;
            var type = 'id';

        } else {

            var isId = false;
            var type = 'name-email';
        }

        // replace the placeholders in the url with the correct values
        search_url = search_url.replace('TYPE', type);
        search_url = search_url.replace('SEARCH', search_term);

        // start to setup the new options
        $('#dataSupportTicketClientId').empty();
        var empty_select = $('#dataSupportTicketClientId').data('empty-label');
        var options = '<option>' + empty_select + '</option>';

        if (isId || (! isId && search_term.length >= 2)) {

            $.ajax({
                'url': search_url,
                'cache': false,
                success: function(data, textStatus, jqXHR) {

                    // add all the options
                    for (var i in data) {

                        options += '<option value="' + i + '">' + data[i] + '</option>';
                    }
                },
                complete: function(jqXHR, textStatus) {

                    // add to drop down on complete
                    $('#dataSupportTicketClientId').html(options);
                }
            });

        } else {

            // add to drop down
            $('#dataSupportTicketClientId').html(options);
        }
    });

    /**
     * after populate the clients drop down. On selecting client
     * search and get all their products so we can assign ticket to a service
     *
     */
    $('#dataSupportTicketClientId').bind('change', function(e) {

        // get the selected client and then start processing the options
        var client_id = $(this).val();
        var products = $('#dataSupportTicketProductPurchaseId');

        products.empty();
        var empty_select = products.data('empty-label');
        var options = '<option>' + empty_select + '</option>';

        // check for id
        if (parseInt(client_id) && client_id > 0) {

            // get the search url and replace the placeholder
            var search_url = products.data('search-url');
            search_url = search_url.replace('CLIENTID', client_id);

            $.ajax({
                'url': search_url,
                'cache': false,
                success: function(data, textStatus, jqXHR) {

                    for (var i in data) {

                        options += '<option value="' + i + '">' + data[i] + '</option>';
                    }
                },
                complete: function(jqXHR, textStatus) {

                    products.html(options);
                }
            });

        } else {

            products.html(options);
        }
    });

});