"use strict";

import once from 'jquery-once';
import Globals from '@BkstgCoreBundle/Resources/assets/js/globals.js';

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
