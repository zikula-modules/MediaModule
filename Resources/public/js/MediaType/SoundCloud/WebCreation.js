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
                tracks: Translator.trans('Tracks'),
                groups: Translator.trans('Groups'),
                users: Translator.trans('Artists'),
                playlists: Translator.trans('Playlists')
            },
            Translator.trans('Search for artist, track, playlist'),
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
                                    Translator.trans('User'),
                                    title + "\n" + Translator.trans('Followers') + ': ' + result.followers_count + "\n" + Translator.trans('Tracks') + ': ' + result.track_count
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
                                    Translator.trans('Track'),
                                    title + "\n" + Translator.trans('License') + ': ' + result.license
                                ]);
                                break;
                            case 'group':
                                title = result.name;
                                results.push([
                                    result.artwork_url,
                                    Translator.trans('Group'),
                                    title + "\n" + Translator.trans('Description') + ': ' + result.short_description
                                ]);
                                break;
                            case 'playlist':
                                title = result.title;
                                results.push([
                                    result.picture_small,
                                    Translator.trans('Playlist'),
                                    title + "\n" + Translator.trans('Duration') + ': ' + (result.duration / 60) + ' ' + Translator.trans('min')
                                        + "\n" + Translator.trans('Tracks') + ': ' + result.tracks.length
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
