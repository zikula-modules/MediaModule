(function ($) {
    window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
    window.CmfcmfMediaModule.Permission = window.CmfcmfMediaModule.Permission || {};

    window.CmfcmfMediaModule.Permission.initLevels = function ($selector) {
        $selector.find('.cmfcmfmedia-security-level').each(function () {
            $(this).click(function () {
                if ($(this).prop('checked')) {
                    setRequiredLevels($(this), $selector);
                    unsetConflictingLevels($(this), $selector);
                } else {
                    enableRequiredLevels($(this), $selector);
                }
                fixCheckboxes($selector);
            });
        });
        fixCheckboxes($selector);
    };

    function setRequiredLevels($level, $selector) {
        var levels = $level.data('required-vertices');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $selector.find('.cmfcmfmedia-security-level[data-vertex-id="' + levels[i] + '"]');
            $otherLevel.prop('checked', true).attr('disabled', true);
            setRequiredLevels($otherLevel, $selector);
        }
    }

    function enableRequiredLevels($level, $selector) {
        var levels = $level.data('required-vertices');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $selector.find('.cmfcmfmedia-security-level[data-vertex-id="' + levels[i] + '"]');
            $otherLevel.attr('disabled', false);
        }
    }

    function unsetConflictingLevels($level, $selector) {
        var levels = $level.data('conflicting-vertices');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $selector.find('.cmfcmfmedia-security-level[data-vertex-id="' + levels[i] + '"]');
            $otherLevel.prop('checked', false);
            enableRequiredLevels($otherLevel, $selector);
        }
    }

    function fixCheckboxes($selector) {
        $selector.find('.cmfcmfmedia-security-level').each(function () {
            if ($(this).prop('checked')) {
                setRequiredLevels($(this), $selector);
            }
        });
    }

    $(function () {
        window.CmfcmfMediaModule.Permission.initLevels($('body'));
    })
})(jQuery);
