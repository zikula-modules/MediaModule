(function ($) {
    $(function () {
        var $slider = $('#cmfcmfmedia-display-slider');
        $slider.slick({
            autoplay: true,
            dots: $slider.children().size() <= 10,
            variableWidth: false,
            lazyLoad: 'ondemand'
        });
    });
})(jQuery);
