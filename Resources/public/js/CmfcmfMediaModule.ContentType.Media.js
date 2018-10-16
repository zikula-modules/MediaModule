'use strict';

/**
 * Loads and displays the preview for a selected medium.
 */
function getMediaPreview(id) {
    var result;

    result = '';
    if (!id) {
        return result;
    }

    jQuery.ajax({
        url: Routing.generate('cmfcmfmediamodule_media_getembeddata', {id: id}),
        method: 'GET',
        async: false
    }).done(function (data) {
        result = data.preview;
    });

    return result;
}

/**
 * Initialises the media editing.
 */
function contentInitMediaEdit() {
    var mediaSelector;

    mediaSelector = jQuery('#zikulacontentmodule_contentitem_contentData_id');
    mediaSelector.parent().append('<p class="help-block small">' + getMediaPreview(mediaSelector.val()) + '</p>');
    mediaSelector.change(function () {
        mediaSelector.parent().find('.help-block').first().html('');
        mediaSelector.parent().find('.help-block').first().html(getMediaPreview(mediaSelector.val()));
    });
}
