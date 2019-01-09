(function ($) {
    $(function () {
        new window.CmfcmfMediaModule.WebCreation.WebMediaTypeBase(
            'twitter',
            [
                new window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn('Avatar'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn('User'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn('Type'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn('')
            ],
            {
                /*everything: Translator.__('Everything'),*/
                tweets: Translator.__('Tweets')/*,
                users: Translator.__('Users')*/
            },
            Translator.__('Search for tweets')
        );
    });
})(jQuery);
