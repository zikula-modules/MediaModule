(function ($) {
    $(function () {
        new window.CmfcmfMediaModule.WebCreation.WebMediaTypeBase(
            'deezer',
            [
                new window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn('Cover'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn('Type'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn('Result')
            ],
            {
                everything: Translator.__('Everything'),
                track: Translator.__('Tracks'),
                album: Translator.__('Albums'),
                artist: Translator.__('Artists'),
                playlist: Translator.__('Playlists')
            },
            Translator.__('Search for artist, track, playlist or album'),
            function (searchInput, dropdownValue, onFinished) {
                var query = '/search';
                if (dropdownValue != 'everything') {
                    query += '/' + encodeURIComponent(dropdownValue);
                }
                query += '?q=' + encodeURIComponent(searchInput);

                DZ.api(query, function(response) {
                    if (response.total == 0) {
                        onFinished({
                            'more': 0,
                            'results': []
                        });
                        return;
                    }
                    var results = [];
                    for (var i = 0; i < response.data.length; i++) {
                        var result = response.data[i];
                        var title = '';
                        switch (result.type) {
                            case 'track':
                                title = result.title;
                                results.push([
                                    result.album.cover_small,
                                    Translator.__('Track'),
                                    title + "\n" + Translator.__('Artist') + ': ' + result.artist.name + "\n" + Translator.__('Album') + ': ' + result.album.title
                                ]);
                                break;
                            case 'album':
                                title = result.title;
                                results.push([
                                    result.cover_small,
                                    Translator.__('Album'),
                                    title + "\n" + result.artist.name
                                ]);
                                break;
                            case 'artist':
                                title = result.name;
                                results.push([
                                    result.picture_small,
                                    Translator.__('Artist'),
                                    name + "\n" + Translator.__('Fans') + ': ' + result.nb_fan
                                ]);
                                break;
                            case 'playlist':
                                title = result.title;
                                results.push([
                                    result.picture_small,
                                    Translator.__('Playlist'),
                                    title + "\n" + Translator.__('Creator') + ': ' + result.user.name + "\n" + Translator.__('Tracks') + ': ' + result.nb_tracks
                                ]);
                                break;
                            default:
                                continue;
                        }
                        results[results.length - 1].unshift({
                            'musicId': result.id,
                            'musicType': result.type,
                            'title': title,
                            'url': result.link
                        });
                    }

                    onFinished({
                        results: results,
                        more: response.next ? (response.next.length > 0 ? response.total - response.data.length : 0) : false
                    });
                });
            }
        );
    });
})(jQuery);
