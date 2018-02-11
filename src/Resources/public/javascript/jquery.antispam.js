"use strict";
/*! (c) Christian Gripp, core23 - webdesign & more | core23.de */
(function ($) {
  $.antiSpam = {version: '1.00'};

  $.fn.antiSpam = function () {
    function clean(string) {
      return string.replace(/[\[\(\{][\w\.]+[\}\)\]]/g, '.').replace(/\s+/g, '');
    }

    return $(this).each(function () {
      var itm = $(this);
      var spans = itm.children('span');

      if (spans.length < 2 || spans.length > 3) {
        return;
      }

      var usr = spans.filter(':eq(0)').text();
      var dmn = spans.filter(':eq(1)').text();
      var txt = spans.filter(':eq(2)').text();
      var ats = String.fromCharCode(32 * 2);
      var cpl = clean(usr) + ats + clean(dmn);
      var mto = String.fromCharCode(109, 97, 105, 108, 116, 111, 58);
      var hrf = mto + cpl;
      var atx = $('<a>').attr('href', hrf).attr('target', '_blank').text(txt ? txt : cpl);
      itm.html(atx);
    });
  };
})(jQuery);
