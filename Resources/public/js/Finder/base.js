(function ($) {
    $(function () {
        // Based on http://stackoverflow.com/a/5926782/2560557 by user xiaohouzi79
        // http://stackoverflow.com/users/139196/xiaohouzi79

        var doneTypingInterval = 500;
        var typingTimer;
        var $searchInput = $('#cmfcmfmedia-finder-search-input');

        var $cog = $searchInput.next().find('.fa');
        $searchInput.keyup(function(){
            clearTimeout(typingTimer);
            if ($searchInput.val) {
                $cog.removeClass('hidden');
                typingTimer = setTimeout(doneTyping, doneTypingInterval);
            }
        });

        function doneTyping () {
            if ($searchInput.val().length == 0) {
                $cog.addClass('hidden');

                return;
            }

            $.getJSON(Routing.generate('cmfcmfmediamodule_finder_ajaxfind'), {q: $searchInput.val()}).success(function (results) {
                $cog.addClass('hidden');

                var $table = $('#cmfcmfmedia-finder-table-body');
                $table.empty();

                // @todo Handle collections!
                results = results.media;
                for (var i = 0; i < results.length; i++) {
                    var tr = '<tr>';

                    tr += '<td>' + window.CmfcmfMediaModule.Util.htmlEncode(results[i].type) + '</td>';
                    tr += '<td>' + window.CmfcmfMediaModule.Util.htmlEncode(results[i].title) + '</td>';
                    if (results[i].license) {
                        tr += '<td>';
                        if (results[i].license.url) {
                            tr += '<a target="_blank" href="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(results[i].license.url) + '">';
                        }
                        tr += window.CmfcmfMediaModule.Util.htmlEncode(results[i].license.title);
                        if (results[i].license.url) {
                            tr += '</a>';
                        }
                        tr += '</td>';
                    } else {
                        tr += '<td>--</td>';
                    }
                    tr += '<td><a target="_blank" href="' + window.CmfcmfMediaModule.Util.htmlAttrEncode(Routing.generate('cmfcmfmediamodule_media_display', {slug: results[i].slug, collectionSlug: results[i].collection.slug})) + '">view</a></td>';

                    tr += '<td>';

                    var event = $.Event('cmfcmfmediamodule:media:finder:getbuttons');
                    event.tableResult = '';
                    event.entity = results[i];
                    $(document).trigger(event);
                    tr += event.tableResult;

                    tr += '</td></tr>';

                    $table.append(tr);
                }
            });
        }
    })
})(jQuery);
