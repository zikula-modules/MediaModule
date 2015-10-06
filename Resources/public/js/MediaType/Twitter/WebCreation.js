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
                /*everything: 'Everything',*/
                tweets: 'Tweets'/*,
                users: 'Users'*/
            },
            'Search for tweets and users'
        );
    });
})(jQuery);
