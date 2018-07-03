"use strict";

import 'bootstrap';
import 'bootstrap-calendar';
import 'jquery-once';

import Globals from '@BkstgCoreBundle/Resources/assets/js/globals.js';

Globals.behaviours.calendar = function() {
  var path = window.location.pathname.split("/");

  $('#calendar').once().each(function() {
    var calendar = $(this).calendar({
      tmpl_path: "/bundles/bkstgschedule/templates/",
      events_source: $(this).data('search-url'),
      format12: true,
      view: 'week',
      onAfterViewLoad: function(view) {
        $('h3.date-display').text(this.getTitle());
        $('.btn-group button').removeClass('active');
        $('button[data-calendar-view="' + view + '"]').addClass('active');
      },
    });

    $('.btn-group button[data-calendar-nav]').each(function() {
      var $this = $(this);
      $this.click(function() {
        calendar.navigate($this.data('calendar-nav'));
      });
    });

    $('.btn-group button[data-calendar-view]').each(function() {
      var $this = $(this);
      $this.click(function() {
        calendar.view($this.data('calendar-view'));
      });
    });
  });
}
