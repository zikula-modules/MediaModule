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
                tracks: Translator.__('Tracks'),
                groups: Translator.__('Groups'),
                users: Translator.__('Artists'),
                playlists: Translator.__('Playlists')
            },
            Translator.__('Search for artist, track, playlist'),
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
                                    Translator.__('User'),
                                    title + "\n" + Translator.__('Followers') + ': ' + result.followers_count + "\n" + Translator.__('Tracks') + ': ' + result.track_count
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
                                    Translator.__('Track'),
                                    title + "\n" + Translator.__('License') + ': ' + result.license
                                ]);
                                break;
                            case 'group':
                                title = result.name;
                                results.push([
                                    result.artwork_url,
                                    Translator.__('Group'),
                                    title + "\n" + Translator.__('Description') + ': ' + result.short_description
                                ]);
                                break;
                            case 'playlist':
                                title = result.title;
                                results.push([
                                    result.picture_small,
                                    Translator.__('Playlist'),
                                    title + "\n" + Translator.__('Duration') + ': ' + (result.duration / 60) + ' ' + Translator.__('min')
                                        + "\n" + Translator.__('Tracks') + ': ' + result.tracks.length
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
