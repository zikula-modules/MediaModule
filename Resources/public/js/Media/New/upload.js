(function ($) {
    var formDataToAdd = {};
    $(function () {
        var isPopup = $('#cmfcmfmedia-is-popup').val();
        $('#cmfcmfmedia-upload-start-fast-btn').popover();

        var myDropzone = initDropzone(onAllFilesUpload, onOneFileUpload);

        function onOneFileUpload (file) {
            getMediaTypeFromFiles([fileToArray(file)], function (response) {
                if (!response.result[0]) {
                    window.toastr['error'](Translator.__('This media type is currently not supported'), Translator.__("We're sorry!"));
                    $('#cmfcmfmedia-tab-content').spin(false);
                    return;
                }
                formDataToAdd.collection = null;

                myDropzone.enqueueFile(file);
            });
        }

        function onAllFilesUpload () {
            var files = myDropzone.getFilesWithStatus(Dropzone.ADDED);
            var filesArr = [];
            for (var i = 0; i < files.length; i++) {
                filesArr.push(fileToArray(files[i]));
            }

            getMediaTypeFromFiles(filesArr, function (response) {
                var canUpload = '';

                for (var i = 0; i < response.result.length; i++) {
                    if (response.result[i]) {
                        canUpload = true;
                        break;
                    }
                }
                if (response.notFound > 0) {
                    if (!canUpload) {
                        window.toastr['error'](Translator.__('These files are not supported.'), Translator.__('Cannot upload files!'));
                        return;
                    }
                    window.toastr['warning'](Translator.__('Some of these files will not be uploaded.'), Translator.__('Cannot upload everything!'));
                }

                openCollectionSelectModal(function (collection) {
                    //console.log('Selected collection', collection);
                    formDataToAdd.collection = collection;

                    myDropzone.enqueueFiles(files);
                });
            });

            function openCollectionSelectModal(onDone) {
                $('#cmfcmfmedia-upload-form-modal').modal('show');

                $('#cmfcmfmedia-upload-form-modal-save-btn').one('click', function () {
                    var collection = $('#cmfcmfmedia-upload-form-modal-collection-select').val();
                    if (collection == -1) {
                        return;
                    }
                    onDone(collection);
                });
            }
        }

        function uploadStart() {
            $('#cmfcmfmedia-tab-content').spin();
        }

        function uploadEnd() {
            $('#cmfcmfmedia-tab-content').spin(false);
        }

        function getMediaTypeFromFiles(files, onDone) {
            if (files.length == 0) {
                return;
            }
            uploadStart();
            $.ajax(Routing.generate('cmfcmfmediamodule_media_getmediatypefromfile'), {
                method: 'POST',
                data: {
                    files: files
                }
            }).done(function (response) {
                onDone(response);
            }).fail(window.CmfcmfMediaModule.Util.Ajax.fail).always(uploadEnd);
        }

        function fileToArray(file) {
            return {
                size: file.size,
                mimeType: (file.type.length > 0) ? file.type : 'text/plain',
                name: file.name
            };
        }

        function initDropzone(onAllFilesUpload, onOneFileUpload) {
            // Get the template HTML and remove it from the doument
            var previewNode = document.querySelector("#cmfcmfmedia-upload-template");
            previewNode.id = "";
            var previewTemplate = previewNode.parentNode.innerHTML;
            previewNode.parentNode.removeChild(previewNode);

            var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
                url: Routing.generate('cmfcmfmediamodule_media_upload'), // Set the url
                thumbnailWidth: 80,
                thumbnailHeight: 80,
                maxThumbnailFilesize: 5,
                maxFilesize: $('#cmfcmfmedia-upload').data('max-filesize') / 1000 / 1000,
                parallelUploads: 20,
                maxFiles: isPopup ? 1 : null,
                previewTemplate: previewTemplate,
                autoQueue: false, // Make sure the files aren't queued until manually added
                previewsContainer: "#cmfcmfmedia-upload-previews", // Define the container to display the previews
                clickable: "#cmfcmfmedia-upload-fileinput-btn",
                dragover: function (event) {
                    //console.log('OVER');
                    //console.log(event);
                },
                dragleave: function (event) {
                    //console.log('LEAVE');
                    //console.log(event);
                }
            });

            // Update the total progress bar
            myDropzone.on('totaluploadprogress', function(progress) {
                document.querySelector("#cmfcmfmedia-upload-total-progress .progress-bar").style.width = progress + '%';
            });

            // Hide the total progress bar when nothing's uploading anymore
            myDropzone.on('queuecomplete', function(progress) {
                document.querySelector("#cmfcmfmedia-upload-total-progress").style.opacity = '0';

                window.toastr["success"]('', Translator.__('Files have been uploaded'));
            });

            myDropzone.on('sending', function(file, xhr, formData) {
                // Show the total progress bar when upload starts
                document.querySelector("#cmfcmfmedia-upload-total-progress").style.opacity = '1';
                // And disable the start button
                file.previewElement.querySelector('.start').setAttribute('disabled', 'disabled');

                Object.keys(formDataToAdd).forEach(function (key) {
                    if (formDataToAdd[key] != null) {
                        formData.append(key, formDataToAdd[key]);
                    }
                });
            });

            myDropzone.on('addedfile', function(file) {
                // Hookup the start button
                file.previewElement.querySelector('.start').onclick = function () {
                    onOneFileUpload(file);
                };
            });

            myDropzone.on('success', function (file, response) {
                if (response.openNewTabAndEdit) {
                    var parent = $('#cmfcmfmedia-upload-form-modal-collection-select').data('parent');
                    var parameters = '';
                    if (parent) {
                        parameters = '?parent=' + encodeURIComponent(parent);
                    }
                    if (isPopup) {
                        if (parameters.length == 0) {
                            parameters = '?';
                        } else {
                            parameters += '&';
                        }
                        parameters += 'popup=1';

                        window.location.href = response.editUrl + parameters;
                    } else {
                        var win = window.open(response.editUrl + parameters, '_blank');
                        win.focus();
                    }
                }
            });

            // Setup the buttons for all transfers
            // The "add files" button doesn't need to be setup because the config
            // `clickable` has already been specified.
            $('#cmfcmfmedia-upload-actions').find('.start').click(onAllFilesUpload);

            return myDropzone;
        }
    });
})(jQuery);
