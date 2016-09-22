(function ($) {
    $(function () {
        new window.CmfcmfMediaModule.WebCreation.WebMediaTypeBase(
            'youTube',
            [
                new window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn('Thumbnail'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn('Type'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn('Channel'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn('Title')
            ],
            {
                everything: 'Everything',
                video: 'Videos',
                channel: 'Channels',
                playlist: 'Playlists'
            },
            'Search for videos, channels and playlists'
        );
    });
})(jQuery);
