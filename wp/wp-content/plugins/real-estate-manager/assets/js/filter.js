jQuery(document).ready(function($) {
    $('#real-estate-filter-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: realEstateAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'real_estate_ajax_filter',
                ...Object.fromEntries(new URLSearchParams(formData))
            },
            success: function(response) {
                $('#real-estate-results').html(response);
            }
        });
    });

    $(document).on('click', '.page-btn', function() {
        var page = $(this).data('page');
        var formData = $('#real-estate-filter-form').serialize();
        $.ajax({
            url: realEstateAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'real_estate_ajax_filter',
                paged: page,
                ...Object.fromEntries(new URLSearchParams(formData))
            },
            success: function(response) {
                $('#real-estate-results').html(response);
            }
        });
    });
});
