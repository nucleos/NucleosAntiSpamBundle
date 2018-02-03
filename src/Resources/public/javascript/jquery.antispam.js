"use strict";
/*! (c) Christian Gripp, core23 - webdesign & more | core23.de */
(function ($) {
    $.antiSpam = {version: '1.00'};

    $.fn.antiSpam = function () {
        function clean(string) {
            return string.replace(/[\[\(\{]\w+[\}\)\]]/g, '.').replace(/\s+/g, '');
        }

        return $(this).each(function () {
            var itm = $(this);
            var usr = itm.children('span:eq(0)').text();
            var dmn = itm.children('span:eq(1)').text();
            var txt = itm.children('span:eq(2)').text();
            var ats = String.fromCharCode(32 * 2);
            var cpl = clean(usr) + ats + clean(dmn);
            var mto = String.fromCharCode(109, 97, 105, 108, 116, 111, 58);
            var hrf = mto + cpl;
            var atx = $('<a>').attr('href', hrf).attr('target', '_blank').text(txt ? txt : cpl);
            itm.html(atx);
        });
    };
})(jQuery);
