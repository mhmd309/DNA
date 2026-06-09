class SearchableSelect {
  constructor(container) {
    this.container = container;
    this.input = container.querySelector('.ss-input');
    this.hidden = container.querySelector('.ss-hidden');
    this.dropdown = container.querySelector('.ss-dropdown');
    this.apiUrl = container.dataset.api;
    this.selectedText = container.querySelector('.ss-selected-text');
    this.timeout = null;

    this.input.addEventListener('input', () => this.search());
    this.input.addEventListener('focus', () => this.search());
    document.addEventListener('click', (e) => {
      if (!container.contains(e.target)) this.dropdown.classList.add('hidden');
    });
  }

  search() {
    clearTimeout(this.timeout);
    const q = this.input.value.trim();
    this.timeout = setTimeout(async () => {
      try {
        const res = await fetch(`${this.apiUrl}?q=${encodeURIComponent(q)}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        this.render(data.data || []);
      } catch { /* silent */ }
    }, 300);
  }

  render(items) {
    if (!items.length) {
      this.dropdown.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">لا توجد نتائج</div>';
    } else {
      this.dropdown.innerHTML = items.map(item => `
                <button type="button" class="w-full text-right px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0 ss-option"
                    data-id="${item.id}" data-text="${item.family_name} (${item.family_code})">
                    <span class="font-medium">${item.family_name}</span>
                    <span class="text-gray-500 text-xs mr-2">${item.family_code}</span>
                </button>
            `).join('');

      this.dropdown.querySelectorAll('.ss-option').forEach(btn => {
        btn.addEventListener('click', () => {
          this.hidden.value = btn.dataset.id;
          this.input.value = btn.dataset.text;
          this.selectedText.textContent = btn.dataset.text;
          this.dropdown.classList.add('hidden');
        });
      });
    }
    this.dropdown.classList.remove('hidden');
  }

  setValue(id, text) {
    this.hidden.value = id;
    this.input.value = text;
    this.selectedText.textContent = text;
  }

  clear() {
    this.hidden.value = '';
    this.input.value = '';
    this.selectedText.textContent = '';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.searchable-select').forEach(el => {
    el.searchableSelect = new SearchableSelect(el);
  });
});
