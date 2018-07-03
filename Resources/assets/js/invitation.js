"use strict";

import Globals from '@BkstgCoreBundle/Resources/assets/js/globals.js';

Globals.behaviours.invitation = function() {
  $('.invitation-respond input').once().on('change', function (e) {
    var parent = $(this).closest('.invitation-respond');
    $(parent).find('.btn').addClass('disabled')
    $.ajax({
      url: $(this).data('respond-url'),
      success: function (data, status) {
        $(parent).find('.btn').removeClass('disabled');
      }
    });
  });
}
