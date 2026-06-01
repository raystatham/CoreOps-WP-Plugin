/* global FullCalendar, coreopsBooking */
( function () {
  'use strict';

  const { restUrl, nonce } = coreopsBooking;

  // ── Calendar ───────────────────────────────────────────────────────────────

  document.querySelectorAll( '.coreops-calendar' ).forEach( function ( el ) {
    const view   = el.dataset.view   || 'dayGridMonth';
    const userId = el.dataset.userId || '';

    const calendar = new FullCalendar.Calendar( el, {
      initialView: view,
      headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  'dayGridMonth,timeGridWeek,listWeek',
      },
      events: function ( info, successCallback, failureCallback ) {
        const params = new URLSearchParams( { from: info.startStr, to: info.endStr } );
        if ( userId ) { params.set( 'user_id', userId ); }
        fetch( restUrl + 'events?' + params )
          .then( ( r ) => r.json() )
          .then( ( data ) => successCallback( data.events ?? [] ) )
          .catch( failureCallback );
      },
    } );

    calendar.render();
  } );

  // ── Availability search ────────────────────────────────────────────────────

  document.querySelectorAll( '.coreops-availability' ).forEach( function ( el ) {
    const form   = el.querySelector( '.coreops-availability-form' );
    const result = el.querySelector( '.coreops-availability-result' );
    const userId = el.dataset.userId || '';

    form.addEventListener( 'submit', function ( e ) {
      e.preventDefault();
      const from = form.querySelector( '[name="from"]' ).value;
      const to   = form.querySelector( '[name="to"]' ).value;
      if ( ! from || ! to ) { return; }

      result.textContent = 'Checking…';
      const params = new URLSearchParams( { from, to } );
      if ( userId ) { params.set( 'user_id', userId ); }

      fetch( restUrl + 'availability?' + params )
        .then( ( r ) => r.json() )
        .then( ( data ) => {
          result.textContent = data.available
            ? '✓ Available'
            : `✗ Not available — ${ data.event_count } event(s) overlap this period.`;
        } )
        .catch( () => { result.textContent = 'Error checking availability.'; } );
    } );
  } );

  // ── Booking form ───────────────────────────────────────────────────────────

  document.querySelectorAll( '.coreops-booking-form' ).forEach( function ( el ) {
    const form   = el.querySelector( '.coreops-booking' );
    const result = el.querySelector( '.coreops-booking-result' );

    form.addEventListener( 'submit', function ( e ) {
      e.preventDefault();
      if ( form.querySelector( '[name="website"]' ).value ) { return; } // honeypot

      const data = {
        name:     form.querySelector( '[name="name"]' ).value.trim(),
        email:    form.querySelector( '[name="email"]' ).value.trim(),
        start_at: form.querySelector( '[name="start_at"]' ).value,
        end_at:   form.querySelector( '[name="end_at"]' ).value,
        notes:    form.querySelector( '[name="notes"]' ).value.trim(),
      };

      result.textContent = 'Submitting…';

      fetch( restUrl + 'book', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body:    JSON.stringify( data ),
      } )
        .then( ( r ) => r.json() )
        .then( ( res ) => {
          if ( res.error ) {
            result.textContent = '✗ ' + res.error;
          } else {
            result.textContent = '✓ Booking submitted successfully.';
            form.reset();
          }
        } )
        .catch( () => { result.textContent = 'Error submitting booking.'; } );
    } );
  } );

} )();
