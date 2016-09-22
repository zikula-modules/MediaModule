(function ($) {
    $(function () {
        var $grid = $('.masonry-grid');
        $grid.masonry();
        $grid.imagesLoaded().progress( function() {
            $grid.masonry('layout');
        });

        $('#cmfcmfmedia-sortable-btn').on('click', function () {
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $grid.masonry('destroy');
                $grid.find('.fa-sort').removeClass('hidden');
            } else {
                $(this).removeClass('active');
                $grid.masonry();
                $grid.find('.fa-sort').addClass('hidden');
            }
        });

        var mediaContainer = document.getElementById("cmfcmfmedia-media-sortable-container");
        if (mediaContainer != null) {
            enableSortable(mediaContainer, 'cmfcmfmediamodule_media_reorder');
        }

        var collectionContainer = document.getElementById('cmfcmfmedia-collection-sortable-container');
        if (collectionContainer != null) {
            enableSortable(collectionContainer, 'cmfcmfmediamodule_collection_reorder');
        }

        function enableSortable(container, routeName) {
            Sortable.create(container, {
                animation: 150,
                handle: ".fa-sort",
                draggable: ".sortable",
                onUpdate: function (evt) {
                    $.get(Routing.generate(routeName), {
                        id: $(evt.item).data('id'),
                        position: evt.newIndex,
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