(function ($) {
    window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
    window.CmfcmfMediaModule.Permission = window.CmfcmfMediaModule.Permission || {};

    window.CmfcmfMediaModule.Permission.initLevels = function ($selector) {
        var $accessNoneBox = $selector.find('.cmfcmfmedia-security-level[data-vertex-id="none"]');
        $selector.find('.cmfcmfmedia-security-level').not($accessNoneBox).each(function () {
            $(this).click(function () {
                if ($(this).prop('checked')) {
                    $accessNoneBox.prop('checked', false);
                    setRequiredLevels($(this), $selector);
                } else {
                    enableRequiredLevels($(this), $selector);
                }
            });
        });
        $accessNoneBox.click(function () {
            $selector.find('.cmfcmfmedia-security-level')
                .not($accessNoneBox)
                .prop('checked', false)
                .attr('disabled', false);
        });
        fixCheckboxes($selector);
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
