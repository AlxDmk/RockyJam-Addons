/* Product Benefits Addon — Admin JS */
jQuery(function($) {
    var index = $('#rja-benefits-list .rja-benefit-row').length;

    $('#rja-add-benefit').on('click', function() {
        index++;
        var row = $(
            '<div class="rja-benefit-row">' +
                '<span class="rja-benefit-num">' + index + '</span>' +
                '<div class="rja-benefit-fields">' +
                    '<input type="text" name="rja_benefits[' + (index - 1) + '][title]" class="widefat" placeholder="Benefit Title" />' +
                    '<textarea name="rja_benefits[' + (index - 1) + '][text]" rows="2" class="widefat" placeholder="Benefit description..."></textarea>' +
                '</div>' +
                '<button type="button" class="rja-benefit-remove button">Remove</button>' +
            '</div>'
        );
        $('#rja-benefits-list').append(row);
    });

    $(document).on('click', '.rja-benefit-remove', function() {
        $(this).closest('.rja-benefit-row').remove();
        // Re-number remaining rows
        $('#rja-benefits-list .rja-benefit-row').each(function(i) {
            $(this).find('.rja-benefit-num').text(i + 1);
        });
    });
});
