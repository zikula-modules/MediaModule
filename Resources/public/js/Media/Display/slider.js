(function ($) {
    $(function () {
        var $slider = $('#cmfcmfmedia-display-slider');
        $slider.slick({
            autoplay: true,
            dots: $slider.children().size() <= 10,
            slidesToShow: 1,
            centerMode: true,
            variableWidth: true,
            centerPadding: 40
        });
    });
})(jQuery);
