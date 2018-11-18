import jQuery from 'jquery';

(function (jQuery) {
  "use strict";

  jQuery.antiSpam = {version: '1.00'};

  jQuery.fn.antiSpam = function () {
    function clean(string) {
      return string.replace(/[\[\(\{][\w\.]+[\}\)\]]/g, '.').replace(/\s+/g, '');
    }

    return jQuery(this).each(function () {
      const itm = jQuery(this);
      const spans = itm.children('span');

      if (spans.length < 2 || spans.length > 3) {
        return;
      }

      const usr = spans.filter(':eq(0)').text();
      const dmn = spans.filter(':eq(1)').text();
      const txt = spans.filter(':eq(2)').text();
      const ats = String.fromCharCode(32 * 2);
      const cpl = clean(usr) + ats + clean(dmn);
      const mto = String.fromCharCode(109, 97, 105, 108, 116, 111, 58);
      const hrf = mto + cpl;
      const atx = jQuery('<a>').attr('href', hrf).attr('target', '_blank').text(txt ? txt : cpl);
      itm.html(atx);
    });
  };
})(jQuery);
