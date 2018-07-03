"use strict";

import Globals from '@BkstgCoreBundle/Resources/assets/js/globals.js';

Globals.behaviours.schedule = function() {
  $('#bkstg_schedulebundle_schedule_events > .card-footer .collection-item-add').once().click(function (e) {
    var items = $('#bkstg_schedulebundle_schedule_events > .collection-items');
    var new_item = $(items).find('> .collection-item:nth-last-child(1)');
    var previous_item = $(items).find('> .collection-item:nth-last-child(2)');

    // Prepopulate some stuff in the new event.
    if (previous_item.length > 0) {
      $(new_item).find('.end-date input[type="date"]').val($(previous_item).find('.end-date input[type="date"]').val());
      $(new_item).find('.start-date input[type="date"]').val($(previous_item).find('.end-date input[type="date"]').val());
      $(new_item).find('.start-date input[type="time"]').val($(previous_item).find('.end-date input[type="time"]').val());
    }
  });
}
