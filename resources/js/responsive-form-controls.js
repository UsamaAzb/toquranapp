if (!window.__toquranResponsiveControlsBooted) {
  window.__toquranResponsiveControlsBooted = true;

  const tabletQuery = window.matchMedia('(max-width: 1024px), (pointer: coarse)');
  const enhancedFieldSelector = [
    'select:not([data-tq-native-control])',
    'input[type="date"]:not([data-tq-native-control])',
    'input[type="time"]:not([data-tq-native-control])',
    'input[type="datetime-local"]:not([data-tq-native-control])'
  ].join(', ');
  const closeEventName = 'tq-responsive-control:close';
  const enhancedControls = new Map();
  const valueSyncIntervalMs = 200;
  const missingAttributeToken = '__tq_missing__';
  const pluginManagedSelectClasses = [
    'select2',
    'select2-hidden-accessible',
    'tq-assignment-select',
    'share-project-select'
  ];

  function escapeSelector(value) {
    if (window.CSS?.escape) {
      return window.CSS.escape(value);
    }

    const text = String(value);
    let escaped = '';

    for (let index = 0; index < text.length; index += 1) {
      const code = text.charCodeAt(index);
      const character = text.charAt(index);
      const isDigit = code >= 48 && code <= 57;
      const isLetter = (code >= 65 && code <= 90) || (code >= 97 && code <= 122);

      if (code === 0) {
        escaped += '\uFFFD';
      } else if (
        (code >= 1 && code <= 31) ||
        code === 127 ||
        (index === 0 && isDigit) ||
        (index === 1 && isDigit && text.charCodeAt(0) === 45)
      ) {
        escaped += `\\${code.toString(16)} `;
      } else if (index === 0 && code === 45 && text.length === 1) {
        escaped += '\\-';
      } else if (code >= 128 || code === 45 || code === 95 || isDigit || isLetter) {
        escaped += character;
      } else {
        escaped += `\\${character}`;
      }
    }

    return escaped;
  }

  function isPluginManagedSelect(field) {
    return Boolean(
      field?.tagName === 'SELECT' &&
      (
        pluginManagedSelectClasses.some(className => field.classList.contains(className)) ||
        field.hasAttribute('data-select2-id') ||
        field.nextElementSibling?.classList?.contains('select2')
      )
    );
  }

  function isEnhanceableField(field) {
    if (!(field instanceof HTMLElement)) {
      return false;
    }

    if (field.dataset.tqResponsiveControl === 'ready' || field.dataset.tqResponsiveControl === 'skip') {
      return false;
    }

    if (field.closest('[data-tq-responsive-controls="off"]')) {
      return false;
    }

    if (field.tagName === 'SELECT') {
      if (field.size > 1) {
        return false;
      }

      if (isPluginManagedSelect(field)) {
        return false;
      }
    }

    return true;
  }

  function fieldLabel(field) {
    if (field.id) {
      const explicitLabel = document.querySelector(`label[for="${escapeSelector(field.id)}"]`);

      if (explicitLabel?.textContent?.trim()) {
        return explicitLabel.textContent.trim();
      }
    }

    return field.getAttribute('aria-label') || field.getAttribute('placeholder') || 'Choose';
  }

  function dispatchValueEvents(field) {
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function feedbackHasMessage(feedback) {
    return Boolean(
      feedback?.classList?.contains('invalid-feedback') &&
      feedback.textContent.trim() !== ''
    );
  }

  function hasServerValidationFeedback(field) {
    const details = enhancedControls.get(field);
    const candidates = [
      field.nextElementSibling,
      details?.wrapper?.nextElementSibling
    ];

    if (field.name) {
      candidates.push(field.closest('form')?.querySelector(`[data-error-for="${escapeSelector(field.name)}"]`));
    }

    return candidates.some(feedbackHasMessage);
  }

  function clearResolvedValidation(field) {
    if (!field.checkValidity?.()) {
      return;
    }

    if (hasServerValidationFeedback(field)) {
      return;
    }

    field.classList.remove('is-invalid');

    if (field.getAttribute('aria-invalid') === 'true') {
      field.setAttribute('aria-invalid', 'false');
    }
  }

  function controlSignature(field) {
    if (field.tagName === 'SELECT') {
      return JSON.stringify(
        Array.from(field.options).map(option => [
          option.value,
          option.textContent,
          option.selected,
          option.disabled
        ])
      );
    }

    return String(field.value);
  }

  function preserveHiddenFieldAccessibility(field) {
    field.dataset.tqOriginalTabindex = field.hasAttribute('tabindex')
      ? field.getAttribute('tabindex')
      : missingAttributeToken;
    field.dataset.tqOriginalAriaHidden = field.hasAttribute('aria-hidden')
      ? field.getAttribute('aria-hidden')
      : missingAttributeToken;
    field.tabIndex = -1;
    field.setAttribute('aria-hidden', 'true');
  }

  function restoreHiddenFieldAccessibility(field) {
    const originalTabindex = field.dataset.tqOriginalTabindex;
    const originalAriaHidden = field.dataset.tqOriginalAriaHidden;

    if (originalTabindex === missingAttributeToken) {
      field.removeAttribute('tabindex');
    } else if (originalTabindex !== undefined) {
      field.setAttribute('tabindex', originalTabindex);
    }

    if (originalAriaHidden === missingAttributeToken) {
      field.removeAttribute('aria-hidden');
    } else if (originalAriaHidden !== undefined) {
      field.setAttribute('aria-hidden', originalAriaHidden);
    }

    delete field.dataset.tqOriginalTabindex;
    delete field.dataset.tqOriginalAriaHidden;
  }

  function registerControl(field, details) {
    enhancedControls.set(field, details);
  }

  function unregisterControl(field, removeWrapper = false) {
    const details = enhancedControls.get(field);

    if (!details) {
      return;
    }

    details.observer?.disconnect();
    window.clearInterval(details.syncTimer);
    details.wrapper?.classList.remove('is-open');

    if (removeWrapper) {
      details.wrapper?.remove();
    }

    enhancedControls.delete(field);

    if (field.isConnected) {
      field.classList.remove('tq-native-control--enhanced');
      restoreHiddenFieldAccessibility(field);
      delete field.dataset.tqResponsiveControl;
    }
  }

  function disableEnhancements() {
    Array.from(enhancedControls.keys()).forEach(field => {
      unregisterControl(field, true);
    });
  }

  function cleanupDisconnectedControls() {
    enhancedControls.forEach((details, field) => {
      if (!field.isConnected) {
        details.wrapper?.remove();
        unregisterControl(field);

        return;
      }

      if (!details.wrapper?.isConnected) {
        unregisterControl(field);
        enhanceField(field);
      }
    });
  }

  function createButton(field) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'tq-responsive-control__button';
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-label', fieldLabel(field));
    button.innerHTML = `
      <span class="tq-responsive-control__value"></span>
      <span class="tq-responsive-control__chevron icon-base ti tabler-chevron-down" aria-hidden="true"></span>
    `;

    if (field.id) {
      button.setAttribute('aria-controls', `${field.id}-tq-menu`);
    }

    return button;
  }

  function createWrapper(field, type) {
    const wrapper = document.createElement('div');
    wrapper.className = `tq-responsive-control tq-responsive-control--${type}`;
    wrapper.dataset.tqResponsiveProxy = 'true';

    field.classList.add('tq-native-control--enhanced');
    field.dataset.tqResponsiveControl = 'ready';
    preserveHiddenFieldAccessibility(field);
    field.insertAdjacentElement('afterend', wrapper);

    return wrapper;
  }

  function syncCommonState(field, wrapper, button) {
    if (!wrapper || !button) {
      return;
    }

    const disabled = field.disabled || field.hasAttribute('readonly');
    const invalid =
      field.classList.contains('is-invalid') ||
      field.getAttribute('aria-invalid') === 'true' ||
      hasServerValidationFeedback(field) ||
      field.matches(':invalid');

    button.disabled = disabled;
    wrapper.classList.toggle('is-disabled', disabled);
    wrapper.classList.toggle('is-invalid', invalid);
    button.setAttribute('aria-disabled', disabled ? 'true' : 'false');
  }

  function closeAllControls(exceptWrapper = null) {
    document.querySelectorAll('.tq-responsive-control.is-open').forEach(wrapper => {
      if (wrapper !== exceptWrapper) {
        wrapper.classList.remove('is-open');
        wrapper.querySelector('.tq-responsive-control__button')?.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function selectedSelectLabel(select) {
    const selected = Array.from(select.selectedOptions).filter(option => option.value !== '');

    if (selected.length === 0) {
      const placeholder = select.querySelector('option[value=""]') || select.options[0];
      return placeholder?.textContent?.trim() || fieldLabel(select);
    }

    if (select.multiple) {
      return selected.length === 1 ? selected[0].textContent.trim() : `${selected.length} selected`;
    }

    return selected[0].textContent.trim();
  }

  function buildSelectMenu(select, menu, valueNode, wrapper, button) {
    menu.innerHTML = '';
    menu.setAttribute('role', 'listbox');
    menu.setAttribute('aria-multiselectable', select.multiple ? 'true' : 'false');

    Array.from(select.options).forEach(option => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'tq-responsive-control__option';
      item.setAttribute('role', 'option');
      item.dataset.value = option.value;
      item.disabled = option.disabled;
      item.setAttribute('aria-selected', option.selected ? 'true' : 'false');

      item.innerHTML = `
        <span class="tq-responsive-control__option-mark" aria-hidden="true"></span>
        <span class="tq-responsive-control__option-label"></span>
      `;
      item.querySelector('.tq-responsive-control__option-label').textContent = option.textContent.trim();

      item.addEventListener('click', () => {
        if (select.multiple) {
          option.selected = !option.selected;
        } else {
          select.value = option.value;
        }

        clearResolvedValidation(select);
        valueNode.textContent = selectedSelectLabel(select);
        buildSelectMenu(select, menu, valueNode, wrapper, button);
        syncCommonState(select, wrapper, button);
        dispatchValueEvents(select);

        if (!select.multiple) {
          closeAllControls();
        }
      });

      menu.appendChild(item);
    });
  }

  function enhanceSelect(select) {
    const wrapper = createWrapper(select, 'select');
    const button = createButton(select);
    const valueNode = button.querySelector('.tq-responsive-control__value');
    const menu = document.createElement('div');
    menu.className = 'tq-responsive-control__menu';

    if (select.id) {
      menu.id = `${select.id}-tq-menu`;
    }

    wrapper.append(button, menu);

    let lastSignature = '';

    const sync = () => {
      lastSignature = controlSignature(select);
      valueNode.textContent = selectedSelectLabel(select);
      syncCommonState(select, wrapper, button);
      buildSelectMenu(select, menu, valueNode, wrapper, button);
    };

    sync();

    button.addEventListener('click', () => {
      if (button.disabled) {
        return;
      }

      const willOpen = !wrapper.classList.contains('is-open');
      closeAllControls(willOpen ? wrapper : null);
      wrapper.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    select.addEventListener('change', sync);
    select.addEventListener('input', sync);
    select.addEventListener('invalid', () => syncCommonState(select, wrapper, button));

    const observer = new MutationObserver(sync);
    observer.observe(select, {
      attributes: true,
      childList: true,
      subtree: true,
      characterData: true,
      attributeFilter: ['disabled', 'class', 'aria-invalid', 'selected', 'value']
    });

    const syncTimer = window.setInterval(() => {
      if (!select.isConnected || !wrapper.isConnected) {
        cleanupDisconnectedControls();

        return;
      }

      if (isPluginManagedSelect(select)) {
        unregisterControl(select, true);

        return;
      }

      if (controlSignature(select) !== lastSignature) {
        sync();
      }
    }, valueSyncIntervalMs);

    registerControl(select, { wrapper, button, observer, sync, syncTimer });
  }

  function parseDateValue(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) {
      return null;
    }

    const [year, month, day] = value.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return Number.isNaN(date.getTime()) ? null : date;
  }

  function formatDateValue(date) {
    const year = String(date.getFullYear());
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function parseTimeValue(value) {
    const match = /^(\d{2}):(\d{2})(?::(\d{2}))?$/.exec(value || '');

    if (!match) {
      return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);
    const seconds = Number(match[3] || 0);

    if (hours > 23 || minutes > 59 || seconds > 59) {
      return null;
    }

    return {
      hours,
      minutes,
      seconds,
      hasSeconds: match[3] !== undefined
    };
  }

  function formatTimeValue(time, includeSeconds = false) {
    const hours = String(time.hours).padStart(2, '0');
    const minutes = String(time.minutes).padStart(2, '0');
    const seconds = String(time.seconds || 0).padStart(2, '0');

    return includeSeconds ? `${hours}:${minutes}:${seconds}` : `${hours}:${minutes}`;
  }

  function timeToSeconds(value) {
    const time = parseTimeValue(value);

    if (!time) {
      return null;
    }

    return (time.hours * 3600) + (time.minutes * 60) + time.seconds;
  }

  function secondsToTimeValue(seconds, includeSeconds = false) {
    const normalized = Math.max(0, Math.min(seconds, 86399));
    const hours = Math.floor(normalized / 3600);
    const minutes = Math.floor((normalized % 3600) / 60);
    const remainingSeconds = normalized % 60;

    return formatTimeValue({ hours, minutes, seconds: remainingSeconds }, includeSeconds);
  }

  function parseDateTimeValue(value) {
    const match = /^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}(?::\d{2})?)$/.exec(value || '');

    if (!match) {
      return null;
    }

    const date = parseDateValue(match[1]);
    const time = parseTimeValue(match[2]);

    return date && time ? { date, time, dateValue: match[1], timeValue: match[2] } : null;
  }

  function formatDateTimeValue(dateValue, timeValue) {
    return dateValue && timeValue ? `${dateValue}T${timeValue}` : '';
  }

  function readableDate(value) {
    const date = parseDateValue(value);

    if (!date) {
      return 'Select date';
    }

    return date.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  function readableTime(value) {
    const time = parseTimeValue(value);

    if (!time) {
      return 'Select time';
    }

    const date = new Date(2000, 0, 1, time.hours, time.minutes, time.seconds);

    return date.toLocaleTimeString(undefined, {
      hour: 'numeric',
      minute: '2-digit'
    });
  }

  function readableDateTime(value) {
    const dateTime = parseDateTimeValue(value);

    if (!dateTime) {
      return 'Select date and time';
    }

    return `${readableDate(dateTime.dateValue)} - ${readableTime(dateTime.timeValue)}`;
  }

  function fieldDateValue(field) {
    if (field.type === 'datetime-local') {
      return parseDateTimeValue(field.value)?.dateValue || '';
    }

    return field.value || '';
  }

  function fieldTimeValue(field) {
    if (field.type === 'datetime-local') {
      return parseDateTimeValue(field.value)?.timeValue || '';
    }

    return field.value || '';
  }

  function defaultTimeValue(field) {
    const minTime = field.type === 'datetime-local'
      ? parseDateTimeValue(field.min)?.timeValue
      : field.min;

    return parseTimeValue(minTime) ? minTime : '09:00';
  }

  function clampMonthByBounds(dateInput, visibleMonth) {
    const minDate = dateInput.type === 'datetime-local'
      ? parseDateTimeValue(dateInput.min)?.date
      : parseDateValue(dateInput.min);
    const maxDate = dateInput.type === 'datetime-local'
      ? parseDateTimeValue(dateInput.max)?.date
      : parseDateValue(dateInput.max);

    if (minDate && visibleMonth < new Date(minDate.getFullYear(), minDate.getMonth(), 1)) {
      return new Date(minDate.getFullYear(), minDate.getMonth(), 1);
    }

    if (maxDate && visibleMonth > new Date(maxDate.getFullYear(), maxDate.getMonth(), 1)) {
      return new Date(maxDate.getFullYear(), maxDate.getMonth(), 1);
    }

    return visibleMonth;
  }

  function isDateDisabled(dateInput, date) {
    const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const minDate = dateInput.type === 'datetime-local'
      ? parseDateTimeValue(dateInput.min)?.date
      : parseDateValue(dateInput.min);
    const maxDate = dateInput.type === 'datetime-local'
      ? parseDateTimeValue(dateInput.max)?.date
      : parseDateValue(dateInput.max);

    return Boolean((minDate && normalizedDate < minDate) || (maxDate && normalizedDate > maxDate));
  }

  function timeStepSeconds(input) {
    if (input.step && input.step !== 'any') {
      const parsed = Number(input.step);

      if (Number.isFinite(parsed) && parsed > 0) {
        return Math.max(60, Math.min(parsed, 3600));
      }
    }

    return 900;
  }

  function timeOptionValues(input, dateValue = '') {
    const includeSeconds = Boolean(parseTimeValue(fieldTimeValue(input))?.hasSeconds);
    const step = timeStepSeconds(input);
    const options = [];

    for (let seconds = 0; seconds < 86400; seconds += step) {
      options.push(secondsToTimeValue(seconds, includeSeconds));
    }

    const current = fieldTimeValue(input);

    if (parseTimeValue(current) && !options.includes(current)) {
      options.push(current);
    }

    return options
      .filter(value => !isTimeDisabled(input, value, dateValue))
      .sort((first, second) => (timeToSeconds(first) || 0) - (timeToSeconds(second) || 0));
  }

  function isTimeDisabled(input, timeValue, dateValue = '') {
    const selectedSeconds = timeToSeconds(timeValue);

    if (selectedSeconds === null) {
      return true;
    }

    if (input.type === 'datetime-local') {
      const candidateValue = formatDateTimeValue(dateValue, timeValue);
      const minValue = input.min || '';
      const maxValue = input.max || '';

      return Boolean(
        (minValue && candidateValue && candidateValue < minValue) ||
        (maxValue && candidateValue && candidateValue > maxValue)
      );
    }

    const minSeconds = timeToSeconds(input.min);
    const maxSeconds = timeToSeconds(input.max);

    return Boolean(
      (minSeconds !== null && selectedSeconds < minSeconds) ||
      (maxSeconds !== null && selectedSeconds > maxSeconds)
    );
  }

  function setTemporalValue(input, value, valueNode, state, keepOpen = false) {
    input.value = value;
    clearResolvedValidation(input);
    syncTemporalLabel(input, valueNode);
    dispatchValueEvents(input);

    const dateValue = fieldDateValue(input);
    const date = parseDateValue(dateValue);

    if (date) {
      state.visibleMonth = new Date(date.getFullYear(), date.getMonth(), 1);
    }

    if (!keepOpen) {
      closeAllControls();
    }
  }

  function syncTemporalLabel(input, valueNode) {
    if (input.type === 'time') {
      valueNode.textContent = readableTime(input.value);
    } else if (input.type === 'datetime-local') {
      valueNode.textContent = readableDateTime(input.value);
    } else {
      valueNode.textContent = readableDate(input.value);
    }
  }

  function renderDateCalendar(dateInput, menu, valueNode, state, options = {}) {
    state.visibleMonth = clampMonthByBounds(dateInput, state.visibleMonth);
    const selectedDate = parseDateValue(fieldDateValue(dateInput));
    const monthStart = new Date(state.visibleMonth.getFullYear(), state.visibleMonth.getMonth(), 1);
    const firstGridDate = new Date(monthStart);
    firstGridDate.setDate(firstGridDate.getDate() - firstGridDate.getDay());

    menu.innerHTML = '';
    menu.className = 'tq-responsive-control__menu tq-responsive-control__calendar';

    const header = document.createElement('div');
    header.className = 'tq-responsive-control__calendar-header';

    const previousButton = document.createElement('button');
    previousButton.type = 'button';
    previousButton.className = 'tq-responsive-control__calendar-nav';
    previousButton.textContent = '<';
    previousButton.dataset.action = 'previous-month';
    previousButton.setAttribute('aria-label', 'Previous month');

    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.className = 'tq-responsive-control__calendar-nav';
    nextButton.textContent = '>';
    nextButton.dataset.action = 'next-month';
    nextButton.setAttribute('aria-label', 'Next month');

    const title = document.createElement('div');
    title.className = 'tq-responsive-control__calendar-title';
    title.textContent = monthStart.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });

    header.append(previousButton, title, nextButton);

    const grid = document.createElement('div');
    grid.className = 'tq-responsive-control__calendar-grid';

    const baseSunday = new Date(2024, 0, 7);
    Array.from({ length: 7 }, (_, offset) => {
      const date = new Date(baseSunday);
      date.setDate(baseSunday.getDate() + offset);
      return date.toLocaleDateString(undefined, { weekday: 'short' });
    }).forEach(day => {
      const heading = document.createElement('div');
      heading.className = 'tq-responsive-control__calendar-day-heading';
      heading.textContent = day;
      grid.appendChild(heading);
    });

    for (let index = 0; index < 42; index += 1) {
      const date = new Date(firstGridDate);
      date.setDate(firstGridDate.getDate() + index);
      const option = document.createElement('button');
      option.type = 'button';
      option.className = 'tq-responsive-control__calendar-day';
      option.textContent = String(date.getDate());

      const isCurrentMonth = date.getMonth() === monthStart.getMonth();
      const value = formatDateValue(date);
      const selected = selectedDate && value === formatDateValue(selectedDate);
      const disabled = isDateDisabled(dateInput, date);

      option.dataset.date = value;
      option.classList.toggle('is-muted', !isCurrentMonth);
      option.classList.toggle('is-selected', Boolean(selected));
      option.disabled = disabled;
      option.setAttribute('aria-pressed', selected ? 'true' : 'false');

      option.addEventListener('click', () => {
        if (dateInput.type === 'datetime-local') {
          const timeValue = fieldTimeValue(dateInput) || defaultTimeValue(dateInput);
          setTemporalValue(dateInput, formatDateTimeValue(value, timeValue), valueNode, state, true);
          renderDateTimeMenu(dateInput, menu, valueNode, state);
          return;
        }

        setTemporalValue(dateInput, value, valueNode, state);
        renderDateCalendar(dateInput, menu, valueNode, state, options);
      });

      grid.appendChild(option);
    }

    const footer = document.createElement('div');
    footer.className = 'tq-responsive-control__calendar-footer';

    const todayButton = document.createElement('button');
    todayButton.type = 'button';
    todayButton.className = 'tq-responsive-control__calendar-action';
    todayButton.textContent = 'Today';
    todayButton.addEventListener('click', () => {
      const today = new Date();

      if (!isDateDisabled(dateInput, today)) {
        const todayValue = formatDateValue(today);
        state.visibleMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        if (dateInput.type === 'datetime-local') {
          setTemporalValue(
            dateInput,
            formatDateTimeValue(todayValue, fieldTimeValue(dateInput) || defaultTimeValue(dateInput)),
            valueNode,
            state,
            true
          );
          renderDateTimeMenu(dateInput, menu, valueNode, state);
          return;
        }

        setTemporalValue(dateInput, todayValue, valueNode, state);
      }
    });

    footer.appendChild(todayButton);

    if (!dateInput.required) {
      const clearButton = document.createElement('button');
      clearButton.type = 'button';
      clearButton.className = 'tq-responsive-control__calendar-action';
      clearButton.textContent = 'Clear';
      clearButton.addEventListener('click', () => {
        setTemporalValue(dateInput, '', valueNode, state);
        renderDateCalendar(dateInput, menu, valueNode, state, options);
      });
      footer.appendChild(clearButton);
    }

    previousButton.addEventListener('click', event => {
      event.stopPropagation();
      state.visibleMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() - 1, 1);
      dateInput.type === 'datetime-local'
        ? renderDateTimeMenu(dateInput, menu, valueNode, state)
        : renderDateCalendar(dateInput, menu, valueNode, state, options);
    });

    nextButton.addEventListener('click', event => {
      event.stopPropagation();
      state.visibleMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 1);
      dateInput.type === 'datetime-local'
        ? renderDateTimeMenu(dateInput, menu, valueNode, state)
        : renderDateCalendar(dateInput, menu, valueNode, state, options);
    });

    menu.append(header, grid, footer);
  }

  function renderTimeOptions(input, container, valueNode, state, dateValue = '') {
    const selectedTime = fieldTimeValue(input);
    const list = document.createElement('div');
    list.className = 'tq-responsive-control__time-list';
    list.setAttribute('role', 'listbox');

    timeOptionValues(input, dateValue).forEach(timeValue => {
      const option = document.createElement('button');
      option.type = 'button';
      option.className = 'tq-responsive-control__time-option';
      option.textContent = readableTime(timeValue);
      option.dataset.value = timeValue;
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', selectedTime === timeValue ? 'true' : 'false');

      option.addEventListener('click', () => {
        const nextValue = input.type === 'datetime-local'
          ? formatDateTimeValue(dateValue || fieldDateValue(input) || formatDateValue(new Date()), timeValue)
          : timeValue;

        setTemporalValue(input, nextValue, valueNode, state);
      });

      list.appendChild(option);
    });

    container.appendChild(list);
  }

  function renderTimeMenu(input, menu, valueNode, state) {
    menu.innerHTML = '';
    menu.className = 'tq-responsive-control__menu tq-responsive-control__time-menu';
    renderTimeOptions(input, menu, valueNode, state);

    if (!input.required && input.value) {
      const footer = document.createElement('div');
      footer.className = 'tq-responsive-control__calendar-footer';

      const clearButton = document.createElement('button');
      clearButton.type = 'button';
      clearButton.className = 'tq-responsive-control__calendar-action';
      clearButton.textContent = 'Clear';
      clearButton.addEventListener('click', () => {
        setTemporalValue(input, '', valueNode, state);
        renderTimeMenu(input, menu, valueNode, state);
      });

      footer.appendChild(clearButton);
      menu.appendChild(footer);
    }
  }

  function renderDateTimeMenu(input, menu, valueNode, state) {
    renderDateCalendar(input, menu, valueNode, state);

    const dateValue = fieldDateValue(input);
    const timeSection = document.createElement('div');
    timeSection.className = 'tq-responsive-control__datetime-time';

    const heading = document.createElement('div');
    heading.className = 'tq-responsive-control__datetime-time-heading';
    heading.textContent = 'Time';
    timeSection.appendChild(heading);

    if (dateValue) {
      renderTimeOptions(input, timeSection, valueNode, state, dateValue);
    } else {
      const empty = document.createElement('div');
      empty.className = 'tq-responsive-control__time-empty';
      empty.textContent = 'Choose a date first.';
      timeSection.appendChild(empty);
    }

    menu.appendChild(timeSection);
  }

  function enhanceTemporalInput(input) {
    const selectedDate = input.type === 'datetime-local'
      ? parseDateTimeValue(input.value)?.date
      : parseDateValue(input.value);
    const state = {
      visibleMonth: selectedDate
        ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
        : new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    };
    const wrapper = createWrapper(input, input.type === 'datetime-local' ? 'datetime' : input.type);
    const button = createButton(input);
    const valueNode = button.querySelector('.tq-responsive-control__value');
    const menu = document.createElement('div');
    menu.className = 'tq-responsive-control__menu';

    if (input.id) {
      menu.id = `${input.id}-tq-menu`;
    }

    wrapper.append(button, menu);

    let lastSignature = '';

    const render = () => {
      if (input.type === 'time') {
        renderTimeMenu(input, menu, valueNode, state);
      } else if (input.type === 'datetime-local') {
        renderDateTimeMenu(input, menu, valueNode, state);
      } else {
        renderDateCalendar(input, menu, valueNode, state);
      }
    };

    const sync = () => {
      const currentDate = input.type === 'datetime-local'
        ? parseDateTimeValue(input.value)?.date
        : parseDateValue(input.value);

      if (currentDate) {
        state.visibleMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
      }

      lastSignature = controlSignature(input);
      syncTemporalLabel(input, valueNode);
      syncCommonState(input, wrapper, button);
      render();
    };

    sync();

    button.addEventListener('click', () => {
      if (button.disabled) {
        return;
      }

      const willOpen = !wrapper.classList.contains('is-open');
      closeAllControls(willOpen ? wrapper : null);
      wrapper.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    input.addEventListener('change', sync);
    input.addEventListener('input', sync);
    input.addEventListener('invalid', () => syncCommonState(input, wrapper, button));

    const observer = new MutationObserver(sync);
    observer.observe(input, {
      attributes: true,
      attributeFilter: ['disabled', 'readonly', 'required', 'min', 'max', 'step', 'class', 'aria-invalid', 'value']
    });

    const syncTimer = window.setInterval(() => {
      if (!input.isConnected || !wrapper.isConnected) {
        cleanupDisconnectedControls();

        return;
      }

      if (controlSignature(input) !== lastSignature) {
        sync();
      }
    }, valueSyncIntervalMs);

    registerControl(input, { wrapper, button, observer, sync, syncTimer });
  }

  function enhanceField(field) {
    if (!tabletQuery.matches || !isEnhanceableField(field)) {
      return;
    }

    if (field.tagName === 'SELECT') {
      enhanceSelect(field);
    } else if (field.matches('input[type="date"], input[type="time"], input[type="datetime-local"]')) {
      enhanceTemporalInput(field);
    }
  }

  function enhanceFields(root = document) {
    root.querySelectorAll?.(enhancedFieldSelector).forEach(enhanceField);
  }

  function syncEnhancementsForViewport() {
    if (tabletQuery.matches) {
      enhanceFields();
    } else {
      disableEnhancements();
    }
  }

  document.addEventListener('click', event => {
    if (!event.target.closest('.tq-responsive-control')) {
      closeAllControls();
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      closeAllControls();
    }
  });

  document.addEventListener(closeEventName, () => closeAllControls());

  document.addEventListener('invalid', event => {
    const details = enhancedControls.get(event.target);

    if (!details) {
      return;
    }

    event.preventDefault();
    details.sync?.();
    details.wrapper.scrollIntoView({ block: 'center', inline: 'nearest' });

    window.requestAnimationFrame(() => {
      details.button.focus({ preventScroll: true });
    });
  }, true);

  document.addEventListener('focusin', event => {
    const details = enhancedControls.get(event.target);

    if (details) {
      details.button.focus({ preventScroll: true });
    }
  });

  document.addEventListener('reset', event => {
    window.setTimeout(() => {
      event.target.querySelectorAll?.(enhancedFieldSelector).forEach(field => {
        enhancedControls.get(field)?.sync?.();
      });
    }, 0);
  }, true);

  document.addEventListener('hidden.bs.modal', event => {
    event.target.querySelectorAll?.('.tq-responsive-control.is-open').forEach(wrapper => {
      wrapper.classList.remove('is-open');
      wrapper.querySelector('.tq-responsive-control__button')?.setAttribute('aria-expanded', 'false');
    });
  });

  function initResponsiveControls() {
    syncEnhancementsForViewport();

    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
          if (!(node instanceof HTMLElement)) {
            return;
          }

          if (node.matches?.(enhancedFieldSelector)) {
            enhanceField(node);
          }

          enhanceFields(node);
        });

        if (mutation.removedNodes.length > 0) {
          cleanupDisconnectedControls();
        }
      });
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (tabletQuery.addEventListener) {
    tabletQuery.addEventListener('change', () => syncEnhancementsForViewport());
  } else if (tabletQuery.addListener) {
    tabletQuery.addListener(() => syncEnhancementsForViewport());
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initResponsiveControls);
  } else {
    initResponsiveControls();
  }
}
