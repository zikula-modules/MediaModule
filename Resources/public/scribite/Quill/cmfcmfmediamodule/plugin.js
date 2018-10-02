var cmfcmfmediamodule = function(quill, options) {
    setTimeout(function() {
        var button;

        button = jQuery('button[value=cmfcmfmediamodule]');

        button
            .css('background', 'url(' + Zikula.Config.baseURL + Zikula.Config.baseURI + '/web/modules/cmfcmfmedia/images/admin.png) no-repeat center center transparent')
            .css('background-size', '16px 16px')
            .attr('title', 'Media')
        ;

        button.click(function() {
            CmfcmfMediaModuleFinderOpenPopup(quill, 'quill');
        });
    }, 1000);
};
