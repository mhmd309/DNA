class SearchableSelect {
  constructor(container) {
    this.container = container;
    this.input = container.querySelector('.ss-input');
    this.hidden = container.querySelector('.ss-hidden');
    this.dropdown = container.querySelector('.ss-dropdown');
    this.apiUrl = container.dataset.api;
    this.selectedText = container.querySelector('.ss-selected-text');
    this.timeout = null;
    this.staticData = null;
    
    // Check for static data
    const dataAttr = container.dataset.static;
    if (dataAttr) {
      try {
        this.staticData = JSON.parse(dataAttr);
      } catch (e) {
        this.staticData = null;
      }
    }

    this.input.addEventListener('input', () => this.search());
    this.input.addEventListener('focus', () => this.search());
    document.addEventListener('click', (e) => {
      if (!container.contains(e.target)) this.dropdown.classList.add('hidden');
    });
  }

  // Normalize item to have id, text, subtext
  normalizeItem(item) {
    return {
      id: item.id,
      text: item.text || item.family_name || item.person_name || item.name || '',
      subtext: item.subtext || item.family_code || item.sample_date || ''
    };
  }

  search() {
    clearTimeout(this.timeout);
    const q = this.input.value.trim().toLowerCase();
    
    if (this.staticData) {
      // Filter static data locally
      this.timeout = setTimeout(() => {
        const filtered = this.staticData.filter(item => {
          const normalized = this.normalizeItem(item);
          return normalized.text.toLowerCase().includes(q) || normalized.subtext.toLowerCase().includes(q);
        });
        this.render(filtered);
      }, 100);
    } else if (this.apiUrl) {
      // Use API
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
  }

  render(items) {
    if (!items.length) {
      this.dropdown.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">لا توجد نتائج</div>';
    } else {
      this.dropdown.innerHTML = items.map(item => {
        const normalized = this.normalizeItem(item);
        return `
                <button type="button" class="w-full text-right px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0 ss-option"
                    data-id="${normalized.id}" data-text="${normalized.text}">
                    <span class="font-medium">${normalized.text}</span>
                    ${normalized.subtext ? `<span class="text-gray-500 text-xs mr-2">${normalized.subtext}</span>` : ''}
                </button>
            `;
      }).join('');

      this.dropdown.querySelectorAll('.ss-option').forEach(btn => {
        btn.addEventListener('click', () => {
          this.hidden.value = btn.dataset.id;
          this.input.value = btn.dataset.text;
          if (this.selectedText) {
            this.selectedText.textContent = btn.dataset.text;
          }
          this.dropdown.classList.add('hidden');
        });
      });
    }
    this.dropdown.classList.remove('hidden');
  }

  setValue(id, text) {
    this.hidden.value = id;
    this.input.value = text;
    if (this.selectedText) {
      this.selectedText.textContent = text;
    }
  }

  clear() {
    this.hidden.value = '';
    this.input.value = '';
    if (this.selectedText) {
      this.selectedText.textContent = '';
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.searchable-select').forEach(el => {
    el.searchableSelect = new SearchableSelect(el);
  });
});
