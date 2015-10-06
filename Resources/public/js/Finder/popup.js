(function ($) {
    $(function () {
        $(document).on('cmfcmfmediamodule:media:finder:getbuttons', function (event) {
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(event.entity.embedCodes.full) + '">' +
                'Full size</button>'
            ;
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(event.entity.embedCodes.medium) + '">' +
                'Medium size</button>'
            ;
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(event.entity.embedCodes.small) + '">' +
                'Small size</button>'
            ;
        });

        $(document).on('click', '.cmfcmfmedia-finder-select-btn', function () {
            var html = $(this).data('embedcode');
            switch (window.opener.CmfcmfMediaModule.currentEditor) {
                case 'ckeditor':
                    window.opener.CmfcmfMediaModule.currentEditorInstance.insertHtml(html);
                    break;
            }
            window.opener.toastr['success']('The media object has been successfully inserted.', 'Media object inserted');
            window.opener.focus();
            window.close();
        });
    });
})(jQuery);
