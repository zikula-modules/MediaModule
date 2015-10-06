(function ($) {
    // http://davidwalsh.name/fullscreen
    function launchIntoFullscreen(element) {
        if(element.requestFullscreen) {
            element.requestFullscreen();
        } else if(element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if(element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if(element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }
    function exitFullscreen() {
        if(document.exitFullscreen) {
            document.exitFullscreen();
        } else if(document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if(document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }

    $(function () {
        $('.cmfcmfmedia-fullscreen-btn').click(function () {
            var element = $(this).data('fullscreen-element');
            launchIntoFullscreen($(element)[0]);
        });
        $('.fullscreen-exit-btn').click(function () {
            exitFullscreen();
        });
    });
})(jQuery);
