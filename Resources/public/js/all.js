"use strict";

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

Globals.behaviours.full_company_widget = function() {
  $('.event-invitation-list').once().each(function (e) {
    var full_company = $(this).find('.event-full-company');
    var invitations = $(this).find('.event-invitations');

    // Hide the invitation list if full company is checked.
    if ($(full_company).find('input[type="checkbox"]:checked').length > 0) {
      $(invitations).hide();
    }

    // Toggle invitations on full company change.
    $(full_company).find('input[type="checkbox"]').change(function (e) {
      $(invitations).toggle();
    });
  });
}

Globals.behaviours.full_company_hide = function() {
  $('.full-company-wrapper').once().each(function (e) {
    // Gather some variables.
    var toggle = $(this).find('.full-company-toggle');
    var show_label = $(toggle).html();
    var hide_label = $(toggle).data('hide-label');
    var invitations = $(this).find('.full-company-list');

    // Hide invitations by default and toggle on click.
    $(invitations).hide();
    $(toggle).click(function (e) {
      e.preventDefault();
      $(invitations).toggle();
      if ($(toggle).html() == show_label) {
        $(toggle).html(hide_label);
      } else {
        $(toggle).html(show_label);
      }
    });
  });
}

Globals.behaviours.google_maps_autocomplete = function() {
  function initAutocomplete() {
    $('input.google-geolocate').once().each(function (e) {
      // Setup autocomplete.
      var autocomplete = new google.maps.places.Autocomplete((this), {types: ['geocode']});

      // Bias to local options.
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          var geolocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };
          var circle = new google.maps.Circle({
            center: geolocation,
            radius: position.coords.accuracy
          });
          autocomplete.setBounds(circle.getBounds());
        });
      }
    });
  }

  // Attach to window for google APIs.
  window.initAutocomplete = initAutocomplete;
}

Globals.behaviours.invitation = function() {
  $('.invitation-respond input').once().on('change', function (e) {
    var parent = $(this).closest('.invitation-respond');
    $(parent).find('.btn').addClass('disabled')
    $.ajax({
      type: 'POST',
      url: $(this).data('respond-url'),
      success: function (data, status) {
        $(parent).find('.btn').removeClass('disabled');
      }
    });
  });
}

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
