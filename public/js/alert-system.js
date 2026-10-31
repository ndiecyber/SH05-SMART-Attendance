(() => {
  const icons = {
    success: '<path d="m5 12 4 4L19 6"/>',
    error: '<path d="M12 8v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>',
    warning: '<path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>',
    info: '<path d="M12 16v-4m0-4h.01M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/>',
    reset: '<path d="M4 4v6h6M5.6 15a7 7 0 1 0 .2-6.8L4 10"/>',
    delete: '<path d="M3 6h18m-2 0-1 14H6L5 6m4 0V4h6v2m-5 4v6m4-6v6"/>',
    logout: '<path d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
  };
  const tones = {
    success: { icon: 'text-emerald-600 bg-emerald-50', edge: 'border-emerald-500', progress: 'bg-emerald-500', title: 'Berhasil' },
    error: { icon: 'text-rose-600 bg-rose-50', edge: 'border-rose-500', progress: 'bg-rose-500', title: 'Terjadi kesalahan' },
    warning: { icon: 'text-amber-700 bg-amber-50', edge: 'border-amber-500', progress: 'bg-amber-500', title: 'Perlu diperhatikan' },
    info: { icon: 'text-blue-700 bg-blue-50', edge: 'border-blue-500', progress: 'bg-blue-500', title: 'Informasi' },
  };
  const region = document.getElementById('lexaToastRegion');
  const modal = document.getElementById('lexaConfirmRoot');
  const dialog = document.getElementById('lexaConfirmDialog');
  const titleElement = document.getElementById('lexaConfirmTitle');
  const messageElement = document.getElementById('lexaConfirmMessage');
  const detailElement = document.getElementById('lexaConfirmDetail');
  const iconElement = document.getElementById('lexaConfirmIcon');
  const cancelButton = document.getElementById('lexaConfirmCancel');
  const acceptButton = document.getElementById('lexaConfirmAccept');
  const closeButton = document.getElementById('lexaConfirmClose');
  if (!region || !modal || !dialog) return;

  let activeConfirmation = null;
  let previousFocus = null;
  let toastSequence = 0;
  const maxToasts = 4;

  const iconSvg = (type) => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.classList.add('h-5', 'w-5');
    svg.innerHTML = icons[type] || icons.info;
    return svg;
  };

  const toast = ({ type = 'info', title, message = '', duration } = {}) => {
    const tone = tones[type] ? type : 'info';
    const style = tones[tone];
    const card = document.createElement('article');
    const icon = document.createElement('span');
    const content = document.createElement('div');
    const heading = document.createElement('p');
    const description = document.createElement('p');
    const close = document.createElement('button');
    const progress = document.createElement('span');
    const timeout = duration ?? (tone === 'error' ? 8000 : 4500);
    const id = ++toastSequence;

    card.dataset.toastId = String(id);
    card.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    card.className = 'pointer-events-auto relative flex translate-x-4 gap-3 overflow-hidden rounded-2xl border border-slate-200 border-l-4 bg-white p-4 opacity-0 shadow-xl transition duration-300';
    card.classList.add(style.edge);
    icon.className = `flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${style.icon}`;
    icon.append(iconSvg(tone));
    content.className = 'min-w-0 flex-1 pr-1';
    heading.className = 'text-sm font-bold text-slate-900';
    heading.textContent = title || style.title;
    description.className = 'mt-1 break-words text-sm leading-5 text-slate-600';
    description.textContent = message;
    description.hidden = !message;
    close.type = 'button';
    close.className = 'self-start rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500';
    close.setAttribute('aria-label', 'Tutup pemberitahuan');
    close.innerHTML = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>';
    progress.className = `absolute inset-x-0 bottom-0 h-0.5 origin-left ${style.progress}`;
    progress.style.animation = `lexa-toast-progress ${timeout}ms linear forwards`;
    content.append(heading, description);
    card.append(icon, content, close, progress);
    region.append(card);
    while (region.children.length > maxToasts) dismissToast(region.firstElementChild);
    requestAnimationFrame(() => card.classList.remove('translate-x-4', 'opacity-0'));

    const timer = window.setTimeout(() => dismissToast(card), timeout);
    const dismiss = () => {
      window.clearTimeout(timer);
      card.classList.add('translate-x-4', 'opacity-0');
      window.setTimeout(() => card.remove(), 250);
    };
    close.addEventListener('click', dismiss);
    return { id, dismiss };
  };

  function dismissToast(card) {
    if (!card || card.dataset.closing === 'true') return;
    card.dataset.closing = 'true';
    card.classList.add('translate-x-4', 'opacity-0');
    window.setTimeout(() => card.remove(), 250);
  }

  const closeConfirmation = (answer) => {
    if (!activeConfirmation) return;
    if (activeConfirmation.busy && answer === false) return;
    const current = activeConfirmation;
    activeConfirmation = null;
    modal.dataset.open = 'false';
    dialog.classList.add('scale-95', 'opacity-0');
    window.setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
      if (previousFocus instanceof HTMLElement && previousFocus.isConnected) previousFocus.focus();
      previousFocus = null;
    }, 180);
    current.resolve(answer);
  };

  const confirm = (options = {}) => new Promise((resolve) => {
    if (activeConfirmation) {
      resolve(false);
      return;
    }
    activeConfirmation = { resolve, options, busy: false };
    previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    const type = options.type || (options.danger ? 'error' : 'info');
    titleElement.textContent = options.title || 'Konfirmasi tindakan';
    messageElement.textContent = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    detailElement.textContent = options.detail || '';
    detailElement.classList.toggle('hidden', !options.detail);
    iconElement.className = `flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${tones[type]?.icon || tones.info.icon}`;
    iconElement.replaceChildren(iconSvg(options.icon || type));
    acceptButton.textContent = options.confirmText || 'Konfirmasi';
    acceptButton.className = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60';
    acceptButton.classList.add(...(options.danger ? ['bg-rose-600', 'hover:bg-rose-700', 'focus:ring-rose-500'] : ['bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500']));
    cancelButton.textContent = options.cancelText || 'Batal';
    cancelButton.disabled = false;
    closeButton.disabled = false;
    acceptButton.disabled = false;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
      modal.dataset.open = 'true';
      dialog.classList.remove('scale-95', 'opacity-0');
      (options.initialFocus === 'confirm' ? acceptButton : cancelButton).focus();
    });
  });

  window.LexaAlerts = {
    toast,
    success: (message, title = 'Berhasil') => toast({ type: 'success', title, message }),
    error: (message, title = 'Terjadi kesalahan') => toast({ type: 'error', title, message }),
    warning: (message, title = 'Perlu diperhatikan') => toast({ type: 'warning', title, message }),
    info: (message, title = 'Informasi') => toast({ type: 'info', title, message }),
    confirm,
  };

  cancelButton.addEventListener('click', () => closeConfirmation(false));
  closeButton.addEventListener('click', () => closeConfirmation(false));
  modal.addEventListener('click', (event) => {
    if (event.target === modal) closeConfirmation(false);
  });
  acceptButton.addEventListener('click', async () => {
    if (!activeConfirmation || activeConfirmation.busy) return;
    const current = activeConfirmation;
    current.busy = true;
    acceptButton.disabled = true;
    cancelButton.disabled = true;
    closeButton.disabled = true;
    acceptButton.replaceChildren(document.createTextNode('Memproses…'));
    try {
      if (typeof current.options.onConfirm === 'function') await current.options.onConfirm();
      closeConfirmation(true);
    } catch (error) {
      current.busy = false;
      acceptButton.disabled = false;
      cancelButton.disabled = false;
      closeButton.disabled = false;
      acceptButton.textContent = current.options.confirmText || 'Konfirmasi';
      toast({ type: 'error', title: 'Tindakan gagal', message: error?.message || 'Terjadi kesalahan. Silakan coba kembali.' });
    }
  });

  document.addEventListener('keydown', (event) => {
    if (!activeConfirmation) return;
    if (event.key === 'Escape' && !activeConfirmation.busy) {
      event.preventDefault();
      closeConfirmation(false);
      return;
    }
    if (event.key !== 'Tab') return;
    const focusable = [...dialog.querySelectorAll('button:not(:disabled)')];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('form[data-confirm]')) return;
    if (form.dataset.lexaConfirmed === 'true') {
      delete form.dataset.lexaConfirmed;
      return;
    }
    event.preventDefault();
    const submitter = event.submitter instanceof HTMLElement ? event.submitter : form.querySelector('[type="submit"]');
    const defaultMessage = form.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    let defaults = {
      title: 'Konfirmasi tindakan',
      message: defaultMessage,
      confirmText: 'Ya, lanjutkan',
      danger: form.dataset.confirmTone === 'danger' || /hapus|permanen/i.test(defaultMessage),
    };
    if (/reset|default/i.test(defaultMessage)) {
      defaults = {
        ...defaults,
        title: 'Reset Pengaturan?',
        message: 'Apakah Anda yakin ingin mengembalikan preferensi tampilan, pengaturan sidebar, dan preferensi notifikasi ke nilai default?',
        detail: 'Data profil, password, foto, kontak darurat, riwayat attendance, dan riwayat notifikasi tidak akan dihapus.',
        confirmText: 'Ya, Reset Pengaturan',
      };
    }
    const options = {
      ...defaults,
      title: form.dataset.confirmTitle || defaults.title,
      message: form.dataset.confirmMessage || defaults.message,
      detail: form.dataset.confirmDetail || defaults.detail,
      confirmText: form.dataset.confirmText || defaults.confirmText,
      cancelText: form.dataset.cancelText || defaults.cancelText,
      danger: form.dataset.confirmTone === 'danger' || defaults.danger,
      icon: form.dataset.confirmIcon || (/reset|default/i.test(defaultMessage) ? 'reset' : /hapus|delete/i.test(defaultMessage) ? 'delete' : /keluar|logout/i.test(defaultMessage) ? 'logout' : undefined),
    };
    if (options.danger) options.type = 'error';
    confirm(options).then((accepted) => {
      if (!accepted || !form.isConnected) return;
      if (submitter) {
        submitter.disabled = true;
        submitter.dataset.originalHtml = submitter.innerHTML;
        submitter.textContent = 'Memproses…';
      }
      form.dataset.lexaConfirmed = 'true';
      form.requestSubmit(submitter instanceof HTMLElement ? submitter : undefined);
      window.setTimeout(() => {
        if (!form.isConnected) return;
        delete form.dataset.lexaConfirmed;
        if (submitter) {
          submitter.disabled = false;
          submitter.innerHTML = submitter.dataset.originalHtml || 'Konfirmasi';
        }
      }, 1000);
    });
  }, true);

  const feedbackElement = document.getElementById('lexaAlertFeedback');
  if (feedbackElement) {
    try {
      const feedback = JSON.parse(feedbackElement.textContent || '{}');
      if (feedback.success) toast({ type: 'success', title: 'Berhasil disimpan', message: feedback.success });
      if (feedback.error) toast({ type: 'error', title: 'Tindakan gagal', message: feedback.error });
      const errors = feedback.errors || {};
      const entries = Object.entries(errors);
      const translate = (message) => {
        const knownMessages = {
          'The new password field is required when current password is present.': 'Kata sandi baru wajib diisi setelah kata sandi saat ini dimasukkan.',
          'The current password field is required when new password is present.': 'Kata sandi saat ini wajib diisi untuk mengubah kata sandi.',
          'The new password confirmation field is required when new password is present.': 'Konfirmasi kata sandi baru wajib diisi.',
          'The new password confirmation does not match.': 'Konfirmasi kata sandi baru tidak cocok.',
          'The password is incorrect.': 'Kata sandi saat ini tidak sesuai.',
        };
        const text = String(message);
        if (knownMessages[text]) return knownMessages[text];
        const requiredMatch = text.match(/^The (.+) field is required\.$/);
        if (requiredMatch) return `Kolom ${requiredMatch[1].replaceAll('_', ' ')} wajib diisi.`;
        const emailMatch = text.match(/^The (.+) field must be a valid email address\.$/);
        if (emailMatch) return `Kolom ${emailMatch[1].replaceAll('_', ' ')} harus menggunakan alamat email yang valid.`;
        const minMatch = text.match(/^The (.+) field must be at least (\d+) characters\.$/);
        if (minMatch) return `Kolom ${minMatch[1].replaceAll('_', ' ')} harus berisi minimal ${minMatch[2]} karakter.`;
        return text;
      };
      entries.forEach(([key, messages]) => {
        const fieldName = key.replace(/\[([^\]]+)\]/g, '.$1');
        const field = [...document.querySelectorAll('[name]')].find((candidate) => candidate.name.replace(/\[([^\]]+)\]/g, '.$1') === fieldName);
        if (!field) return;
        field.setAttribute('aria-invalid', 'true');
        field.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
        const container = field.closest('[data-field-wrapper], .settings-field, label') || field.parentElement;
        if (!container || container.querySelector(`[data-lexa-error="${CSS.escape(fieldName)}"]`)) return;
        const error = document.createElement('span');
        error.dataset.lexaError = fieldName;
        error.className = 'mt-1 block text-xs font-medium leading-5 text-rose-600';
        error.textContent = translate(messages?.[0] || 'Periksa kembali nilai pada kolom ini.');
        container.append(error);
      });
      if (entries.length) toast({ type: 'error', title: 'Periksa kembali data', message: `Ada ${entries.length} kolom yang perlu diperbaiki. Pesan validasi ditampilkan di bawah masing-masing kolom.`, duration: 8000 });
    } catch {
      toast({ type: 'error', title: 'Terjadi kesalahan', message: 'Pesan validasi tidak dapat ditampilkan.' });
    }
  }
})();
