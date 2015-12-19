(function ($) {
    window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
    window.CmfcmfMediaModule.Permission = window.CmfcmfMediaModule.Permission || {};

    window.CmfcmfMediaModule.Permission.initLevels = function ($selector) {
        $selector.find($(' .cmfcmfmedia-security-level')).each(function () {
            console.log('init');
            $(this).click(function () {
                if ($(this).prop('checked')) {
                    setRequiredLevels($(this));
                    unsetDisallowedLevels($(this));
                } else {
                    enableDisallowedLevels($(this));
                    enableRequiredLevels($(this));
                }
            });
        });
    };

    function setRequiredLevels($level) {
        var levels = $level.data('required-levels');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $('.cmfcmfmedia-security-level[data-level-id="' + levels[i] + '"]');
            $otherLevel
                .prop('checked', true)
                .attr('disabled', true);
            setRequiredLevels($otherLevel);
            unsetDisallowedLevels($otherLevel);
        }
    }

    function unsetDisallowedLevels($level) {
        var levels = $level.data('disallowed-levels');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $('.cmfcmfmedia-security-level[data-level-id="' + levels[i] + '"]');
            $otherLevel
                .prop('checked', false)
                .attr('disabled', true);
        }
    }

    function enableRequiredLevels($level) {
        var levels = $level.data('required-levels');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $('.cmfcmfmedia-security-level[data-level-id="' + levels[i] + '"]');
            $otherLevel
                .attr('disabled', false);
        }
    }

    function enableDisallowedLevels($level) {
        var levels = $level.data('disallowed-levels');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $('.cmfcmfmedia-security-level[data-level-id="' + levels[i] + '"]');
            $otherLevel
                .attr('disabled', false);
        }
    }


    $(function () {
        window.CmfcmfMediaModule.Permission.initLevels($('body'));
    })
})(jQuery);