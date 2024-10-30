(function ($) {
  'use strict';

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(window).load(function () {
    var elements = $('.additional-categories-wrapper');

    elements.each(function () {
      var $el = $(this);
      var select = $el.find('.multiple-select');
      var hidden = $el.find('.input-hidden');
      select.select2({
        width: '100%',
        allowClear: true,
        placeholder: function () {
          $(this).data('placeholder');
        }
      });
      var val = select.data('value');
      select.val(val.split(';'));
      select.select2().on('change', function () {
        var selected = select.val() || [];
        hidden.val(selected.join(';'));
      })
    })

    elements.show();

    $('.fill-value').each(function () {
      var el = $(this)
      var val = el.data('value');
      if (val) {
        el.val(val);
      }
    });
  });

})(jQuery);
