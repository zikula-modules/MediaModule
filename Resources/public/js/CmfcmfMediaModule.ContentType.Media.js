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
 * Initialises the media edit view.
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

/**
 * Initialises the media translation view.
 */
function contentInitMediaTranslation() {
    jQuery('.source-section .field-id').each(function (index) {
        var fieldContainer;

        fieldContainer = jQuery(this).find('.form-control-static');
        zikulacontentmodule_translate_contentData_id
        fieldContainer.append('<p class="help-block small">' + getMediaPreview(fieldContainer.text()) + '</p>');
    });
    jQuery('#contentTranslateTarget .tab-content').find("select[id$='_id']").each(function (index) {
        var mediaSelector;

        mediaSelector = jQuery(this);
        mediaSelector.parents('.col-sm-9').first().append('<p class="help-block small">' + getMediaPreview(mediaSelector.val()) + '</p>');
        mediaSelector.change(function () {
            mediaSelector.parents('.col-sm-9').first().find('.help-block').first().html('');
            mediaSelector.parents('.col-sm-9').first().find('.help-block').first().html(getMediaPreview(mediaSelector.val()));
        });
    });
}
