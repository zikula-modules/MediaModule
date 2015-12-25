(function ($) {
    $(function () {
        $('.cmfcmfmedia-permission-open-creation-modal').click(function () {
            var afterPermissionId = $(this).data('after-permission-id');
            $('#cmfcmfmedia-permission-after-permission-id').val(afterPermissionId);
        });
        /*
         {{ path('cmfcmfmediamodule_permission_new', {afterPermission: '%afterPermission', collection: collection.id, type: '%type'}) }}
        */
        $('#cmfcmfmedia-permission-create-btn').click(function () {
            window.location.href = Routing.generate('cmfcmfmediamodule_permission_new', {
                afterPermission: $('#cmfcmfmedia-permission-after-permission-id').val(),
                collection: $('#cmfcmfmedia-permission-collection').val(),
                type: $('#cmfcmfmedia-permission-permission-type').val()
            })
        });
    })
})(jQuery);
