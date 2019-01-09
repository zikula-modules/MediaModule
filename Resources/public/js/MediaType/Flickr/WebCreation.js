(function ($) {
    $(function () {
        var availableLicenses = [];
        var applicableLicenseNames = [
            'Attribution-NonCommercial-ShareAlike License',
            'Attribution-NonCommercial License',
            'Attribution License',
            'Attribution-ShareAlike License'
        ];
        var applicableLicenseIds = [];
        // https://www.flickr.com/services/api/explore/flickr.photos.licenses.getInfo
        var url = 'https://api.flickr.com/services/rest/?method=flickr.photos.licenses.getInfo&api_key='
            + window.FLICKR_KEY + '&format=json&nojsoncallback=1';

        window.CmfcmfMediaModule.Util.Ajax.makeExternalRequest(
            url,
            function (licenses) {
                licenses = JSON.parse(licenses).licenses.license;
                for (var i = 0; i < licenses.length; i++) {
                    availableLicenses[licenses[i].id] = {
                        name: licenses[i].name,
                        url: licenses[i].url
                    };
                    if ($.inArray(licenses[i].name, applicableLicenseNames) > -1) {
                        applicableLicenseIds.push(licenses[i].id);
                    }
                }
            },
            window.CmfcmfMediaModule.Util.Ajax.fail
        );

        new window.CmfcmfMediaModule.WebCreation.WebMediaTypeBase(
            'flickr',
            [
                new window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn('Image'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn('Creator'),
                new window.CmfcmfMediaModule.WebCreation.tableColumns.UrlColumn('License')
            ],
            {
                everything: Translator.__('Everything')
            },
            Translator.__('Search for images'),
            function (searchInput, dropdownValue, onFinished) {
                var license = '';//encodeURIComponent(license);

                // https://www.flickr.com/services/api/explore/flickr.photos.search
                // https://www.flickr.com/services/api/misc.urls.html
                var url = 'https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=' + window.FLICKR_KEY + '&text=' + encodeURIComponent(searchInput) + '&license=' + applicableLicenseIds.join(',') + '&sort=relevance&extras=ownername%2Curl_h%2Curl_m%2Clicense%2Cdescription&per_page=20&format=json&nojsoncallback=1';

                window.CmfcmfMediaModule.Util.Ajax.makeExternalRequest(
                    url,
                    function(response) {
                        response = JSON.parse(response);
                        response = response.photos;

                        if (response.length == 0 || response == null) {
                            // Nothing found! Sorry!
                            onFinished({
                                'more': 0,
                                'results': []
                            });
                            return;
                        }
                        var results = [];
                        for (var i = 0; i < response.photo.length; i++) {
                            var result = response.photo[i];

                            results.push([
                                {
                                    'flickrId': result.id,
                                    'flickrSecret': result.secret,
                                    'flickrServer': result.server,
                                    'flickrFarm': result.farm,
                                    'title': searchInput,
                                    'url': 'https://www.flickr.com/photos/' + result.owner + '/' + result.id,
                                    'license': availableLicenses[result.license].name
                                },
                                result.url_m,
                                result.ownername,
                                {
                                    text: availableLicenses[result.license].name,
                                    url:  availableLicenses[result.license].url
                                }
                            ]);
                        }
                        var more = 0;
                        if (response.page != response.pages) {
                            more = response.total - response.page * response.perpage;
                        }
                        onFinished({
                            'more': more,
                            'results': results
                        });
                    },
                    window.CmfcmfMediaModule.Util.Ajax.fail
                );
            }
        );
    });
})(jQuery);
