(function ($) {
    $(function () {
        $(document).on('cmfcmfmediamodule:media:finder:getbuttons', function (event) {
            event.tableResult += '<button class="cmfcmfmedia-finder-select-btn btn btn-primary btn-sm" type="button"'
                + ' data-entity="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(JSON.stringify(event.entity)) + '">'
                + 'Select</button>'
            ;
        });

        $(document).on('click', '.cmfcmfmedia-finder-select-btn', function () {
            var entity = $(this).data('entity');
            console.log(entity);

            var $table = $('#cmfcmfmedia-media-hook-table');
            $table.find('.cmfcmfmedia-empty-msg').remove();

            var img = '';
            if (entity.thumbnail.small) {
                img = '<img alt="" style="max-width:200px;max-height:150px" src="' +
                    window.CmfcmfMediaModule.Util.htmlAttrEncode(entity.thumbnail.small) +
                    '" />';
            }

            $table.append(
                '<tr>' +
                    '<td>' +
                        img +
                    '</td>' +
                    '<td>' +
                        window.CmfcmfMediaModule.Util.htmlEncode(entity.title) +
                    '</td>' +
                    '<td>' +
                        '<button type="button" class="cmfcmfmedia-media-hook-table-remove btn btn-danger btn-sm">' +
                            '<i class="fa fa-trash-o"></i>' +
                        '</button>' +
                        '<input type="hidden" value="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(entity.id) +
                            '" name="cmfcmfmediamodule[media][]" />' +
                    '</td>' +
                '</tr>'
            );
        });
        $(document).on('click', '.cmfcmfmedia-media-hook-table-remove', function () {
            $(this).parents('tr').remove();
        })
    });
})(jQuery);
