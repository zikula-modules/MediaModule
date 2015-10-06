(function ($) {
    $(function () {
        $('#cmfcmfmedia-hook-collection-tree').on('changed.enable_checkbox.jstree', function (e, data) {
            $('input[name="cmfcmfmediamodule[collections]"]').val(JSON.stringify(data.selected));
        }).on('changed.disable_checkbox.jstree', function (e, data) {
            $('input[name="cmfcmfmediamodule[collections]"]').val(JSON.stringify(data.selected));
        });
    });
})(jQuery);
