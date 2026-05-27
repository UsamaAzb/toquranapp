/**
 * App Calendar
 */

/**
 * ! If both start and end dates are same Full calendar will nullify the end date value.
 * ! Full calendar will end the event on a day before at 12:00:00AM thus, event won't extend to the end date.
 * ! We are getting events from a separate file named app-calendar-events.js. You can add or remove events from there.
 *
 **/

'use strict';
if (window.axios && document.querySelector('meta[name="csrf-token"]')) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}
document.addEventListener('DOMContentLoaded', function () {
  const direction = isRtl ? 'rtl' : 'ltr';
  (function () {
    // DOM Elements
    const calendarEl = document.getElementById('calendar');
    const appCalendarSidebar = document.querySelector('.app-calendar-sidebar');
    const addEventSidebar = document.getElementById('addEventSidebar');
    const appOverlay = document.querySelector('.app-overlay');
    const offcanvasTitle = document.querySelector('.offcanvas-title');
    const btnToggleSidebar = document.querySelector('.btn-toggle-sidebar');
    const btnSubmit = document.getElementById('addEventBtn');
    const btnDeleteEvent = document.querySelector('.btn-delete-event');
    const btnCancel = document.querySelector('.btn-cancel');
    const eventTitle = document.getElementById('eventTitle');
    const eventStartDate = document.getElementById('eventStartDate');
    const eventEndDate = document.getElementById('eventEndDate');
    const eventUrl = document.getElementById('eventURL');
    // const eventLocation = document.getElementById('eventLocation');
    const eventDescription = document.getElementById('eventDescription');
    // const allDaySwitch = document.querySelector('.allDay-switch');
    const allDaySwitch = document.getElementById('allDaySwitch');

    const selectAll = document.querySelector('.select-all');
    const filterInputs = Array.from(document.querySelectorAll('.input-filter'));
    const inlineCalendar = document.querySelector('.inline-calendar');

    // Calendar settings
    const calendarColors = {
      Busy: 'danger',
   Available: 'success',
   Consultation: 'primary',
   Classes: 'warning',
    Holiday: 'info',
   // لو رجعت قيم بحروف صغيرة أيضًا
   busy: 'danger',
   available: 'success',
   consultation: 'primary',
   classes: 'warning',
     holiday: 'info',
    };

    // External jQuery Elements
    const eventLabel = $('#eventLabel'); // ! Using jQuery vars due to select2 jQuery dependency
    const eventGuests = $('#eventGuests'); // ! Using jQuery vars due to select2 jQuery dependency

    // Event Data
    // let currentEvents = events;
    // Assuming events are imported from app-calendar-events.js
    let isFormValid = false;
    let eventToUpdate = null;
    let inlineCalInstance = null;

    // Offcanvas Instance
    const bsAddEventSidebar = new bootstrap.Offcanvas(addEventSidebar);

    //! TODO: Update Event label and guest code to JS once select removes jQuery dependency
    // Initialize Select2 with custom templates
    if (eventLabel.length) {
      function renderBadges(option) {
        if (!option.id) {
          return option.text;
        }
        var $badge =
          "<span class='badge badge-dot bg-" + $(option.element).data('label') + " me-2'> " + '</span>' + option.text;

        return $badge;
      }
      eventLabel.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: eventLabel.parent(),
        templateResult: renderBadges,
        templateSelection: renderBadges,
        minimumResultsForSearch: -1,
        escapeMarkup: function (es) {
          return es;
        }
      });
    }

    // Render guest avatars
    if (eventGuests.length) {
      function renderGuestAvatar(option) {
        if (!option.id) return option.text;
        return `
    <div class='d-flex flex-wrap align-items-center'>

      ${option.text}
    </div>`;
      }
      eventGuests.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: eventGuests.parent(),
        closeOnSelect: false,
        templateResult: renderGuestAvatar,
        templateSelection: renderGuestAvatar,
        escapeMarkup: function (es) {
          return es;
        }
      });
    }

    // Event start (flatpicker)
    if (eventStartDate) {
      var start = eventStartDate.flatpickr({
        monthSelectorType: 'static',
        static: true,
        enableTime: true,
        // altFormat: 'Y-m-dTH:i:S',
        dateFormat: 'Y-m-d H:i',
        onReady: function (selectedDates, dateStr, instance) {
          if (instance.isMobile) {
            instance.mobileInput.setAttribute('step', null);
          }
        }
      });
    }

    // Event end (flatpicker)
    if (eventEndDate) {
      var end = eventEndDate.flatpickr({
        monthSelectorType: 'static',
        static: true,
        enableTime: true,
        // altFormat: 'Y-m-dTH:i:S',
        dateFormat: 'Y-m-d H:i',
        onReady: function (selectedDates, dateStr, instance) {
          if (instance.isMobile) {
            instance.mobileInput.setAttribute('step', null);
          }
        }
      });
    }

    // Inline sidebar calendar (flatpicker)
    if (inlineCalendar) {
      inlineCalInstance = inlineCalendar.flatpickr({
        monthSelectorType: 'static',
        static: true,
        inline: true
      });
    }

    // Event click function
//     function eventClick(info) {
//       eventToUpdate = info.event;
//       if (eventToUpdate.url) {
//         info.jsEvent.preventDefault();
//         window.open(eventToUpdate.url, '_blank');
//       }
//       bsAddEventSidebar.show();
//       // For update event set offcanvas title text: Update Event
//       if (offcanvasTitle) {
//         offcanvasTitle.innerHTML = 'Update Event';
//       }
//       btnSubmit.innerHTML = 'Update';
//       btnSubmit.classList.add('btn-update-event');
//       btnSubmit.classList.remove('btn-add-event');
//       btnDeleteEvent.classList.remove('d-none');
//
//       eventTitle.value = eventToUpdate.title;
//       start.setDate(eventToUpdate.start, true);
//       eventToUpdate.allDay === true ? (allDaySwitch.checked = true) : (allDaySwitch.checked = false);
//       eventToUpdate.end !== null
//         ? end.setDate(eventToUpdate.end, true)
//         : end.setDate(eventToUpdate.start, true);
//       const calVal = String(eventToUpdate.extendedProps.calendar || '').toLowerCase();
// eventLabel.val(calVal).trigger('change');
//
//         const currentGuestId = eventToUpdate.extendedProps?.guests ?? null;
//         $('#eventGuests').val(currentGuestId ? String(currentGuestId) : '').trigger('change');
//
//       eventToUpdate.extendedProps.description !== undefined
//         ? (eventDescription.value = eventToUpdate.extendedProps.description)
//         : null;
//     }


// Treat null / "null" / "undefined" / "" as empty string
function cleanNullableText(v) {
  if (v === null || v === undefined) return '';
  const s = String(v).trim();
  if (s === '' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') return '';
  return s;
}



function eventClick(info) {
  eventToUpdate = info.event;

  // If the event has a URL, don't auto-navigate when we want to edit.
  // Let users open the link ONLY with Ctrl/Cmd + click.
  if (eventToUpdate.url) {
    if (info.jsEvent.ctrlKey || info.jsEvent.metaKey) {
      // user explicitly wants a new tab
      window.open(eventToUpdate.url, '_blank');
      return; // don't open the editor in this case
    }
    // stop FullCalendar's default link navigation for normal clicks
    info.jsEvent.preventDefault();
  }

  // Switch UI to "Update" mode
  if (offcanvasTitle) offcanvasTitle.innerHTML = 'Update Event';
  btnSubmit.innerHTML = 'Update';
  btnSubmit.classList.add('btn-update-event');
  btnSubmit.classList.remove('btn-add-event');
  btnDeleteEvent.classList.remove('d-none');

  // Fill the form (this sets title, URL, dates, label, guests, description)
  fillFormFromEvent(eventToUpdate, { start, end });

  // Finally show the editor
  bsAddEventSidebar.show();
}

    // Modify sidebar toggler
    function modifyToggler() {
      const fcSidebarToggleButton = document.querySelector('.fc-sidebarToggle-button');
      fcSidebarToggleButton.classList.remove('fc-button-primary');
      fcSidebarToggleButton.classList.add('d-lg-none', 'd-inline-block', 'ps-0');
      while (fcSidebarToggleButton.firstChild) {
        fcSidebarToggleButton.firstChild.remove();
      }
      fcSidebarToggleButton.setAttribute('data-bs-toggle', 'sidebar');
      fcSidebarToggleButton.setAttribute('data-overlay', '');
      fcSidebarToggleButton.setAttribute('data-target', '#app-calendar-sidebar');
      fcSidebarToggleButton.insertAdjacentHTML(
        'beforeend',
        '<i class="icon-base ti tabler-menu-2 icon-lg text-heading"></i>'
      );
    }

    // Filter events by calender
    // function selectedCalendars() {
    //   let selected = [],
    //     filterInputChecked = [].slice.call(document.querySelectorAll('.input-filter:checked'));
    //
    //   filterInputChecked.forEach(item => {
    //     selected.push(item.getAttribute('data-value'));
    //   });
    //
    //   return selected;
    // }
    function selectedCalendars() {
      const boxes = document.querySelectorAll('.input-filter:checked');
      const vals = Array.from(boxes).map(b => (b.dataset.value || '').toLowerCase());
      // لو مفيش أي اختيار، خلّيها كل الفئات
      return vals.length ? vals : ['busy', 'available', 'consultation', 'classes', 'holiday'];
    }
    // --------------------------------------------------------------------------------------------------
    // AXIOS: fetchEvents
    // * This will be called by fullCalendar to fetch events. Also this can be used to refetch events.
    // --------------------------------------------------------------------------------------------------
    // function fetchEvents(info, successCallback) {
    //   let calendars = selectedCalendars();
    //   // We are reading event object from app-calendar-events.js file directly by including that file above app-calendar file.
    //   // You should make an API call, look into above commented API call for reference
    //   let selectedEvents = currentEvents.filter(function (event) {
    //     return calendars.includes(event.extendedProps.calendar.toLowerCase());
    //   });
    //   // if (selectedEvents.length > 0) {
    //   successCallback(selectedEvents);
    //   // }
    // }


    function fetchEvents(info, successCallback, failureCallback) {
      const calendars = selectedCalendars(); // ['busy', ...]
      // استخدمي axios لو موجود، وإلا استخدمي fetch
      if (window.axios) {
        axios.get('/admin/calendar/events', {
          params: { start: info.startStr, end: info.endStr, calendars }
        })
        .then(res => successCallback(res.data))
        .catch(err => failureCallback && failureCallback(err));
      } else {
        const params = new URLSearchParams();
        params.set('start', info.startStr);
        params.set('end', info.endStr);
        calendars.forEach(c => params.append('calendars[]', c));
        fetch('/admin/calendar/events?' + params.toString(), { credentials: 'same-origin' })
          .then(r => r.json()).then(data => successCallback(data))
          .catch(err => failureCallback && failureCallback(err));
      }
    }


    function displayEndForForm(ev) {
      if (ev.allDay && ev.end) {
        const d = new Date(ev.end);
        d.setDate(d.getDate() - 1);
        return d;
      }
      return ev.end || ev.start;
    }

    // عبيّ كل حقول النموذج من حدث FullCalendar
    function fillFormFromEvent(ev, pickers) {
      document.getElementById('eventTitle').value       = ev.title || '';
      document.getElementById('eventURL').value         = cleanNullableText(ev.url);
      // لو عندك حقل location فعّليه هنا
      // document.getElementById('eventLocation').value = ev.extendedProps?.location || '';
      document.getElementById('eventDescription').value = ev.extendedProps?.description || '';

      // Label (Select2): القيم lowercase
      const cal = String(ev.extendedProps?.calendar || 'busy').toLowerCase();
      $('#eventLabel').val(cal).trigger('change'); // set programmatically

      // Guest: قيمة واحدة integer أو null → لازم نص + trigger('change')
      // const gid = ev.extendedProps?.guests ?? null;
      // $('#eventGuests').val(gid ? String(gid) : '').trigger('change');

      const gid = ev.extendedProps?.guests ?? null;

// مهم: حمّلي القائمة حسب الـ Label ثم اعملي preselect للـ guest
loadGuests(cal, gid).then(() => {
  // لا شيء إضافي — preselect تم داخل loadGuests
});

      // All Day
      const allDaySwitch = document.getElementById('allDaySwitch');
      if (allDaySwitch) allDaySwitch.checked = !!ev.allDay;

      // مواعيد البدء/النهاية لِـ flatpickr
      const startDate = ev.start ? new Date(ev.start) : null;
      const endDate   = displayEndForForm(ev);
      if (pickers?.start) pickers.start.setDate(startDate, true);
      if (pickers?.end)   pickers.end.setDate(endDate ? new Date(endDate) : startDate, true);
    }


    // ===== Guests loader (before "Init FullCalendar") =====
    function rebuildGuestsSelect(data, preselectId) {
      const $sel = $('#eventGuests');

      // فضّي الاختيارات القديمة
      $sel.empty();

      // أضِف Placeholder
      const placeholder = new Option('— Select —', '', true, false);
      $sel.append(placeholder);

      // أضِف العناصر الجديدة [{id,text},...]
      (data || []).forEach(item => {
        const opt = new Option(item.text, item.id, false, false);
        $sel.append(opt);
      });

      // فعّل/عطّل حسب وجود عناصر
      const enable = (data && data.length > 0);
 $sel.prop('disabled', !enable).trigger('change.select2');

      // Preselect لو موجود
      if (preselectId != null) {
        $sel.val(String(preselectId)).trigger('change');
      } else {
        $sel.val('').trigger('change');
      }
    }

    // label: 'classes' | 'consultation' | غيرهما
    function loadGuests(label, preselectId) {
      label = (label || '').toLowerCase();
      if (label !== 'classes' && label !== 'consultation') {
        rebuildGuestsSelect([], null); // فاضي
        return Promise.resolve();
      }

      const req = window.axios
        ? axios.get('/admin/calendar/guests', { params: { label } })
        : fetch('/admin/calendar/guests?label=' + encodeURIComponent(label))
            .then(r => r.json());

      return Promise.resolve(req).then(res => {
        const data = window.axios ? (res.data?.results || []) : (res.results || []);
        rebuildGuestsSelect(data, preselectId ?? null);
      }).catch(() => {
        rebuildGuestsSelect([], null);
      });
    }




    // Init FullCalendar
    // ------------------------------------------------
    let calendar = new Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: fetchEvents,
      plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
      editable: true,
      dragScroll: true,
      dayMaxEvents: 2,
      eventResizableFromStart: true,
      customButtons: {
        sidebarToggle: {
          text: 'Sidebar'
        }
      },
      headerToolbar: {
        start: 'sidebarToggle, prev,next, title',
        end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
      },
      direction: direction,
      initialDate: new Date(),
      navLinks: true, // can click day/week names to navigate views
      // eventClassNames: function ({ event: calendarEvent }) {
      //   const colorName = calendarColors[calendarEvent._def.extendedProps.calendar];
      //   // Background Color
      //   return ['bg-label-' + colorName];
      // },
      eventClassNames: function ({ event }) {
  const cal = event.extendedProps?.calendar; // بدلاً من _def.extendedProps
  const key = (cal in calendarColors) ? cal : String(cal || '').toLowerCase();
  const colorName = calendarColors[key] || 'primary';
  return ['bg-label-' + colorName];
},

      dateClick: function (info) {
        // let date = moment(info.date).format('YYYY-MM-DD');
        resetValues();
        bsAddEventSidebar.show();

        // For new event set offcanvas title text: Add Event
        if (offcanvasTitle) {
          offcanvasTitle.innerHTML = 'Add Event';
        }
        btnSubmit.innerHTML = 'Add';
        btnSubmit.classList.remove('btn-update-event');
        btnSubmit.classList.add('btn-add-event');
        btnDeleteEvent.classList.add('d-none');
        // eventStartDate.value = date;
        // eventEndDate.value = date;
        eventStartDate.value = info.dateStr; // جاهزة من FullCalendar
eventEndDate.value   = info.dateStr;
const currentLabel = ($('#eventLabel').val() || '').toLowerCase();
loadGuests(currentLabel, null);
      },
      eventClick: function (info) {
        eventClick(info);
      },
      eventDrop: function(info) {
  const id = info.event.id;
  const payload = {
    title: info.event.title,
    // start: info.event.start ? info.event.start.toISOString() : null,
    // end:   info.event.end   ? info.event.end.toISOString()   : null,
    start: info.event.startStr,
end:   info.event.endStr,
    allDay: info.event.allDay,
    url: (cleanNullableText(info.event.url) || null),
    description: info.event.extendedProps?.description || null,
    calendar: String(info.event.extendedProps?.calendar || '').toLowerCase(),
    guests: (info.event.extendedProps?.guests ?? null)
  };

  const req = window.axios
    ? axios.put(`/admin/calendar/events/${id}`, payload)
    : fetch(`/admin/calendar/events/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });

  // Promise.resolve(req)
  //   .then(() => calendar.refetchEvents())
  //   .catch(() => info.revert());



  Promise.resolve(req)
    .then(() => calendar.refetchEvents())
    .catch(err => {
      if (err?.response?.status === 422) {
        const errors = err.response.data?.errors || {};
        const lines = Object.entries(errors)
          .flatMap(([field, msgs]) => msgs.map(m => `- ${field}: ${m}`));
        alert('Validation errors:\n' + lines.join('\n'));
      }
      info.revert();
    });



},

eventResize: function(info) {
  const id = info.event.id;
  const payload = {
    title: info.event.title,
    // start: info.event.start ? info.event.start.toISOString() : null,
    // end:   info.event.end   ? info.event.end.toISOString()   : null,
    start: info.event.startStr,
   end:   info.event.endStr,
    allDay: info.event.allDay,
    url: (cleanNullableText(info.event.url) || null),
    description: info.event.extendedProps?.description || null,
    calendar: String(info.event.extendedProps?.calendar || '').toLowerCase(),
    guests: (info.event.extendedProps?.guests ?? null)
  };

  const req = window.axios
    ? axios.put(`/admin/calendar/events/${id}`, payload)
    : fetch(`/admin/calendar/events/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });

  // Promise.resolve(req)
  //   .then(() => calendar.refetchEvents())
  //   .catch(() => info.revert());

  Promise.resolve(req)
  .then(() => calendar.refetchEvents())
  .catch(err => {
    if (err?.response?.status === 422) {
      const errors = err.response.data?.errors || {};
      const lines = Object.entries(errors)
        .flatMap(([field, msgs]) => msgs.map(m => `- ${field}: ${m}`));
      alert('Validation errors:\n' + lines.join('\n'));
    }
    info.revert();
  });

},

      datesSet: function () {
        modifyToggler();
      },
      viewDidMount: function () {
        modifyToggler();
      }
    });

    // Render calendar
    calendar.render();
    // Modify sidebar toggler
    modifyToggler();

    const eventForm = document.getElementById('eventForm');
    const fv = FormValidation.formValidation(eventForm, {
      fields: {
        eventTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter event title '
            }
          }
        },
        eventStartDate: {
          validators: {
            notEmpty: {
              message: 'Please enter start date '
            }
          }
        },
        eventEndDate: {
          validators: {
            notEmpty: {
              message: 'Please enter end date '
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: '',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    })
      .on('core.form.valid', function () {
        // Jump to the next step when all fields in the current step are valid
        isFormValid = true;
      })
      .on('core.form.invalid', function () {
        // if fields are invalid
        isFormValid = false;
      });

    // Sidebar Toggle Btn
    if (btnToggleSidebar) {
      // btnToggleSidebar.addEventListener('click', e => {
      //   btnCancel.classList.remove('d-none');
      // });



      btnToggleSidebar.addEventListener('click', e => {
  btnCancel.classList.remove('d-none');
  const currentLabel = ($('#eventLabel').val() || '').toLowerCase();
  loadGuests(currentLabel, null);
});

    }






    // [app-calendar.js] — أضيفي هذا قبل ربط الأحداث بالأزرار
    function buildPayloadFromForm() {
      // عدّلي الـ IDs عشان تطابق عناصر النموذج عندك
      const eventTitle       = document.getElementById('eventTitle');
      const eventStartDate   = document.getElementById('eventStartDate'); // أو start-date input
      const eventEndDate     = document.getElementById('eventEndDate');
      const allDaySwitch     = document.getElementById('allDaySwitch');   // checkbox
      const eventUrl         = document.getElementById('eventURL');
      // const eventLocation    = document.getElementById('eventLocation');
      const eventDescription = document.getElementById('eventDescription');
      const eventLabel       = $('#eventLabel');
      // const eventGuests      = $('#eventGuests');
      const guestVal = $('#eventGuests').val();
const guestId  = guestVal ? parseInt(guestVal, 10) : null;
const cleanUrl = cleanNullableText(eventUrl?.value);

      return {
        title:       eventTitle?.value || '',
        start:       eventStartDate?.value || '',
        end:         eventEndDate?.value || null,
        allDay:      !!(allDaySwitch && allDaySwitch.checked),
        url: (cleanUrl === '' ? null : cleanUrl),
        // location:    eventLocation?.value || null,
        description: eventDescription?.value || null,
        calendar:    (eventLabel?.val?.() || 'busy').toLowerCase(),
        // guests:      (eventGuests?.val?.() || [])
         guests: Number.isFinite(guestId) ? guestId : null
      };
    }

    function addEventFromForm(calendar) {
      const payload = buildPayloadFromForm();
      const req = window.axios
        ? axios.post('/admin/calendar/events', payload)
        : fetch('/admin/calendar/events', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
          });
          return Promise.resolve(req)
            .then(() => calendar.refetchEvents())
            .catch(err => {
              if (err?.response?.status === 422) {
                const errors = err.response.data?.errors || {};
                const lines = Object.entries(errors)
                  .flatMap(([field, msgs]) => msgs.map(m => `- ${field}: ${m}`));
                alert('Validation errors:\n' + lines.join('\n'));
              }
              throw err; // عشان يبان في الـ console برضه
            });

      // return Promise.resolve(req).then(() => calendar.refetchEvents());
    }

    function updateEventFromForm(calendar, eventId) {
      const payload = buildPayloadFromForm();
      const req = window.axios
        ? axios.put(`/admin/calendar/events/${eventId}`, payload)
        : fetch(`/admin/calendar/events/${eventId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
          });

      // return Promise.resolve(req).then(() => calendar.refetchEvents());
      return Promise.resolve(req)
  .then(() => calendar.refetchEvents())
  .catch(err => {
    if (err?.response?.status === 422) {
      const errors = err.response.data?.errors || {};
      const lines = Object.entries(errors)
        .flatMap(([field, msgs]) => msgs.map(m => `- ${field}: ${m}`));
      alert('Validation errors:\n' + lines.join('\n'));
    }
    throw err;
  });

    }

    function removeEventById(calendar, id) {
      const req = window.axios
        ? axios.delete(`/admin/calendar/events/${id}`)
        : fetch(`/admin/calendar/events/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
            credentials: 'same-origin'
          });
          return Promise.resolve(req)
            .then(() => calendar.refetchEvents())
            .catch(err => {
              alert('Delete failed');
              throw err;
            });

      // return Promise.resolve(req).then(() => calendar.refetchEvents());
    }






    // Add Event
    // ------------------------------------------------
    // function addEvent(eventData) {
    //   // ? Add new event data to current events object and refetch it to display on calender
    //   // ? You can write below code to AJAX call success response
    //
    //   currentEvents.push(eventData);
    //   calendar.refetchEvents();
    //
    //   // ? To add event directly to calender (won't update currentEvents object)
    //   // calendar.addEvent(eventData);
    // }

    // Update Event
    // ------------------------------------------------
    // function updateEvent(eventData) {
    //   // ? Update existing event data to current events object and refetch it to display on calender
    //   // ? You can write below code to AJAX call success response
    //   eventData.id = parseInt(eventData.id);
    //   currentEvents[currentEvents.findIndex(el => el.id === eventData.id)] = eventData; // Update event by id
    //   calendar.refetchEvents();
    //
    //   // ? To update event directly to calender (won't update currentEvents object)
    //   // let propsToUpdate = ['id', 'title', 'url'];
    //   // let extendedPropsToUpdate = ['calendar', 'guests', 'location', 'description'];
    //
    //   // updateEventInCalendar(eventData, propsToUpdate, extendedPropsToUpdate);
    // }

    // Remove Event
    // ------------------------------------------------

    // function removeEvent(eventId) {
    //   // ? Delete existing event data to current events object and refetch it to display on calender
    //   // ? You can write below code to AJAX call success response
    //   currentEvents = currentEvents.filter(function (event) {
    //     return event.id != eventId;
    //   });
    //   calendar.refetchEvents();
    //
    //   // ? To delete event directly to calender (won't update currentEvents object)
    //   // removeEventInCalendar(eventId);
    // }

    // (Update Event In Calendar (UI Only)
    // ------------------------------------------------
    const updateEventInCalendar = (updatedEventData, propsToUpdate, extendedPropsToUpdate) => {
      const existingEvent = calendar.getEventById(updatedEventData.id);

      // --- Set event properties except date related ----- //
      // ? Docs: https://fullcalendar.io/docs/Event-setProp
      // dateRelatedProps => ['start', 'end', 'allDay']
      // eslint-disable-next-line no-plusplus
      for (var index = 0; index < propsToUpdate.length; index++) {
        var propName = propsToUpdate[index];
        existingEvent.setProp(propName, updatedEventData[propName]);
      }

      // --- Set date related props ----- //
      // ? Docs: https://fullcalendar.io/docs/Event-setDates
      existingEvent.setDates(updatedEventData.start, updatedEventData.end, {
        allDay: updatedEventData.allDay
      });

      // --- Set event's extendedProps ----- //
      // ? Docs: https://fullcalendar.io/docs/Event-setExtendedProp
      // eslint-disable-next-line no-plusplus
      for (var index = 0; index < extendedPropsToUpdate.length; index++) {
        var propName = extendedPropsToUpdate[index];
        existingEvent.setExtendedProp(propName, updatedEventData.extendedProps[propName]);
      }
    };

    // Remove Event In Calendar (UI Only)
    // ------------------------------------------------
    function removeEventInCalendar(eventId) {
      calendar.getEventById(eventId).remove();
    }

    // Add new event
    // ------------------------------------------------
    // btnSubmit.addEventListener('click', e => {
    //   if (btnSubmit.classList.contains('btn-add-event')) {
    //     if (isFormValid) {
    //       let newEvent = {
    //         id: calendar.getEvents().length + 1,
    //         title: eventTitle.value,
    //         start: eventStartDate.value,
    //         end: eventEndDate.value,
    //         startStr: eventStartDate.value,
    //         endStr: eventEndDate.value,
    //         display: 'block',
    //         extendedProps: {
    //           // location: eventLocation.value,
    //           guests: eventGuests.val(),
    //           calendar: eventLabel.val(),
    //           description: eventDescription.value
    //         }
    //       };
    //       if (eventUrl.value) {
    //         newEvent.url = eventUrl.value;
    //       }
    //       if (allDaySwitch.checked) {
    //         newEvent.allDay = true;
    //       }
    //       addEvent(newEvent);
    //       bsAddEventSidebar.hide();
    //     }
    //   } else {
    //     // Update event
    //     // ------------------------------------------------
    //     if (isFormValid) {
    //       let eventData = {
    //         id: eventToUpdate.id,
    //         title: eventTitle.value,
    //         start: eventStartDate.value,
    //         end: eventEndDate.value,
    //         url: eventUrl.value,
    //         extendedProps: {
    //           // location: eventLocation.value,
    //           guests: eventGuests.val(),
    //           calendar: eventLabel.val(),
    //           description: eventDescription.value
    //         },
    //         display: 'block',
    //         allDay: allDaySwitch.checked ? true : false
    //       };
    //
    //       updateEvent(eventData);
    //       bsAddEventSidebar.hide();
    //     }
    //   }
    // });
    btnSubmit.addEventListener('click', e => {
      e.preventDefault();
      if (!isFormValid) return;

      const isAdd = btnSubmit.classList.contains('btn-add-event');
      if (isAdd) {
        addEventFromForm(calendar).then(() => {
          bsAddEventSidebar.hide();
        });
      } else {
        const id = (eventToUpdate && eventToUpdate.id) ? eventToUpdate.id : null;
        if (!id) return;
        updateEventFromForm(calendar, id).then(() => {
          bsAddEventSidebar.hide();
        });
      }
    });

    // Call removeEvent function
    btnDeleteEvent.addEventListener('click', e => {
      // removeEvent(parseInt(eventToUpdate.id));
      // // eventToUpdate.remove();
      // bsAddEventSidebar.hide();



      const id = (eventToUpdate && eventToUpdate.id) ? eventToUpdate.id : null;
        if (!id) return;
        removeEventById(calendar, id).then(() => {
          if (typeof bsAddEventSidebar !== 'undefined') bsAddEventSidebar.hide();
        });

    });

    // Reset event form inputs values
    // ------------------------------------------------
    function resetValues() {
      eventEndDate.value = '';
      eventUrl.value = '';
      eventStartDate.value = '';
      eventTitle.value = '';
      // eventLocation.value = '';
      allDaySwitch.checked = false;
      eventGuests.val('').trigger('change');
      eventDescription.value = '';
    }

    // When modal hides reset input values
    addEventSidebar.addEventListener('hidden.bs.offcanvas', function () {
      resetValues();
    });

    // Hide left sidebar if the right sidebar is open
    btnToggleSidebar.addEventListener('click', e => {
      if (offcanvasTitle) {
        offcanvasTitle.innerHTML = 'Add Event';
      }
      btnSubmit.innerHTML = 'Add';
      btnSubmit.classList.remove('btn-update-event');
      btnSubmit.classList.add('btn-add-event');
      btnDeleteEvent.classList.add('d-none');
      appCalendarSidebar.classList.remove('show');
      appOverlay.classList.remove('show');
    });



    // أي تغيير في Label يحمّل قائمة الضيوف المناسبة
    $('#eventLabel').on('change', function () {
      const label = ($(this).val() || '').toLowerCase();
      loadGuests(label, null); // بدون preselect أثناء الإضافة
    });


    // Calender filter functionality
    // ------------------------------------------------
    if (selectAll) {
      selectAll.addEventListener('click', e => {
        if (e.currentTarget.checked) {
          document.querySelectorAll('.input-filter').forEach(c => (c.checked = 1));
        } else {
          document.querySelectorAll('.input-filter').forEach(c => (c.checked = 0));
        }
        calendar.refetchEvents();
      });
    }

    if (filterInputs) {
      filterInputs.forEach(item => {
        item.addEventListener('click', () => {
          document.querySelectorAll('.input-filter:checked').length < document.querySelectorAll('.input-filter').length
            ? (selectAll.checked = false)
            : (selectAll.checked = true);
          calendar.refetchEvents();
        });
      });
    }

    // Jump to date on sidebar(inline) calendar change
    inlineCalInstance.config.onChange.push(function (date) {
      calendar.changeView(calendar.view.type, moment(date[0]).format('YYYY-MM-DD'));
      modifyToggler();
      appCalendarSidebar.classList.remove('show');
      appOverlay.classList.remove('show');
    });
  })();
});
