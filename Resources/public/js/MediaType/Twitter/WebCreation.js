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
                /*everything: Translator.trans('Everything'),*/
                tweets: Translator.trans('Tweets')/*,
                users: Translator.trans('Users')*/
            },
            Translator.trans('Search for tweets')
        );
    });
})(jQuery);
