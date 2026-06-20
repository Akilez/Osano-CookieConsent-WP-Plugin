(function ($) {
  function activateTab(tabKey) {
    $('.ccbo-cookie-consent-tabs .nav-tab').removeClass('nav-tab-active');
    $('.ccbo-cookie-consent-tabs .nav-tab[data-tab-target="' + tabKey + '"]').addClass('nav-tab-active');
    $('.ccbo-cookie-consent-tab-panel').removeClass('is-active');
    $('.ccbo-cookie-consent-tab-panel[data-tab-panel="' + tabKey + '"]').addClass('is-active');
  }

  $(function () {
    $('.ccbo-color-field').wpColorPicker();

    $('.ccbo-cookie-consent-tabs').on('click', '.nav-tab', function () {
      activateTab($(this).data('tab-target'));
    });
  });
})(jQuery);
