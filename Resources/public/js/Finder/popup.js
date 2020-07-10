(function ($) {
    $(function () {
        $(document).on('cmfcmfmediamodule:media:finder:getbuttons', function (event) {
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(event.entity.embedCodes.full) + '">' +
                Translator.trans('Full size') + '</button>'
            ;
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(event.entity.embedCodes.medium) + '">' +
                Translator.trans('Medium size') + '</button>'
            ;
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(event.entity.embedCodes.small) + '">' +
                Translator.trans('Small size') + '</button>'
            ;
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button" ' +
                'data-embedcode="' + window.CmfcmfMediaModule.Util.htmlAttrEncode('<a href="' + Routing.generate('cmfcmfmediamodule_media_download', {'slug': event.entity.slug, 'collectionSlug': event.entity.collection.slug}) + '">' + event.entity.title + '</a>') + '">' +
                Translator.trans('Link') + '</button>'
            ;
        });

        $(document).on('click', '.cmfcmfmedia-finder-select-btn', function () {
            var html = $(this).data('embedcode');
            switch (window.opener.CmfcmfMediaModule.currentEditor) {
                case 'ckeditor':
                    window.opener.CmfcmfMediaModule.currentEditorInstance.insertHtml(html);
                    break;
                case 'quill':
                    window.opener.CmfcmfMediaModule.currentEditorInstance.clipboard.dangerouslyPasteHTML(window.opener.CmfcmfMediaModule.currentEditorInstance.getLength(), html);
                    break;
                case 'summernote':
                    html = jQuery(html).get(0);
                    window.opener.CmfcmfMediaModule.currentEditorInstance.invoke('insertNode', html);
                    break;
                case 'tinymce':
                    window.opener.CmfcmfMediaModule.currentEditorInstance.insertContent(html);
                    break;
            }
            window.opener.toastr['success'](Translator.trans('The medium has been successfully inserted.'), Translator.trans('Medium inserted'));
            window.opener.focus();
            window.close();
        });
    });
})(jQuery);
