(function ($) {
    if ('undefined' != typeof window.toastr) {
        window.toastr.options = {
            'progressBar': true,
            'closeButton': true
        };
    }
    $(function () {
        window.CmfcmfMediaModule = window.CmfcmfMediaModule || {};
        window.CmfcmfMediaModule.Util = {
            alert: function (title, body) {
                alert(title + "\n" + body);
            },
            Ajax: {
                fail: function (data) {
                    var errorText = Translator.trans('Sorry, the AJAX request could not be finished. Please try again.');
                    if (data.responseJSON && data.responseJSON.error) {
                        errorText = data.responseJSON.error;
                    } else if (data.status == 403) {
                        errorText = Translator.trans('You do not have permission to execute this action.');
                    }
                    if ('undefined' != typeof window.toastr) {
                        window.toastr['error'](errorText, Translator.trans('Something went wrong!'));
                    } else {
                        alert(Translator.trans('Something went wrong!') + ' ' + errorText);
                    }
                },
                makeExternalRequest: function (url, done, fail, always) {
                    var xmlhttp;

                    if (window.XMLHttpRequest) {
                        // code for IE7+, Firefox, Chrome, Opera, Safari
                        xmlhttp = new XMLHttpRequest();
                    } else {
                        // code for IE6, IE5
                        xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
                    }

                    xmlhttp.onreadystatechange = function() {
                        if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
                            if(xmlhttp.status == 200){
                                done(xmlhttp.responseText);
                            } else {
                                fail();
                            }
                            if (typeof always == 'function') {
                                always();
                            }
                        }
                    };

                    xmlhttp.open('GET', url, true);
                    xmlhttp.send();
                }
            },
            // Taken from http://stackoverflow.com/a/1219983/2560557
            htmlEncode: function(value) {
                //create a in-memory div, set it's inner text(which jQuery automatically encodes)
                //then grab the encoded contents back out.  The div never exists on the page.
                return $('<div/>').text(value).html();
            },
            htmlAttrEncode: function(value) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };

                return String(value).replace(/[&<>"']/g, function(m) { return map[m]; });
            },
            htmlDecode: function(value){
                return $('<div/>').html(value).text();
            },
            nl2br: function (str) {
                //  discuss at: http://phpjs.org/functions/nl2br/
                // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                // improved by: Philip Peterson
                // improved by: Onno Marsman
                // improved by: Atli Þór
                // improved by: Brett Zamir (http://brett-zamir.me)
                // improved by: Maximusya
                // bugfixed by: Onno Marsman
                // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                //    input by: Brett Zamir (http://brett-zamir.me)

                return (str + '')
                    .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br />' + '$2');
            },
            lcfirst: function (str) {
                //  discuss at: http://phpjs.org/functions/lcfirst/
                // original by: Brett Zamir (http://brett-zamir.me)
                //   example 1: lcfirst('Kevin Van Zonneveld');
                //   returns 1: 'kevin Van Zonneveld'

                str += '';
                var f = str.charAt(0)
                    .toLowerCase();
                return f + str.substr(1);
            },
            liveUpdateDropdownButton: function($element) {
                $element.parent().find("> .dropdown-menu li a").click(function(){
                    $element
                        .html($(this).text() + ' <span class="caret"></span>')
                        .val($(this).data('value'))
                        .change();
                });
            }
        };
    });
})(jQuery);
