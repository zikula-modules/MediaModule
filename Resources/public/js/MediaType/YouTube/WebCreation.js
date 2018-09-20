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
                everything: Translator.__('Everything'),
                video: Translator.__('Videos'),
                channel: Translator.__('Channels'),
                playlist: Translator.__('Playlists')
            },
            Translator.__('Search for videos, channels and playlists')
        );
    });
})(jQuery);
