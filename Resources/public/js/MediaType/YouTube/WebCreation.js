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
                everything: Translator.trans('Everything'),
                video: Translator.trans('Videos'),
                channel: Translator.trans('Channels'),
                playlist: Translator.trans('Playlists')
            },
            Translator.trans('Search for videos, channels and playlists')
        );
    });
})(jQuery);
