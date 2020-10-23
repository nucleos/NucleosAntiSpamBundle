export default class AntiSpam {
  /**
   * @param {string} selector
   *
   * @constructor
   */
  constructor(selector) {
    document.querySelectorAll(selector).forEach((element) => {
      AntiSpam.replaceText(element);
    });
  }

  /**
   * @param {string }string
   * @returns {string}
   */
  static clean(string) {
    return string.replace(/[[({][\w.]+[})\]]/g, '.').replace(/\s+/g, '');
  }

  /**
   * @param {Element} element
   */
  static replaceText(element) {
    const spans = element.querySelectorAll('span');

    if (spans.length < 2 || spans.length > 3) {
      return;
    }

    const local = spans[0].textContent;
    const domain = spans[1].textContent;
    const text = spans.length === 3 ? spans[2].textContent : null;

    const ats = String.fromCharCode(32 * 2);
    const cleanText = this.clean(local) + ats + this.clean(domain);
    const mailto = String.fromCharCode(109, 97, 105, 108, 116, 111, 58);
    const href = mailto + cleanText;

    const link = document.createElement('a');
    link.setAttribute('href', href);
    link.setAttribute('target', '_blank');
    link.innerText = text ? text : cleanText;

    element.replaceWith(link);
  }
}
