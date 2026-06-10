let childIndex = 0;

function addChild(data = {}) {
  const container = document.getElementById('childrenContainer');
  const idx = childIndex++;
  const html = `
    <div class="child-row bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-200 dark:border-gray-600 relative" data-index="${idx}">
      <button type="button" class="remove-child-btn absolute top-2 left-2 p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"><i class="fas fa-times"></i></button>
        <h4 class="font-semibold mb-3 text-sm">ابن/ابنة #${idx + 1}</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
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
          <div class="sm:col-span-2">
            <label class="block text-xs font-medium mb-1">صورة البطاقة</label>
            <input type="file" name="child_id_card_${idx}" accept="image/*" class="form-input w-full text-sm">
            ${data.id_card_image ? `<img src="${(typeof uploadUrlBase !== 'undefined' ? uploadUrlBase : '')}${data.id_card_image}" class="mt-2 h-16 rounded border">` : ''}
          </div>
        </div>
        <!-- DNA Markers for Child -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
          <h5 class="text-sm font-semibold mb-3 text-green-700 dark:text-green-400">نتائج تحليل الحمض النووي</h5>
          <div class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-gray-100 dark:bg-gray-800/50">
                <tr>
                  <th class="px-2 py-1.5 text-center font-semibold">العلامة</th>
                  <th class="px-2 py-1.5 text-center font-semibold">الأليل 1</th>
                  <th class="px-2 py-1.5 text-center font-semibold">الأليل 2</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                <tr>
                  <td class="px-2 py-1.5 text-center font-medium">D3S1358</td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][D3S1358_1]" value="${data.D3S1358_1 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][D3S1358_2]" value="${data.D3S1358_2 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5 text-center font-medium">vWA</td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][vWA_1]" value="${data.vWA_1 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][vWA_2]" value="${data.vWA_2 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5 text-center font-medium">FGA</td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][FGA_1]" value="${data.FGA_1 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][FGA_2]" value="${data.FGA_2 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5 text-center font-medium">D8S1179</td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][D8S1179_1]" value="${data.D8S1179_1 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][D8S1179_2]" value="${data.D8S1179_2 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5 text-center font-medium">D21S11</td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][D21S11_1]" value="${data.D21S11_1 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                  <td class="px-2 py-1.5"><input type="text" name="children[${idx}][D21S11_2]" value="${data.D21S11_2 || ''}" class="form-input w-full px-2 py-1.5 rounded border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800"></td>
                </tr>
              </tbody>
            </table>
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
