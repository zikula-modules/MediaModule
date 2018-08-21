(function ($) {
    $(function () {
        var isPopup = $('#cmfcmfmedia-is-popup').val();
        $('#cmfcmfmedia-paste-btn-parse').click(function () {
            var $button = $(this);
            var $fa = $button.find('> i.fa');
            var $input = $('#cmfcmfmedia-paste-text');
            var pastedText = $input.val();
            var $form = $button.parent();

            // Add big spinner and also spin the FA icon of the button.
            $input.parent().spin();
            $input.attr('readonly', true);
            $button.attr('disabled', true);
            $fa.addClass('fa-spin');

            $.ajax({
                url: Routing.generate('cmfcmfmediamodule_media_matchespaste'),
                data: {pastedText: pastedText},
                method: 'POST'
            }).done(function (mediaTypes) {
                if (mediaTypes.length == 0) {
                    window.toastr['warning'](
                        "It seems like it isn't supported (yet) or simply an unknown way of embedding. " +
                        "Please consider to open an issue.",
                        "Pasted url or embed code could not be parsed."
                    );

                    $input.parent().spin(false);
                    $input.attr('readonly', false);
                    $button.attr('disabled', false);
                    $fa.removeClass('fa-spin');
                    return;
                }
                /*
                window.CmfcmfMediaModule.Media.New.showAjaxForm(mediaTypes[0].alias, '', function (formData) {});
                */
                var params = {
                    type: 'paste',
                    mediaType: mediaTypes[0].alias
                };
                if (isPopup) {
                    params.popup = true;
                }
                $form.attr('action', Routing.generate('cmfcmfmediamodule_media_create', params));
                $form.submit();
            }).fail(window.CmfcmfMediaModule.Util.Ajax.fail).fail(function () {
                $input.attr('readonly', false);
                $button.attr('disabled', false);
                $fa.removeClass('fa-spin');
            }).always(function () {
                $input.parent().spin(false);
            });
        });
    });
})(jQuery);
