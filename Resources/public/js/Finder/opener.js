window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
window.CmfcmfMediaModule.currentEditor = window.CmfcmfMediaModule.currentEditor || null;
window.CmfcmfMediaModule.currentEditorInstance = window.CmfcmfMediaModule.currentEditorInstance || null;

/**
 * Returns the attributes used for the popup window. 
 * @return {String}
 */
function getCmfcmfMediaModulePopupAttributes() {
    var pWidth, pHeight;

    pWidth = screen.width * 0.75;
    pHeight = screen.height * 0.66;

    return 'width=' + pWidth + ',height=' + pHeight + ',location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes';
}

/**
 * Open a popup window with the finder triggered by an editor button.
 */
function CmfcmfMediaModuleFinderOpenPopup(editor, editorName) {
    var popupUrl;

    // Save editor for access in selector window
    window.CmfcmfMediaModule.currentEditor = editorName;
    window.CmfcmfMediaModule.currentEditorInstance = editor;

    popupUrl = Routing.generate('cmfcmfmediamodule_finder_choosemethod');

    if (editorName == 'ckeditor') {
        editor.popup(popupUrl, /*width*/ '80%', /*height*/ '70%', getCmfcmfMediaModulePopupAttributes());
    } else {
        window.open(popupUrl, '_blank', getCmfcmfMediaModulePopupAttributes());
    }
}
