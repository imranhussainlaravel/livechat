@php
    $modalInput = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
    $modalCompanies = \App\Models\Company::orderBy('name')->get();
@endphp

{{-- ─── Create Company modal ─── --}}
{{-- Higher z than the contact modal (z-[9998]) so it stacks ON TOP when
     opened from within Contact ("+ new company"), not behind it. --}}
<div id="crm-modal-company" class="hidden fixed inset-0 z-[10000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="crmModal.close('company')"></div>
    <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700/60">
            <h3 class="text-base font-bold text-white">New Company</h3>
            <button type="button" onclick="crmModal.close('company')" class="text-slate-500 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form data-crm-create="company" action="{{ route('crm.companies.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Company name<span class="text-rose-400"> *</span></label>
                <input type="text" name="name" required class="{{ $modalInput }}" placeholder="Acme Packaging">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">City</label>
                <input type="text" name="city" class="{{ $modalInput }}" placeholder="Karachi">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Industry notes</label>
                <textarea name="industry_notes" rows="2" class="{{ $modalInput }}"></textarea>
            </div>
            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Create</button>
                <button type="button" onclick="crmModal.close('company')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Create Contact modal (has its own company picker + "+") ─── --}}
<div id="crm-modal-contact" class="hidden fixed inset-0 z-[9998] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="crmModal.close('contact')"></div>
    <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700/60">
            <h3 class="text-base font-bold text-white">New Contact</h3>
            <button type="button" onclick="crmModal.close('contact')" class="text-slate-500 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form data-crm-create="contact" action="{{ route('crm.contacts.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Company<span class="text-rose-400"> *</span></label>
                <div class="flex gap-2">
                    <select name="company_id" id="contact_modal_company_id" required class="{{ $modalInput }}">
                        <option value="">Select a company…</option>
                        @foreach($modalCompanies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="crmModal.open('company', '#contact_modal_company_id')"
                            title="New company"
                            class="shrink-0 inline-flex items-center justify-center w-10 rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Contact person<span class="text-rose-400"> *</span></label>
                <input type="text" name="name" required class="{{ $modalInput }}" placeholder="Jane Doe">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone</label>
                <input type="text" name="phone" class="{{ $modalInput }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email</label>
                <input type="email" name="email" class="{{ $modalInput }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Designation</label>
                <input type="text" name="designation" class="{{ $modalInput }}">
            </div>
            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Create</button>
                <button type="button" onclick="crmModal.close('contact')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Global modal controller (defined every render; cheap + Turbo-safe).
    window.crmModal = window.crmModal || {
        _targets: {},
        open: function (name, targetSel) {
            if (targetSel) this._targets[name] = targetSel;
            var m = document.getElementById('crm-modal-' + name);
            if (m) m.classList.remove('hidden');
        },
        close: function (name) {
            var m = document.getElementById('crm-modal-' + name);
            if (m) m.classList.add('hidden');
        }
    };

    // Bind the submit handler once (document survives Turbo body swaps).
    if (!window._crmModalBound) {
        window._crmModalBound = true;
        document.addEventListener('submit', function (e) {
            var form = e.target.closest('form[data-crm-create]');
            if (!form) return;
            e.preventDefault();

            var name = form.getAttribute('data-crm-create');
            var payload = {};
            new FormData(form).forEach(function (v, k) { if (k.charAt(0) !== '_') payload[k] = v; });

            var btn = form.querySelector('button[type="submit"]');
            var orig = btn ? btn.innerHTML : '';
            if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(payload)
            })
            .then(async function (r) { var j = await r.json(); if (!r.ok) throw j; return j; })
            .then(function (j) {
                var targetSel = window.crmModal._targets[name];
                if (targetSel) {
                    var sel = document.querySelector(targetSel);
                    if (sel) {
                        var opt = document.createElement('option');
                        opt.value = j.id;
                        opt.textContent = j.label || j.name;
                        opt.selected = true;
                        sel.appendChild(opt);
                        sel.dispatchEvent(new Event('change'));
                    }
                } else {
                    // No target (opened from an index "New" button) → refresh to show the new row.
                    if (window.Turbo) { window.Turbo.visit(window.location.href, { action: 'replace' }); }
                    else { window.location.reload(); }
                }
                if (window.showToast) showToast((name.charAt(0).toUpperCase() + name.slice(1)) + ' created.');
                form.reset();
                window.crmModal.close(name);
            })
            .catch(function (err) {
                var msg = (err && err.errors) ? Object.values(err.errors).flat()[0] : ((err && err.message) || 'Could not save.');
                if (window.showToast) showToast(msg, 'error');
            })
            .finally(function () { if (btn) { btn.disabled = false; btn.innerHTML = orig; } });
        });
    }
})();
</script>
@endpush
