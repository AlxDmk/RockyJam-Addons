/* Product Badges Addon — Admin JS */
jQuery(function($) {
    var index = $('#rja-badges-list .rja-badge-row').length;

    $('#rja-add-badge').on('click', function() {
        var row = $('<div class="rja-badge-row">' +
            '<input type="text" name="rja_badges[' + index + '][title]" placeholder="Badge Title (e.g. 🔒 Warranty)" class="rja-badge-title" />' +
            '<input type="text" name="rja_badges[' + index + '][text]" placeholder="Badge Text (e.g. 3-year limited warranty)" class="rja-badge-text" />' +
            '<button type="button" class="rja-badge-remove button">Remove</button>' +
            '</div>');
        $('#rja-badges-list').append(row);
        index++;
    });

    $(document).on('click', '.rja-badge-remove', function() {
        $(this).closest('.rja-badge-row').remove();
    });
});
