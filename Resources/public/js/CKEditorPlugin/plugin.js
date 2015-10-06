window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
window.CmfcmfMediaModule.currentEditor = window.CmfcmfMediaModule.currentEditor || null;
window.CmfcmfMediaModule.currentEditorInstance = window.CmfcmfMediaModule.currentEditorInstance || null;

CKEDITOR.plugins.add('cmfcmfmediamodule', {
    requires: 'popup',
    lang: 'en,de',
    init: function (editor) {
        editor.addCommand('insertMediaObject', {
            exec: function (editor) {
                window.CmfcmfMediaModule.currentEditor = 'ckeditor';
                window.CmfcmfMediaModule.currentEditorInstance = editor;
                editor.popup(
                    Routing.generate('cmfcmfmediamodule_finder_choosemethod'),
                    /*width*/ '80%', /*height*/ '70%',
                    'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes'
                );
            }
        });
        editor.ui.addButton('cmfcmfmediamodule', {
            label: editor.lang.cmfcmfmediamodule.title,
            command: 'insertMediaObject',
            icon: this.path + '/../../../images/admin.png'
        });
    }
});
