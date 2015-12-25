(function ($) {
    function executeStep(step) {
        var steps = $('#cmfcmfmedia-settings-upgrade-start-btn').data('steps');
        if (steps == step) {
            window.location.href = Routing.generate('cmfcmfmediamodule_settings_settings');
            return;
        }

        var $stepsList = $('#cmfcmfmedia-settings-upgrade-steps-list');
        var $li = $stepsList.find('li:nth-child(' + (step + 1) + ')');
        var $icon = $li.find('.fa');

        $li.removeClass('text-muted').addClass('text-primary');
        $icon.addClass('fa-spin');

        $.ajax(Routing.generate('cmfcmfmediamodule_upgrade_ajax', {step: $li.data('step')}), {
            timeout: 60000
        })
            .fail(function () {
                $li.addClass('text-danger');
            })
            .done(function (result) {
                if (result.proceed) {
                    $li.addClass('text-success');
                    executeStep(step + 1);
                } else {
                    $li.addClass('text-danger');
                    alert(result.message);
                }
            })
            .always(function () {
                $icon.removeClass('fa-spin');
            })
        ;
    }

    $(function () {
        $('#cmfcmfmedia-settings-upgrade-start-btn').one('click', function () {
            $(this).attr('disabled', true);
            $(this).find('.fa').removeClass('hidden');
            executeStep(0);
        });
    });
})(jQuery);
