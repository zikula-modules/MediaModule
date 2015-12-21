(function ($) {
    window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
    window.CmfcmfMediaModule.Permission = window.CmfcmfMediaModule.Permission || {};

    window.CmfcmfMediaModule.Permission.initLevels = function ($selector) {
        $selector.find('.cmfcmfmedia-security-level').each(function () {
            $(this).click(function () {
                if ($(this).prop('checked')) {
                    setRequiredLevels($(this), $selector);
                } else {
                    enableRequiredLevels($(this), $selector);
                }
            });
        });
        $selector.find('#cmfcmfmedia-security-level-no-access').click(function () {
            $selector.find('.cmfcmfmedia-security-level')
                .prop('checked', false)
                .attr('disabled', false);
            $(this).prop('checked', false);
        })
    };

    function setRequiredLevels($level, $selector) {
        var levels = $level.data('children-vertices');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $selector.find('.cmfcmfmedia-security-level[data-vertex-id="' + levels[i] + '"]');
            $otherLevel
                .prop('checked', true)
                .attr('disabled', true);
            setRequiredLevels($otherLevel, $selector);
        }
    }

    function enableRequiredLevels($level, $selector) {
        var levels = $level.data('children-vertices');
        for (var i = 0; i < levels.length; i++) {
            var $otherLevel = $selector.find('.cmfcmfmedia-security-level[data-vertex-id="' + levels[i] + '"]');
            $otherLevel
                .attr('disabled', false);
        }
        fixCheckboxes($selector);
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