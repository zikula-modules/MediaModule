(function ($) {
    $(function() {
        $('.cmfcmfmedia-markdown-highlight').each(function(i, block) {
            hljs.highlightBlock(block);
        });
    });
})(jQuery);
