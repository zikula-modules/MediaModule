(function ($) {
    $(function () {
        new window.CmfcmfMediaModule.WebCreation.WebMediaTypeBase(
            'soundCloud',
            [
                new window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn('Cover'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn('Type'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn('Result')
            ],
            {
                tracks: 'Tracks',
                groups: 'Groups',
                users: 'Artists',
                playlists: 'Playlists'
            },
            'Search for artist, track, playlist',
            function (searchInput, dropdownValue, onFinished) {
                var query = '/' + encodeURIComponent(dropdownValue) + '?q=' + encodeURIComponent(searchInput);
                
                SC.get(query, {}, function(response) {
                    if (response.length == 0 || response == null) {
                        onFinished({
                            'more': 0,
                            'results': []
                        });
                        return;
                    }
                    var results = [];
                    for (var i = 0; i < response.length; i++) {
                        var result = response[i];
                        var type = result.kind;
                        var title = '';
                        switch (type) {
                            case 'user':
                                title = result.full_name;
                                results.push([
                                    result.avatar_url,
                                    'User',
                                    title + "\nFollowers: " + result.followers_count + "\nTracks: " + result.track_count
                                ]);
                                break;
                            case 'track':
                                title = result.title;
                                var image;
                                if (result.artwork_url != null) {
                                    image = result.artwork_url;
                                } else {
                                    image = result.user.avatar_url;
                                }
                                results.push([
                                    image,
                                    'Track',
                                    title + "\nLicense: " + result.license
                                ]);
                                break;
                            case 'group':
                                title = result.name;
                                results.push([
                                    result.artwork_url,
                                    'Group',
                                    title + "\nDescription: " + result.short_description
                                ]);
                                break;
                            case 'playlist':
                                title = result.title;
                                results.push([
                                    result.picture_small,
                                    'Playlist',
                                    title + "\nDuration: " + (result.duration / 60) + ' min'
                                    + "\nTracks: " + result.tracks.length
                                ]);
                                break;
                            default:
                                continue;
                        }
                        results[results.length - 1].unshift({
                            'musicId': result.id,
                            'musicType': result.type,
                            'title': title,
                            'url': result.permalink_url,
                            'license': type == 'track' ? result.license : ''
                        });
                    }

                    onFinished({
                        results: results,
                        more: 0
                    });
                });
            }
        );
    });
})(jQuery);
