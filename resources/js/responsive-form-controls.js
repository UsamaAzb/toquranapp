const tabletQuery = window.matchMedia('(max-width: 1024px), (pointer: coarse)');
const enhancedFieldSelector = 'select:not([data-tq-native-control]), input[type="date"]:not([data-tq-native-control])';
const closeEventName = 'tq-responsive-control:close';
const enhancedControls = new Map();
const valueSyncIntervalMs = 200;

function escapeSelector(value) {
  if (window.CSS?.escape) {
    return window.CSS.escape(value);
  }

  return String(value).replace(/["\\]/g, '\\$&');
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

  if (field.tagName === 'SELECT' && field.size > 1) {
    return false;
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

function clearResolvedValidation(field) {
  if (!field.checkValidity?.()) {
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
  button.innerHTML = `
    <span class="tq-responsive-control__value"></span>
    <span class="tq-responsive-control__chevron" aria-hidden="true">v</span>
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

function clampMonthByBounds(dateInput, visibleMonth) {
  const minDate = parseDateValue(dateInput.min);
  const maxDate = parseDateValue(dateInput.max);

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
  const minDate = parseDateValue(dateInput.min);
  const maxDate = parseDateValue(dateInput.max);
  return Boolean((minDate && normalizedDate < minDate) || (maxDate && normalizedDate > maxDate));
}

function renderDateCalendar(dateInput, menu, valueNode, state) {
  state.visibleMonth = clampMonthByBounds(dateInput, state.visibleMonth);
  const selectedDate = parseDateValue(dateInput.value);
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

  ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
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
      dateInput.value = value;
      clearResolvedValidation(dateInput);
      valueNode.textContent = readableDate(dateInput.value);
      dispatchValueEvents(dateInput);
      closeAllControls();
      renderDateCalendar(dateInput, menu, valueNode, state);
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
      state.visibleMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      dateInput.value = formatDateValue(today);
      clearResolvedValidation(dateInput);
      valueNode.textContent = readableDate(dateInput.value);
      dispatchValueEvents(dateInput);
      closeAllControls();
    }
  });

  footer.appendChild(todayButton);

  if (!dateInput.required) {
    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.className = 'tq-responsive-control__calendar-action';
    clearButton.textContent = 'Clear';
    clearButton.addEventListener('click', () => {
      dateInput.value = '';
      clearResolvedValidation(dateInput);
      valueNode.textContent = readableDate(dateInput.value);
      dispatchValueEvents(dateInput);
      closeAllControls();
      renderDateCalendar(dateInput, menu, valueNode, state);
    });
    footer.appendChild(clearButton);
  }

  previousButton.addEventListener('click', event => {
    event.stopPropagation();
    state.visibleMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() - 1, 1);
    renderDateCalendar(dateInput, menu, valueNode, state);
  });

  nextButton.addEventListener('click', event => {
    event.stopPropagation();
    state.visibleMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 1);
    renderDateCalendar(dateInput, menu, valueNode, state);
  });

  menu.append(header, grid, footer);
}

function enhanceDateInput(dateInput) {
  const selectedDate = parseDateValue(dateInput.value) || new Date();
  const state = {
    visibleMonth: new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
  };
  const wrapper = createWrapper(dateInput, 'date');
  const button = createButton(dateInput);
  const valueNode = button.querySelector('.tq-responsive-control__value');
  const menu = document.createElement('div');
  menu.className = 'tq-responsive-control__menu';

  if (dateInput.id) {
    menu.id = `${dateInput.id}-tq-menu`;
  }

  wrapper.append(button, menu);

  let lastSignature = '';

  const sync = () => {
    const currentDate = parseDateValue(dateInput.value);

    if (currentDate) {
      state.visibleMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    }

    lastSignature = controlSignature(dateInput);
    valueNode.textContent = readableDate(dateInput.value);
    syncCommonState(dateInput, wrapper, button);
    renderDateCalendar(dateInput, menu, valueNode, state);
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

  dateInput.addEventListener('change', sync);
  dateInput.addEventListener('input', sync);
  dateInput.addEventListener('invalid', () => syncCommonState(dateInput, wrapper, button));

  const observer = new MutationObserver(sync);
  observer.observe(dateInput, {
    attributes: true,
    attributeFilter: ['disabled', 'readonly', 'required', 'min', 'max', 'class', 'aria-invalid', 'value']
  });

  const syncTimer = window.setInterval(() => {
    if (!dateInput.isConnected || !wrapper.isConnected) {
      cleanupDisconnectedControls();

      return;
    }

    if (controlSignature(dateInput) !== lastSignature) {
      sync();
    }
  }, valueSyncIntervalMs);

  registerControl(dateInput, { wrapper, button, observer, sync, syncTimer });
}

function enhanceField(field) {
  if (!tabletQuery.matches || !isEnhanceableField(field)) {
    return;
  }

  if (field.tagName === 'SELECT') {
    enhanceSelect(field);
  } else if (field.matches('input[type="date"]')) {
    enhanceDateInput(field);
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
