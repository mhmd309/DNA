const App = {
  baseUrl: window.DNA_BASE_URL || '/DNA',

  init() {
    this.initTheme();
    this.initSidebar();
    this.initGlobalSearch();
    this.initDeleteButtons();
    this.initLogoutConfirm();
    this.initSearchInputs();
    this.initNumericInputs();
    this.initFileInputs();
    this.initImagePreviews();
  },

  initImagePreviews() {
    const popup = document.getElementById('imagePreviewPopup');
    const previewImg = document.getElementById('previewImage');
    const closeBtn = document.getElementById('closeImagePreview');
    if (!popup || !previewImg || !closeBtn) return;
    document.addEventListener('click', (e) => {
      let src = null;
      if (e.target.tagName === 'IMG' && e.target.src && e.target.src !== window.location.href) {
        src = e.target.src;
      } else if (e.target.closest('[data-preview-image]')) {
        src = e.target.closest('[data-preview-image]').dataset.previewImage;
      }

      if (src) {
        previewImg.src = src;
        popup.classList.remove('hidden');
      }
    });

    // Close preview
    const closePreview = () => {
      popup.classList.add('hidden');
      previewImg.src = '';
    };

    closeBtn.addEventListener('click', closePreview);
    popup.addEventListener('click', (e) => {
      if (e.target === popup) {
        closePreview();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !popup.classList.contains('hidden')) {
        closePreview();
      }
    });
  },

  initTheme() {
    const saved = localStorage.getItem('dna_theme') || 'light';
    this.setTheme(saved);

    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
      btn.addEventListener('click', () => {
        const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        this.setTheme(next);
        localStorage.setItem('dna_theme', next);
      });
    });
  },

  setTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    document.querySelectorAll('[data-theme-icon]').forEach(icon => {
      icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });
  },

  isSidebarOpen() {
    return !document.documentElement.classList.contains('sidebar-collapsed');
  },

  setSidebarOpen(open) {
    document.documentElement.classList.toggle('sidebar-collapsed', !open);
    localStorage.setItem('dna_sidebar', open ? 'open' : 'closed');

    const overlay = document.getElementById('sidebarOverlay');
    const icon = document.getElementById('sidebarToggleIcon');
    if (overlay) {
      if (open && window.innerWidth < 1024) {
        overlay.classList.remove('hidden');
      } else {
        overlay.classList.add('hidden');
      }
    }
    if (icon) {
      icon.className = open ? 'fas fa-times text-lg' : 'fas fa-bars text-lg';
    }
  },

  toggleSidebar() {
    this.setSidebarOpen(!this.isSidebarOpen());
  },

  initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    const saved = localStorage.getItem('dna_sidebar');
    const open = saved !== 'closed';
    this.setSidebarOpen(open);

    toggle?.addEventListener('click', () => this.toggleSidebar());
    overlay?.addEventListener('click', () => this.setSidebarOpen(false));

    window.addEventListener('resize', () => {
      const overlayEl = document.getElementById('sidebarOverlay');
      if (!overlayEl) return;
      if (this.isSidebarOpen() && window.innerWidth < 1024) {
        overlayEl.classList.remove('hidden');
      } else {
        overlayEl.classList.add('hidden');
      }
    });

    const path = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
      const href = link.getAttribute('href');
      if (href) {
        // First remove active from all links
        link.classList.remove('active');

        // Check for exact match
        if (path === href) {
          link.classList.add('active');
        }
      }
    });

    // Now handle subpages - for example, if we're on /dna-tests/123, we might want /dna-tests to be active
    // But in our case, we have two separate DNA links, so we need special handling
    const dnaTestsPath = this.baseUrl + '/dna-tests';
    const dnaComparePath = this.baseUrl + '/dna-tests/compare';

    if (path === dnaComparePath) {
      // On compare page, only activate compare link
      const compareLink = Array.from(document.querySelectorAll('.sidebar-link')).find(link => link.getAttribute('href') === dnaComparePath);
      if (compareLink) {
        compareLink.classList.add('active');
      }
    } else if (path.startsWith(dnaTestsPath + '/')) {
      // On other DNA subpages, activate the main DNA tests link
      const dnaTestsLink = Array.from(document.querySelectorAll('.sidebar-link')).find(link => link.getAttribute('href') === dnaTestsPath);
      if (dnaTestsLink) {
        dnaTestsLink.classList.add('active');
      }
    }
  },

  initNumericInputs() {
    document.querySelectorAll('[data-national-id]').forEach(input => {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 14);
      });
    });
    document.querySelectorAll('[data-phone]').forEach(input => {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 11);
      });
    });
  },

  initGlobalSearch() {
    const input = document.getElementById('globalSearch');
    const results = document.getElementById('searchResults');
    if (!input || !results) return;

    let timeout;
    input.addEventListener('input', () => {
      clearTimeout(timeout);
      const q = input.value.trim();
      if (q.length < 2) {
        results.classList.add('hidden');
        return;
      }
      timeout = setTimeout(async () => {
        const result = await Api.request(`${this.baseUrl}/api/search?q=${encodeURIComponent(q)}`);
        if (result.data.success) {
          this.renderSearchResults(result.data.results || [], results);
        } else {
          results.classList.add('hidden');
        }
      }, 300);
    });

    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !results.contains(e.target)) {
        results.classList.add('hidden');
      }
    });
  },

  renderSearchResults(items, container) {
    if (!items.length) {
      container.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">لا توجد نتائج</div>';
      container.classList.remove('hidden');
      return;
    }

    container.innerHTML = items.map(item => `
            <a href="${item.url}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                    <i class="fas ${item.icon} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">${this.escape(item.title)}</div>
                    <div class="text-xs text-gray-500 truncate">${this.escape(item.subtitle)} · ${this.escape(item.label)}</div>
                </div>
            </a>
        `).join('');
    container.classList.remove('hidden');
  },

  initDeleteButtons() {
    document.querySelectorAll('[data-delete]').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const url = btn.dataset.delete;
        const name = btn.dataset.name || 'هذا السجل';

        const confirmed = await Confirm.show(`هل أنت متأكد من حذف ${name}؟`);
        if (!confirmed) return;

        const result = await Api.request(url, { method: 'POST' });
        const data = result.data;
        if (data.success) {
          Toast.show(data.message, 'success');
          setTimeout(() => location.reload(), 1000);
        } else if (!handleApiFeedback(data)) {
          Toast.show(data.message || 'تعذر حذف السجل', 'error');
        }
      });
    });
  },

  initLogoutConfirm() {
    document.querySelectorAll('[data-confirm-logout]').forEach(link => {
      link.addEventListener('click', async (e) => {
        e.preventDefault();
        const ok = await Confirm.show('هل تريد تسجيل الخروج؟', 'تسجيل الخروج');
        if (ok) window.location.href = link.getAttribute('href');
      });
    });
  },

  initSearchInputs() {
    document.querySelectorAll('[data-instant-search]').forEach(form => {
      const input = form.querySelector('input[name="search"]');
      if (!input) return;

      // Submit only on Enter key press or blur
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          form.submit();
        }
      });

      input.addEventListener('blur', () => {
        // Only submit if value changed from initial
        const initialValue = input.dataset.initialValue || '';
        if (input.value.trim() !== initialValue.trim()) {
          form.submit();
        }
      });

      // Store initial value to avoid unnecessary submits
      input.dataset.initialValue = input.value;
    });
  },

  initFileInputs() {
    document.querySelectorAll('input[type="file"]').forEach(input => {
      if (input.dataset.fileUi === '1') return;
      input.dataset.fileUi = '1';

      const nextEl = input.nextElementSibling;
      if (nextEl?.classList?.contains('file-input-info')) {
        nextEl.remove();
      }
      const maybePreview = input.parentElement?.querySelector('.file-input-preview');
      maybePreview?.remove();

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition';
      btn.innerHTML = `<i class="fas fa-upload"></i><span>${input.multiple ? 'رفع ملفات' : 'رفع ملف'}</span>`;

      input.classList.add('sr-only');
      input.tabIndex = -1;
      input.insertAdjacentElement('afterend', btn);

      btn.addEventListener('click', () => input.click());

      let previewEl = null;
      let previewUrl = null;
      const cleanupUrl = () => {
        if (previewUrl) {
          URL.revokeObjectURL(previewUrl);
          previewUrl = null;
        }
      };

      const accept = (input.getAttribute('accept') || '').toLowerCase();
      const isImageCapable = accept.includes('image') || accept.includes('.jpg') || accept.includes('.jpeg') || accept.includes('.png') || accept.includes('.webp');

      const findPreviewImg = () => {
        let el = btn.nextElementSibling;
        while (el) {
          if (el.tagName === 'IMG') return el;
          el = el.nextElementSibling;
        }
        return null;
      };

      if (isImageCapable) {
        previewEl = findPreviewImg();
        if (!previewEl) {
          previewEl = document.createElement('img');
          previewEl.className = 'file-input-preview mt-2 hidden';
          btn.insertAdjacentElement('afterend', previewEl);
        }
        if (!previewEl.dataset.originalSrc) {
          previewEl.dataset.originalSrc = previewEl.getAttribute('src') || '';
        }
      }

      input.addEventListener('change', () => {
        if (!previewEl) return;
        cleanupUrl();

        const file = (input.files && input.files[0]) ? input.files[0] : null;
        if (!file) {
          const original = previewEl.dataset.originalSrc || '';
          if (original) {
            previewEl.src = original;
            previewEl.classList.remove('hidden');
          } else {
            previewEl.classList.add('hidden');
            previewEl.removeAttribute('src');
          }
          return;
        }

        if ((file.type || '').startsWith('image/')) {
          previewUrl = URL.createObjectURL(file);
          previewEl.src = previewUrl;
          previewEl.classList.remove('hidden');
        }
      });
    });
  },

  escape(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  },

  calcAge(birthDate, targetEl) {
    if (!birthDate || !targetEl) return;
    const birth = new Date(birthDate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    targetEl.value = age >= 0 ? age : '';
  }
};

const Api = {
  statusMessages: {
    400: 'طلب غير صالح',
    401: 'انتهت جلستك، يرجى تسجيل الدخول مرة أخرى',
    403: 'انتهت صلاحية الصفحة أو لا تملك صلاحية لهذا الإجراء',
    404: 'العنصر المطلوب غير موجود',
    422: 'يرجى مراجعة البيانات المدخلة',
    429: 'طلبات كثيرة، انتظر قليلاً ثم حاول مجدداً',
    500: 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً',
    502: 'الخادم غير متاح حالياً',
    503: 'الخدمة غير متاحة مؤقتاً',
  },

  statusMessage(status) {
    return this.statusMessages[status] || `حدث خطأ غير متوقع (${status})`;
  },

  networkMessage() {
    if (!navigator.onLine) {
      return 'لا يوجد اتصال بالإنترنت، تحقق من الشبكة وحاول مجدداً';
    }
    return 'تعذر الاتصال بالخادم، تحقق من الاتصال وحاول مجدداً';
  },

  async parseResponse(res) {
    const contentType = (res.headers.get('content-type') || '').toLowerCase();

    if (contentType.includes('application/json')) {
      try {
        const data = await res.json();
        if (!res.ok && !data.message) {
          data.message = this.statusMessage(res.status);
        }
        return { ok: res.ok, status: res.status, data };
      } catch {
        return {
          ok: false,
          status: res.status,
          data: { success: false, message: this.statusMessage(res.status) },
        };
      }
    }

    if (res.status === 401) {
      return {
        ok: false,
        status: 401,
        data: {
          success: false,
          message: this.statusMessage(401),
          redirect: `${App.baseUrl}/login`,
        },
      };
    }

    return {
      ok: false,
      status: res.status,
      data: { success: false, message: this.statusMessage(res.status) },
    };
  },

  async request(url, options = {}) {
    const headers = { ...(options.headers || {}) };
    if (!headers['X-Requested-With']) {
      headers['X-Requested-With'] = 'XMLHttpRequest';
    }

    const method = (options.method || 'GET').toUpperCase();
    if (method === 'POST') {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (csrfToken && !headers['X-CSRF-TOKEN']) {
        headers['X-CSRF-TOKEN'] = csrfToken;
      }
    }

    try {
      const res = await fetch(url, { ...options, headers });
      return await this.parseResponse(res);
    } catch {
      return {
        ok: false,
        status: 0,
        data: { success: false, message: this.networkMessage() },
      };
    }
  },
};

function handleApiFeedback(data, type = 'error') {
  if (!data?.message) return false;

  Toast.show(data.message, data.redirect ? 'warning' : type);
  if (data.redirect) {
    setTimeout(() => { window.location.href = data.redirect; }, 1200);
    return true;
  }
  return false;
}

const Toast = {
  container: null,

  getContainer() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
    return this.container;
  },

  show(message, type = 'info', duration = 4000) {
    const colors = {
      success: 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300',
      error: 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300',
      warning: 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300',
      info: 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300',
    };
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };

    const toast = document.createElement('div');
    toast.className = `toast flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg ${colors[type] || colors.info}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span class="text-sm font-medium">${message}</span>`;

    this.getContainer().appendChild(toast);
    setTimeout(() => {
      toast.classList.add('toast-out');
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }
};

const Confirm = {
  show(message, yesText = 'نعم، احذف') {
    return new Promise((resolve) => {
      const overlay = document.createElement('div');
      overlay.className = 'fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-4';
      overlay.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 fade-in">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
                        </div>
                        <p class="text-gray-700 dark:text-gray-200">${message}</p>
                    </div>
                    <div class="flex gap-3 justify-center">
                        <button class="confirm-yes px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition">${yesText}</button>
                        <button class="confirm-no px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl font-medium transition">إلغاء</button>
                    </div>
                </div>
            `;
      document.body.appendChild(overlay);
      overlay.querySelector('.confirm-yes').onclick = () => { overlay.remove(); resolve(true); };
      overlay.querySelector('.confirm-no').onclick = () => { overlay.remove(); resolve(false); };
    });
  }
};

async function submitForm(form, options = {}) {
  const btn = form.querySelector('[type="submit"]');
  const originalText = btn?.innerHTML;
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';
  }

  try {
    const formData = new FormData(form);
    const result = await Api.request(form.action, {
      method: 'POST',
      body: formData,
    });
    const data = result.data;

    if (data.success) {
      Toast.show(data.message, 'success');
      if (data.redirect) {
        setTimeout(() => window.location.href = data.redirect, 1000);
      } else if (options.onSuccess) {
        options.onSuccess(data);
      }
    } else if (handleApiFeedback(data)) {
      // تم التعامل مع إعادة التوجيه (مثل انتهاء الجلسة)
    } else {
      Toast.show(data.message || 'تعذر إتمام العملية', 'error');
      if (data.errors) {
        Object.entries(data.errors).forEach(([field, msg]) => {
          const el = form.querySelector(`[name="${field}"], [data-error="${field}"]`);
          if (el) {
            el.classList.add('border-red-500');
            let errEl = el.parentElement.querySelector('.field-error');
            if (!errEl) {
              errEl = document.createElement('p');
              errEl.className = 'field-error text-red-500 text-xs mt-1';
              el.parentElement.appendChild(errEl);
            }
            errEl.textContent = msg;
          }
        });
      }
    }
  } catch {
    Toast.show(Api.networkMessage(), 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }
}

document.addEventListener('DOMContentLoaded', () => App.init());
