let childIndex = 0;

function addChild(data = {}) {
  const container = document.getElementById('childrenContainer');
  const idx = childIndex++;
  const html = `
    <div class="child-row bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-200 dark:border-gray-600 relative" data-index="${idx}">
      <button type="button" class="remove-child-btn absolute top-2 left-2 p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"><i class="fas fa-times"></i></button>
        <h4 class="font-semibold mb-3 text-sm">ابن/ابنة #${idx + 1}</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <input type="hidden" name="children[${idx}][id]" value="${data.id || ''}">
            <input type="hidden" name="children[${idx}][id_card_image]" value="${data.id_card_image || ''}">
            <div>
                <label class="block text-xs font-medium mb-1">الاسم *</label>
                <input type="text" name="children[${idx}][name]" value="${data.name || ''}" required class="form-input w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">الرقم القومي</label>
                <input type="text" name="children[${idx}][national_id]" value="${data.national_id || ''}" maxlength="14" inputmode="numeric" data-national-id class="form-input w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">رقم عينة DNA</label>
                <input type="text" name="children[${idx}][dna_sample_number]" value="${data.dna_sample_number || ''}" class="form-input w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">الجنس *</label>
                <select name="children[${idx}][gender]" required class="form-input w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    <option value="male" ${data.gender === 'male' ? 'selected' : ''}>ذكر</option>
                    <option value="female" ${data.gender === 'female' ? 'selected' : ''}>أنثى</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">فصيلة الدم</label>
                <select name="children[${idx}][blood_type]" class="form-input w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                    <option value="">--</option>
                    ${['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'].map(bt => `<option value="${bt}" ${data.blood_type === bt ? 'selected' : ''}>${bt}</option>`).join('')}
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">تاريخ الميلاد</label>
                <input type="date" name="children[${idx}][birth_date]" value="${data.birth_date || ''}" onchange="App.calcAge(this.value, this.closest('.child-row').querySelector('.child-age'))" class="form-input w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">العمر</label>
                <input type="text" readonly class="child-age w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-sm" value="">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">صورة البطاقة</label>
                <input type="file" name="child_id_card_${idx}" accept="image/*" class="form-input w-full text-sm">
                ${data.id_card_image ? `<img src="${(typeof uploadUrlBase !== 'undefined' ? uploadUrlBase : '')}${data.id_card_image}" class="mt-2 h-16 rounded border">` : ''}
            </div>
        </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
  const row = container.lastElementChild;
  const delBtn = row?.querySelector('.remove-child-btn');
  if (delBtn) {
    delBtn.addEventListener('click', async (e) => {
      await removeChild(e.currentTarget);
    });
  }
  const birthInput = row?.querySelector(`input[name="children[${idx}][birth_date]"]`);
  if (birthInput?.value && typeof App !== 'undefined' && typeof App.calcAge === 'function') {
    App.calcAge(birthInput.value, row.querySelector('.child-age'));
  }
  row?.querySelector('[data-national-id]')?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 14);
  });
  if (typeof App !== 'undefined' && typeof App.initFileInputs === 'function') {
    App.initFileInputs();
  }
}

async function removeChild(btn) {
  const row = btn.closest('.child-row');
  if (!row) return;
  if (typeof Confirm !== 'undefined') {
    const ok = await Confirm.show('هل أنت متأكد من حذف هذا الابن/الابنة؟ لا يمكن التراجع عن هذا الإجراء.');
    if (!ok) return;
  } else {
    if (!confirm('هل أنت متأكد من حذف هذا الابن/الابنة؟ لا يمكن التراجع عن هذا الإجراء.')) return;
  }
  if (row.parentNode) {
    try {
      if (row.parentNode.contains(row)) {
        row.parentNode.removeChild(row);
      } else if (typeof row.remove === 'function') {
        row.remove();
      }
    } catch (e) {
      if (typeof row.remove === 'function') row.remove();
    }
  } else if (typeof row.remove === 'function') {
    row.remove();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('addChildBtn')?.addEventListener('click', () => addChild());

  document.querySelectorAll('[data-age-calc]').forEach(input => {
    input.addEventListener('change', function () {
      const target = document.querySelector(this.dataset.ageCalc);
      App.calcAge(this.value, target);
    });
  });
});
