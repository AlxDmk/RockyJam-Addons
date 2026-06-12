(function($){
    'use strict';

    function getNextIndex($tbody) {
        var max = -1;
        $tbody.find('tr').not('.rja-shipping-badge-template').each(function(){
            var name = $(this).find('input[name^="rja_shipping_badges["]').attr('name');
            if (!name) return;
            var match = name.match(/rja_shipping_badges\[(\d+)\]/);
            if (match && match[1]) {
                var idx = parseInt(match[1], 10);
                if (idx > max) max = idx;
            }
        });
        return max + 1;
    }

    $(document).on('click', '.rja-shipping-badge-add', function(e){
        e.preventDefault();
        var $box   = $(this).closest('.rja-shipping-badge-admin');
        var $tbody = $box.find('.rja-shipping-badge-rows');
        var $tpl   = $tbody.find('.rja-shipping-badge-template');
        var index  = getNextIndex($tbody);

        var html = $tpl.prop('outerHTML')
            .replace(/__index__/g, index)
            .replace('style="display:none;"', '');

        var $row = $(html);
        $row.removeClass('rja-shipping-badge-template');
        $tbody.append($row);
    });

    $(document).on('click', '.rja-sb-remove', function(e){
        e.preventDefault();
        var $row = $(this).closest('tr');
        var $tbody = $row.closest('tbody');
        $row.remove();

        // Ensure at least one empty row so UI does not vanish completely.
        if ($tbody.find('tr').not('.rja-shipping-badge-template').length === 0) {
            $('.rja-shipping-badge-add').trigger('click');
        }
    });

    $(function(){
        $('.rja-shipping-badge-rows').sortable({
            handle: '.rja-sb-handle',
            items: '> tr:not(.rja-shipping-badge-template)',
            axis: 'y',
            helper: function(e, ui){
                ui.children().each(function(){
                    $(this).width($(this).width());
                });
                return ui;
            }
        });
    });

})(jQuery);
