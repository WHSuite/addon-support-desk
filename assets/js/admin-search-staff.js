$(document).ready(function() {

    /**
     * on key up, check type and search admins
     */
    $('#searchStaff').bind('keyup', function(e) {

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
        $('#dataSupportTicketStaffId').empty();
        var empty_select = $('#dataSupportTicketStaffId').data('empty-label');
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
                    $('#dataSupportTicketStaffId').html(options);
                }
            });

        } else {

            // add to drop down
            $('#dataSupportTicketStaffId').html(options);
        }
    });

});