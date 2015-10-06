(function ($) {
    $(function () {
        var container = document.getElementById("cmfcmfmedia-media-sortable-container");
        var sort = Sortable.create(container, {
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
    });
})(jQuery);
