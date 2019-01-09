CKEDITOR.plugins.add('cmfcmfmediamodule', {
    requires: 'popup',
    lang: 'en,de',
    init: function (editor) {
        editor.addCommand('insertMediaObject', {
            exec: function (editor) {
                CmfcmfMediaModuleFinderOpenPopup(editor, 'ckeditor');
            }
        });
        editor.ui.addButton('cmfcmfmediamodule', {
            label: editor.lang.cmfcmfmediamodule.title,
            command: 'insertMediaObject',
            icon: this.path.replace('scribite/CKEditor/cmfcmfmediamodule', 'images') + 'admin.png'
        });
    }
});
