(function ($) {
    $(function () {
        // Sorting existing permissions.
        var $table = $('#cmfcmfmedia-permission-sortable-table').find('tbody');
        Sortable.create($table[0], {
            animation: 150,
            handle: ".fa-sort",
            draggable: "tr",
            onUpdate: function (evt) {
                var $item = $(evt.item);
                if (evt.oldIndex > evt.newIndex && !$item.hasClass('goOn')) {
                    // Element moved up the list.
                    var highestLockedIndex = -1;
                    var i = 0;
                    $table.children().each(function () {
                        if ($(this).hasClass('locked')) {
                            highestLockedIndex = i;
                        }
                        i++;
                    });
                    // Element is before the lowest locked element.
                    if (evt.newIndex < highestLockedIndex) {
                        window.toastr['error'](Translator.__('You cannot move a permission with goOn = no above a locked permission.'), Translator.__('Problem detected!'));
                        $table.find('tr:nth-child(' + parseInt(evt.oldIndex + 1) + ')').after($item);

                        return;
                    }
                }
                $.get(Routing.generate('cmfcmfmediamodule_permission_reorder', {
                    permissionId: parseInt($item.data('id')),
                        permissionVersion: parseInt($item.data('version')),
                        newIndex: parseInt(evt.newIndex)
                })).success(function (data) {
                    $item.data('version', data.newVersion);
                    window.toastr['success']('', Translator.__('Saved new position.'));
                }).fail(window.CmfcmfMediaModule.Util.Ajax.fail);
            }
        });

        // Modal to create a new permission.
        $('.cmfcmfmedia-permission-open-creation-modal').click(function () {
            var afterPermissionId = $(this).data('after-permission-id');
            $('#cmfcmfmedia-permission-after-permission-id').val(afterPermissionId);
        });
        $('#cmfcmfmedia-permission-create-btn').click(function () {
            window.location.href = Routing.generate('cmfcmfmediamodule_permission_new', {
                afterPermission: $('#cmfcmfmedia-permission-after-permission-id').val(),
                collection: $('#cmfcmfmedia-permission-collection').val(),
                type: $('#cmfcmfmedia-permission-permission-type').val()
            })
        });
    })
})(jQuery);
