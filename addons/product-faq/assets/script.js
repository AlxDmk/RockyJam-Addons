/**
 * Product FAQ Addon — script.js
 * Handles admin UI for adding/removing FAQ items.
 * Frontend accordion is handled by the template's script.js.
 */
jQuery(function($) {
    var index = $('#rja-faq-list .rja-faq-row').length;

    // Add new FAQ row
    $('#rja-add-faq').on('click', function() {
        var row = $(
            '<div class="rja-faq-row">' +
                '<div class="rja-faq-row-header">' +
                    '<span class="rja-faq-handle dashicons dashicons-menu"></span>' +
                    '<strong>FAQ Item</strong>' +
                    '<button type="button" class="rja-faq-remove button button-link-delete">Remove</button>' +
                '</div>' +
                '<div class="rja-faq-row-body">' +
                    '<label>Question</label>' +
                    '<input type="text" name="rja_faq[' + index + '][question]" class="widefat rja-faq-question-input" />' +
                    '<label>Answer</label>' +
                    '<textarea name="rja_faq[' + index + '][answer]" rows="3" class="widefat"></textarea>' +
                '</div>' +
            '</div>'
        );
        $('#rja-faq-list').append(row);
        index++;
    });

    // Remove row
    $(document).on('click', '.rja-faq-remove', function() {
        $(this).closest('.rja-faq-row').remove();
    });

    // Update header label when question changes
    $(document).on('input', '.rja-faq-question-input', function() {
        var val = $(this).val() || 'FAQ Item';
        $(this).closest('.rja-faq-row').find('.rja-faq-row-header strong').text(val);
    });
});
