(function ($) {
    $(function () {
        var mediaContainer = document.getElementById("cmfcmfmedia-media-sortable-container");
        Sortable.create(mediaContainer, {
            animation: 150,
            handle: ".fa-sort",
            draggable: ".sortable",
            onUpdate: function (evt) {
                $.get(Routing.generate('cmfcmfmediamodule_media_reorder'), {
                    id: $(evt.item).data('id'),
                    position: evt.newIndex
                }).success(function () {
                    window.toastr['success']('', 'Saved new position.');
                }).fail(window.CmfcmfMediaModule.Util.Ajax.fail);
            }
        });
        var collectionContainer = document.getElementById('cmfcmfmedia-collection-sortable-container');
        if (collectionContainer != null) {
            Sortable.create(collectionContainer, {
                animation: 150,
                handle: ".fa-sort",
                draggable: ".sortable",
                onUpdate: function (evt) {
                    $.get(Routing.generate('cmfcmfmediamodule_collection_reorder'), {
                        id: $(evt.item).data('id'),
                        'new-position': evt.newIndex,
                        'old-position': evt.oldIndex
                    }).success(function () {
                        window.toastr['success']('', 'Saved new position.');
                    }).fail(window.CmfcmfMediaModule.Util.Ajax.fail);
                }
            });
        }
    });
})(jQuery);
