(function ($) {
    window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
    window.CmfcmfMediaModule.WebCreation = {
        tableColumns: []
    };

    //// Table Columns

    // Constructor
    window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn = function (name) {
        this.name = name;
    };
    // Render function
    window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.prototype.render = function (content) {
        return window.CmfcmfMediaModule.Util.nl2br(
            window.CmfcmfMediaModule.Util.htmlEncode(content)
        );
    };

    // Constructor
    window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn = function (name) {
        window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.call(this, name);

        this.maxWidth = 200;
        this.maxHeight = 150;
    };
    // Inheritance
    window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn.prototype
        = Object.create(window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.prototype);
    // Render function
    window.CmfcmfMediaModule.WebCreation.tableColumns.ImageColumn.prototype.render = function (content) {
        return '<img style="max-width: ' + this.maxWidth + 'px; max-height: ' + this.maxHeight + 'px;"' +
            ' src="' + window.CmfcmfMediaModule.Util.htmlEncode(content) + '" />';
    };

    // Constructor
    window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn = function FirstLineBoldColumn(name) {
        window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.call(this, name);
    };
    // Inheritance
    window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn.prototype
        = Object.create(window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.prototype);
    // Render function
    window.CmfcmfMediaModule.WebCreation.tableColumns.FirstLineBoldColumn.prototype.render = function (content) {
        content = window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.prototype.render(content);
        content = '<strong>' + content;
        var pos = content.indexOf('<br />');
        if (pos === -1) {
            content += '</strong>';
        } else {
            content = content.slice(0, pos) + '</strong>' + content.slice(pos);
        }

        return content;
    };

    // Constructor
    window.CmfcmfMediaModule.WebCreation.tableColumns.UrlColumn = function (name) {
        window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.call(this, name);
    };
    // Inheritance
    window.CmfcmfMediaModule.WebCreation.tableColumns.UrlColumn.prototype
        = Object.create(window.CmfcmfMediaModule.WebCreation.tableColumns.TextColumn.prototype);
    // Render function
    window.CmfcmfMediaModule.WebCreation.tableColumns.UrlColumn.prototype.render = function (content) {
        var text = window.CmfcmfMediaModule.Util.htmlEncode(content.text);
        if (content.url != "") {
            var url = window.CmfcmfMediaModule.Util.htmlEncode(content.url);
            return '<a href="' + url + '">' + text + '</a>';
        }
        return text;
    };

    //// Media Type

    window.CmfcmfMediaModule.WebCreation.WebMediaTypeBase
        = function (mediaType, tableColumns, dropdownOptions, inputPlaceholder, onSearch) {
        var $results = $('#cmfcmfmedia-mediatype-' + mediaType + '-results');

        bindSubmitButtons();
        createHeaderRow();
        createSearchInput();
        bindSearchCallback();

        function bindSubmitButtons() {
            $(document).on('click', '.cmfcmfmedia-web-mediatype-' + mediaType + '-submit-btn', function () {
                $('#cmfcmfmedia-web-mediatype-' + mediaType + '-settings').val(JSON.stringify($(this).data('settings')));
            });
        }

        function createHeaderRow() {
            var headerRow = '<tr>';
            for (var c = 0; c < tableColumns.length; c++) {
                headerRow += '<th>' + tableColumns[c].name + '</th>';
            }
            headerRow += '<th></th>';
            headerRow += '</tr>';
            $results.parent().find('thead').append(headerRow);
        }

        function createSearchInput() {
            var firstOptionKey = Object.keys(dropdownOptions)[0];
            var input =
                '<div class="input-group">' +
                    '<div class="input-group-btn">' +
                        '<button type="button" value="' + firstOptionKey + '" ' +
                            'class="btn btn-secondary dropdown-toggle cmfcmfmedia-web-search-type-btn" ' +
                            'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ' +
                            'id="cmfcmfmedia-mediatype-' + mediaType + '-search-type">' +
                            dropdownOptions[firstOptionKey] + ' <span class="caret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">';
            Object.keys(dropdownOptions).forEach(function (key) {
                input += '<li><a href="#" data-value="' + key + '">' + dropdownOptions[key] + '</a></li>';
            });
            input +=
                        '</ul>' +
                    '</div>' +
                    '<input type="text" class="form-control" name="q" id="cmfcmfmedia-mediatype-' + mediaType + '-search" placeholder="' + inputPlaceholder + '">' +
                '</div>';

            $results.parents('form').prepend(input);

            window.CmfcmfMediaModule.Util.liveUpdateDropdownButton($('.cmfcmfmedia-web-search-type-btn'));
        }

        function bindSearchCallback() {
            var $input = $('#cmfcmfmedia-mediatype-' + mediaType + '-search');
            var $btn = $('#cmfcmfmedia-mediatype-' + mediaType + '-search-type');
            var actualCallback = function () {
                var searchInput = $input.val();
                if (searchInput.length == 0) {
                    return;
                }
                $results.empty();
                $results.append('<tr><td colspan="' + (tableColumns.length + 1) + '" class="text-center">' + Translator.trans('Searching...') + '</td></tr>');

                var dropdownValue = $btn.val();
                if (typeof onSearch === "function") {
                    onSearch(searchInput, dropdownValue, handleSearchResult);
                } else {
                    $.ajax({
                        url: Routing.generate('cmfcmfmediamodule_media_webcreationajaxresults', {mediaType: mediaType}),
                        data: {
                            q: searchInput,
                            dropdownValue: dropdownValue == 'everything' ? null : dropdownValue
                        }
                    })
                        .fail(window.CmfcmfMediaModule.Util.Ajax.fail)
                        .done(handleSearchResult)
                    ;
                }

                function handleSearchResult(resultSet) {
                    // Remove table content.
                    $results.empty();
                    if (resultSet.results.length == 0) {
                        $results.append(
                            '<tr><td colspan="' + (tableColumns.length + 1) + '" class="text-center">' + Translator.trans('No results found.') + '</td></tr>'
                        );
                        return;
                    }
                    var resultTable = '';
                    for (var i = 0; i < resultSet.results.length; i++) {
                        resultTable += '<tr>';
                        for (var c = 1; c < resultSet.results[i].length; c++) {
                            resultTable += '<td>';
                            resultTable += tableColumns[c - 1].render(resultSet.results[i][c]);
                            resultTable += '</td>';
                        }
                        var settings = window.CmfcmfMediaModule.Util.htmlAttrEncode(JSON.stringify(resultSet.results[i][0]));
                        resultTable += '<td>' +
                            '<button type="submit" class="btn btn-primary cmfcmfmedia-web-mediatype-' + mediaType +
                            '-submit-btn" data-settings="' + settings + '">' + Translator.trans('Choose') + ' <i class="fa fa-fw fa-arrow-right"></i></button>' +
                            '</td>';
                        resultTable += '</tr>';
                    }
                    $results.append(resultTable);
                }
            };
            $input.blur(actualCallback);
            $btn.change(actualCallback);
            $(document).on('keypress', $input, function(event) {
                if (event.keyCode != 13) {
                    return true;
                }
                $input.blur();
                // Ignore ENTER -> Do not submit form on enter.
                return false;
            });
        }
    }
})(jQuery);
