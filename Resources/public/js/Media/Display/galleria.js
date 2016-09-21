(function ($) {
    $(function () {
        $('.cmfcmfmedia-display-galleria').each(function () {
            Galleria.run($(this), {
                height: $(this).data('height')
            });
        });
    });
})(jQuery);
