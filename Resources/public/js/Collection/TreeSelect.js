(function ($) {
    $(function () {
        var $tree = $('#cmfcmfmedia-hook-collection-tree');
        $tree.jstree({
            "plugins" : [
            /*  "wholerow",*/
                "checkbox"/*,
                "search"*/
            ],
            'core': {
                'data': {
                    'url': function (node) {
                        return Routing.generate('cmfcmfmediamodule_finder_getcollections', {
                            parentId: node.id,
                            hookedObjectId: $tree.data('hooked-object-id')
                        })
                    },
                    'data': function (node) {
                        return {'id': node.id};
                    }
                }
            },
            'checkbox': {
                'three_state': false
            }
        });

        //var to = false;
        //var $searchInput = $('#cmfcmfmedia-hook-collection-tree-search');
        //$searchInput.keyup(function () {
        //    if(to) { clearTimeout(to); }
        //    to = setTimeout(function () {
        //        var v = $searchInput.val();
        //        $tree.jstree(true).search(v);
        //    }, 250);
        //});
    });
})(jQuery);
