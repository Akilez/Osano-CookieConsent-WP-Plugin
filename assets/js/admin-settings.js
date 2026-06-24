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

    $('.ccbo-script-gate').on('click', '.ccbo-script-gate-add', function () {
      var $container = $(this).closest('.ccbo-script-gate');
      var nextIndex = parseInt($container.attr('data-next-index'), 10) || 0;
      var template = $container.find('.ccbo-script-gate-template').html();

      template = template.replace(/__index__/g, String(nextIndex));

      var $row = $(template);
      $row.find(':input').prop('disabled', false);
      $container.find('.ccbo-script-gate-rows').append($row);
      $container.attr('data-next-index', String(nextIndex + 1));
    });

    $('.ccbo-script-gate').on('click', '.ccbo-script-gate-remove', function () {
      $(this).closest('.ccbo-script-gate-row').remove();
    });
  });
})(jQuery);
