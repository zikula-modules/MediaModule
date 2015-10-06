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
                everything: 'Everything',
                track: 'Tracks',
                album: 'Albums',
                artist: 'Artists',
                playlist: 'Playlists'
            },
            'Search for artist, track, playlist or album',
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
                                    'Track',
                                    title + "\nArtist: " + result.artist.name + "\nAlbum: " + result.album.title
                                ]);
                                break;
                            case 'album':
                                title = result.title;
                                results.push([
                                    result.cover_small,
                                    'Album',
                                    title + "\n" + result.artist.name
                                ]);
                                break;
                            case 'artist':
                                title = result.name;
                                results.push([
                                    result.picture_small,
                                    'Artist',
                                    name + "\nFans: " + result.nb_fan
                                ]);
                                break;
                            case 'playlist':
                                title = result.title;
                                results.push([
                                    result.picture_small,
                                    'Playlist',
                                    title + "\nCreator: " + result.user.name + "\nTracks: " + result.nb_tracks
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
