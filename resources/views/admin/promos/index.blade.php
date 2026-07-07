@extends('admin.layout')

@section('title', 'Promos')
@section('subtitle', 'Manage subscription promo codes & automatic discounts')

@section('content')
    <div style="margin-bottom:20px;">
        <button class="admin-btn admin-btn-primary" onclick="document.getElementById('createPromoModal').classList.add('active')">+ New Promo</button>
    </div>

    <div class="admin-card" style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Plan</th>
                    <th>Uses</th>
                    <th>Valid Until</th>
                    <th>Popup</th>
                    <th>Active</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promos as $promo)
                    <tr>
                        <td>{{ $promo->name }}</td>
                        <td><code>{{ $promo->code ?? '(auto)' }}</code></td>
                        <td>{{ $promo->type === 'percent' ? "{$promo->value}%" : 'Rp' . number_format($promo->value, 0, ',', '.') }}</td>
                        <td>{{ $promo->plan?->name ?? 'All plans' }}</td>
                        <td>{{ $promo->used_count }}{{ $promo->max_uses > 0 ? "/{$promo->max_uses}" : '/∞' }}</td>
                        <td>{{ $promo->expires_at?->format('d M Y') ?? '∞' }}</td>
                        <td>{{ $promo->show_popup ? '✅' : '❌' }}</td>
                        <td>{{ $promo->is_active ? '✅' : '❌' }}</td>
                        <td>{{ $promo->creator?->name ?? '-' }}</td>
                        <td>
                            @if ($promo->is_active)
                                <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" style="display:inline;" onsubmit="return confirm('Disable this promo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Disable</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:24px;">No promos yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create Modal --}}
    <div class="admin-modal-overlay" id="createPromoModal" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="admin-modal" style="max-width:520px;" onclick="event.stopPropagation()">
            <h2 class="admin-modal-title">New Promo</h2>
            <form method="POST" action="{{ route('admin.promos.store') }}" class="admin-form">
                @csrf
                <div>
                    <label class="admin-form-label">Name</label>
                    <input type="text" name="name" class="admin-form-input" required>
                </div>
                <div>
                    <label class="admin-form-label">Code <span style="color:var(--muted);font-weight:400;">(leave empty = auto promo)</span></label>
                    <input type="text" name="code" class="admin-form-input" maxlength="32">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="admin-form-label">Type</label>
                        <select name="type" class="admin-form-input" id="promoType" required onchange="togglePromoValue()">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-form-label">Value</label>
                        <input type="number" name="value" id="promoValue" class="admin-form-input" min="1" max="100" required>
                    </div>
                </div>
                <div>
                    <label class="admin-form-label">Target Plan <span style="color:var(--muted);font-weight:400;">(optional — all if empty)</span></label>
                    <select name="plan_id" class="admin-form-input">
                        <option value="">All Plans</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="admin-form-label">Max Uses (0 = unlimited)</label>
                        <input type="number" name="max_uses" class="admin-form-input" min="0" value="0">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="admin-form-label">Start At</label>
                        <input type="datetime-local" name="starts_at" class="admin-form-input">
                    </div>
                    <div>
                        <label class="admin-form-label">Expires At</label>
                        <input type="datetime-local" name="expires_at" class="admin-form-input">
                    </div>
                </div>
                <div>
                    <label class="admin-form-label">Popup</label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);margin-top:4px;">
                        <input type="checkbox" name="show_popup" value="1"> Show popup to users
                    </label>
                </div>
                <div id="popupFields" style="display:none;">
                    <div>
                        <label class="admin-form-label">Popup Title</label>
                        <input type="text" name="popup_title" class="admin-form-input" maxlength="100">
                    </div>
                    <div>
                        <label class="admin-form-label">Popup Message</label>
                        <textarea name="popup_message" class="admin-form-input admin-form-textarea"></textarea>
                    </div>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Create</button>
                    <button type="button" class="admin-btn" onclick="document.getElementById('createPromoModal').classList.remove('active')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('[name="show_popup"]')?.addEventListener('change', function() {
            document.getElementById('popupFields').style.display = this.checked ? 'block' : 'none';
        });
        function togglePromoValue() {
            const type = document.getElementById('promoType').value;
            const input = document.getElementById('promoValue');
            input.max = type === 'percent' ? 100 : 999999;
            if (type === 'percent' && input.value > 100) input.value = 100;
        }
    </script>
@endsection
