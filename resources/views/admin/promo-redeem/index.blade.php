@extends('admin.layout')

@section('title', 'Promo & Redeem')
@section('subtitle', 'Manage promos and redeem codes')

@section('content')
    @php $tab = request()->query('tab', 'promos'); @endphp

    <div style="display:flex;gap:0;margin-bottom:24px;border-bottom:1px solid rgba(255,255,255,0.08);">
        <a href="{{ route('admin.promo-redeem.index', ['tab' => 'promos']) }}"
           style="padding:10px 20px;font-size:0.85rem;font-weight:600;text-decoration:none;color:{{ $tab === 'promos' ? 'var(--accent)' : 'var(--muted)' }};border-bottom:2px solid {{ $tab === 'promos' ? 'var(--accent)' : 'transparent' }};transition:all 0.2s;">
            📢 Promo
        </a>
        <a href="{{ route('admin.promo-redeem.index', ['tab' => 'redeem']) }}"
           style="padding:10px 20px;font-size:0.85rem;font-weight:600;text-decoration:none;color:{{ $tab === 'redeem' ? 'var(--accent)' : 'var(--muted)' }};border-bottom:2px solid {{ $tab === 'redeem' ? 'var(--accent)' : 'transparent' }};transition:all 0.2s;">
            🎟️ Redeem Codes
        </a>
    </div>

    @if ($tab === 'promos')
        {{-- ===================== TAB PROMO ===================== --}}
        <div style="margin-bottom:20px;">
            <button class="admin-btn admin-btn-primary" onclick="document.getElementById('choosePromoTypeModal').classList.add('active')">+ New Promo</button>
        </div>

        <div class="admin-card" style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
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
                            <td>{{ $promo->type === 'percent' ? "{$promo->value}%" : 'Rp' . number_format($promo->value, 0, ',', '.') }}</td>
                            <td>{{ $promo->plan?->name ?? 'All plans' }}</td>
                            <td>{{ $promo->used_count }}{{ $promo->max_uses > 0 ? "/{$promo->max_uses}" : '/∞' }}</td>
                            <td>{{ $promo->expires_at?->format('d M Y') ?? '∞' }}</td>
                            <td>{{ $promo->show_popup ? '✅' : '❌' }}</td>
                            <td>{{ $promo->is_active ? '✅' : '❌' }}</td>
                            <td>{{ $promo->creator?->name ?? '-' }}</td>
                            <td>
                                <button type="button" class="admin-btn admin-btn-sm"
                                    onclick="editPromo({{ Illuminate\Support\Js::from([
                                        'id' => $promo->id,
                                        'name' => $promo->name,
                                        'type' => $promo->type,
                                        'value' => $promo->value,
                                        'plan_id' => $promo->plan_id,
                                        'max_uses' => $promo->max_uses,
                                        'starts_at' => $promo->starts_at?->format('Y-m-d\TH:i'),
                                        'expires_at' => $promo->expires_at?->format('Y-m-d\TH:i'),
                                        'show_popup' => $promo->show_popup,
                                        'popup_title' => $promo->popup_title,
                                        'popup_message' => $promo->popup_message,
                                    ]) }})"
                                    style="margin-right:4px;">Edit</button>
                                @if ($promo->is_active)
                                    <form method="POST" action="{{ route('admin.promo-redeem.promos.destroy', $promo) }}" style="display:inline;" onsubmit="return confirm('Disable this promo?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Disable</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.promo-redeem.promos.activate', $promo) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:24px;">No promos yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Popup Pilih Tipe Promo --}}
        <div class="admin-modal-overlay" id="choosePromoTypeModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:440px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Pilih Tipe Promo</h2>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <button onclick="document.getElementById('choosePromoTypeModal').classList.remove('active'); document.getElementById('createCampaignModal').classList.add('active');"
                            style="display:flex;align-items:center;gap:12px;padding:16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;cursor:pointer;color:#fff;font-size:0.9rem;font-weight:600;text-align:left;transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <span style="font-size:1.5rem;">📢</span>
                        <div>
                            <div>Promo Campaign</div>
                            <div style="font-size:0.75rem;color:var(--muted);font-weight:400;">Diskon dengan popup, bisa untuk semua plan atau plan tertentu</div>
                        </div>
                    </button>
                    <button onclick="document.getElementById('choosePromoTypeModal').classList.remove('active'); document.getElementById('createPlanDiscountModal').classList.add('active');"
                            style="display:flex;align-items:center;gap:12px;padding:16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;cursor:pointer;color:#fff;font-size:0.9rem;font-weight:600;text-align:left;transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <span style="font-size:1.5rem;">📉</span>
                        <div>
                            <div>Diskon Plan</div>
                            <div style="font-size:0.75rem;color:var(--muted);font-weight:400;">Diskon langsung ke plan tertentu, tanpa popup</div>
                        </div>
                    </button>
                </div>
                <div class="admin-modal-actions" style="margin-top:20px;">
                    <button type="button" class="admin-btn" onclick="document.getElementById('choosePromoTypeModal').classList.remove('active')">Batal</button>
                </div>
            </div>
        </div>

        {{-- Modal: Promo Campaign --}}
        <div class="admin-modal-overlay" id="createCampaignModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:520px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Promo Campaign</h2>
                <form method="POST" action="{{ route('admin.promo-redeem.promos.store') }}" class="admin-form">
                    @csrf
                    <div>
                        <label class="admin-form-label">Name</label>
                        <input type="text" name="name" class="admin-form-input" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="admin-form-label">Type</label>
                            <select name="type" class="admin-form-input" required>
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Value</label>
                            <input type="number" name="value" class="admin-form-input" min="1" max="100" required>
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
                    <div>
                        <label class="admin-form-label">Max Uses (0 = unlimited)</label>
                        <input type="number" name="max_uses" class="admin-form-input" min="0" value="0">
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
                            <input type="checkbox" name="show_popup" value="1" onchange="document.getElementById('campaignPopupFields').style.display=this.checked?'block':'none';"> Show popup to users
                        </label>
                    </div>
                    <div id="campaignPopupFields" style="display:none;">
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
                        <button type="button" class="admin-btn" onclick="document.getElementById('createCampaignModal').classList.remove('active')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Diskon Plan --}}
        <div class="admin-modal-overlay" id="createPlanDiscountModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:440px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Diskon Plan</h2>
                <form method="POST" action="{{ route('admin.promo-redeem.promos.store') }}" class="admin-form">
                    @csrf
                    <div>
                        <label class="admin-form-label">Target Plan</label>
                        <select name="plan_id" class="admin-form-input" required>
                            <option value="">— Pilih Plan —</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="admin-form-label">Type</label>
                            <select name="type" class="admin-form-input" onchange="document.getElementById('planDiscountValue').max=this.value==='percent'?100:999999;" required>
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Value</label>
                            <input type="number" name="value" id="planDiscountValue" class="admin-form-input" min="1" max="100" required>
                        </div>
                    </div>
                    <input type="hidden" name="max_uses" value="0">
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">Create</button>
                        <button type="button" class="admin-btn" onclick="document.getElementById('createPlanDiscountModal').classList.remove('active')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Edit Promo --}}
        <div class="admin-modal-overlay" id="editPromoModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:520px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Edit Promo</h2>
                <form method="POST" action="" id="editPromoForm" class="admin-form">
                    @csrf @method('PUT')
                    <div>
                        <label class="admin-form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="admin-form-input" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="admin-form-label">Type</label>
                            <select name="type" id="edit_type" class="admin-form-input" required onchange="document.getElementById('edit_value').max=this.value==='percent'?100:999999;">
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Value</label>
                            <input type="number" name="value" id="edit_value" class="admin-form-input" min="1" max="100" required>
                        </div>
                    </div>
                    <div>
                        <label class="admin-form-label">Target Plan <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
                        <select name="plan_id" id="edit_plan_id" class="admin-form-input">
                            <option value="">All Plans</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-form-label">Max Uses (0 = unlimited)</label>
                        <input type="number" name="max_uses" id="edit_max_uses" class="admin-form-input" min="0" value="0">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="admin-form-label">Start At</label>
                            <input type="datetime-local" name="starts_at" id="edit_starts_at" class="admin-form-input">
                        </div>
                        <div>
                            <label class="admin-form-label">Expires At</label>
                            <input type="datetime-local" name="expires_at" id="edit_expires_at" class="admin-form-input">
                        </div>
                    </div>
                    <div>
                        <label class="admin-form-label">Popup</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);margin-top:4px;">
                            <input type="checkbox" name="show_popup" id="edit_show_popup" value="1" onchange="document.getElementById('editPopupFields').style.display=this.checked?'block':'none';"> Show popup to users
                        </label>
                    </div>
                    <div id="editPopupFields" style="display:none;">
                        <div>
                            <label class="admin-form-label">Popup Title</label>
                            <input type="text" name="popup_title" id="edit_popup_title" class="admin-form-input" maxlength="100">
                        </div>
                        <div>
                            <label class="admin-form-label">Popup Message</label>
                            <textarea name="popup_message" id="edit_popup_message" class="admin-form-input admin-form-textarea"></textarea>
                        </div>
                    </div>
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">Update</button>
                        <button type="button" class="admin-btn" onclick="document.getElementById('editPromoModal').classList.remove('active')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function editPromo(data) {
                document.getElementById('editPromoForm').action = '{{ url('admin/promo-redeem/promos') }}/' + data.id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_type').value = data.type;
                document.getElementById('edit_value').value = data.value;
                document.getElementById('edit_plan_id').value = data.plan_id || '';
                document.getElementById('edit_max_uses').value = data.max_uses;
                document.getElementById('edit_starts_at').value = data.starts_at || '';
                document.getElementById('edit_expires_at').value = data.expires_at || '';
                document.getElementById('edit_show_popup').checked = data.show_popup;
                document.getElementById('edit_popup_title').value = data.popup_title || '';
                document.getElementById('edit_popup_message').value = data.popup_message || '';
                document.getElementById('editPopupFields').style.display = data.show_popup ? 'block' : 'none';
                document.getElementById('editPromoModal').classList.add('active');
            }
        </script>

    @else
        {{-- ===================== TAB REDEEM CODES ===================== --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;">
            <div>
                <p style="color:rgba(255,255,255,0.45);font-size:0.85rem;margin:0;">Total: {{ $totalCodes }} &middot; Aktif: {{ $totalActive }} &middot; Digunakan: {{ $totalRedeemed }}x</p>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <button class="admin-btn admin-btn-primary" onclick="document.getElementById('chooseRedeemTypeModal').classList.add('active')">+ New Code</button>
        </div>

        {{-- Popup Pilih Tipe Redeem Code --}}
        <div class="admin-modal-overlay" id="chooseRedeemTypeModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:440px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Pilih Tipe Kode</h2>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <button onclick="document.getElementById('chooseRedeemTypeModal').classList.remove('active'); document.getElementById('createFreeAccessModal').classList.add('active');"
                            style="display:flex;align-items:center;gap:12px;padding:16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;cursor:pointer;color:#fff;font-size:0.9rem;font-weight:600;text-align:left;transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <span style="font-size:1.5rem;">🎟️</span>
                        <div>
                            <div>Free Access</div>
                            <div style="font-size:0.75rem;color:var(--muted);font-weight:400;">Kode akses gratis Plus/Plus+ (tier + durasi)</div>
                        </div>
                    </button>
                    <button onclick="document.getElementById('chooseRedeemTypeModal').classList.remove('active'); document.getElementById('createPromoCodeModal').classList.add('active');"
                            style="display:flex;align-items:center;gap:12px;padding:16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;cursor:pointer;color:#fff;font-size:0.9rem;font-weight:600;text-align:left;transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                        <span style="font-size:1.5rem;">🏷️</span>
                        <div>
                            <div>Kode Promo</div>
                            <div style="font-size:0.75rem;color:var(--muted);font-weight:400;">Kode diskon untuk subscription (percent/fixed)</div>
                        </div>
                    </button>
                </div>
                <div class="admin-modal-actions" style="margin-top:20px;">
                    <button type="button" class="admin-btn" onclick="document.getElementById('chooseRedeemTypeModal').classList.remove('active')">Batal</button>
                </div>
            </div>
        </div>

        {{-- Modal: Free Access --}}
        <div class="admin-modal-overlay" id="createFreeAccessModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:500px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Buat Kode Free Access</h2>
                <form method="POST" action="{{ route('admin.promo-redeem.redeem-codes.store') }}" class="admin-form">
                    @csrf
                    <input type="hidden" name="type" value="free_access">
                    <div>
                        <label class="admin-form-label">KODE</label>
                        <input type="text" name="code" placeholder="Contoh: GRATIS30" class="admin-form-input" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                        <div>
                            <label class="admin-form-label">TIER</label>
                            <select name="tier" class="admin-form-input" required>
                                <option value="plus">Plus</option>
                                <option value="plus_plus">Plus+</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">DURASI</label>
                            <select name="duration_days" class="admin-form-input" required>
                                <option value="30">30 hari</option>
                                <option value="365">365 hari</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">MAX PAKAI</label>
                            <input type="number" name="max_uses" value="1" min="0" class="admin-form-input">
                        </div>
                    </div>
                    <div>
                        <label class="admin-form-label">Expires At <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
                        <input type="datetime-local" name="expires_at" class="admin-form-input">
                    </div>
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">Buat</button>
                        <button type="button" class="admin-btn" onclick="document.getElementById('createFreeAccessModal').classList.remove('active')">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Kode Promo --}}
        <div class="admin-modal-overlay" id="createPromoCodeModal" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="admin-modal" style="max-width:520px;" onclick="event.stopPropagation()">
                <h2 class="admin-modal-title">Buat Kode Promo</h2>
                <form method="POST" action="{{ route('admin.promo-redeem.redeem-codes.store') }}" class="admin-form">
                    @csrf
                    <input type="hidden" name="type" value="promo">
                    <div>
                        <label class="admin-form-label">KODE</label>
                        <input type="text" name="code" placeholder="Contoh: HEMAT25" class="admin-form-input" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="admin-form-label">Discount Type</label>
                            <select name="discount_type" class="admin-form-input" required>
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Discount Value</label>
                            <input type="number" name="discount_value" class="admin-form-input" min="1" max="100" required>
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
                    <div>
                        <label class="admin-form-label">Max Uses (0 = unlimited)</label>
                        <input type="number" name="max_uses" class="admin-form-input" min="0" value="1">
                    </div>
                    <div>
                        <label class="admin-form-label">Expires At <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
                        <input type="datetime-local" name="expires_at" class="admin-form-input">
                    </div>
                    <div>
                        <label class="admin-form-label">Popup</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);margin-top:4px;">
                            <input type="checkbox" name="show_popup" value="1" onchange="document.getElementById('promoCodePopupFields').style.display=this.checked?'block':'none';"> Show popup to users
                        </label>
                    </div>
                    <div id="promoCodePopupFields" style="display:none;">
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
                        <button type="submit" class="admin-btn admin-btn-primary">Buat</button>
                        <button type="button" class="admin-btn" onclick="document.getElementById('createPromoCodeModal').classList.remove('active')">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Redeem Codes --}}
        @if ($codes->isEmpty())
            <div style="text-align:center;padding:60px 0;color:rgba(255,255,255,0.3);font-size:0.9rem;">Belum ada kode.</div>
        @else
            <div class="admin-card" style="padding:0;overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Type</th>
                            <th>Detail</th>
                            <th>Plan</th>
                            <th>Pemakaian</th>
                            <th>Status</th>
                            <th>Admin</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($codes as $code)
                            <tr>
                                <td style="font-family:'Courier New',monospace;font-weight:600;letter-spacing:0.5px;">{{ $code->code }}</td>
                                <td>
                                    @if ($code->isFreeAccess())
                                        <span style="background:rgba(0,188,212,0.15);color:#00bcd4;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Free Access</span>
                                    @else
                                        <span style="background:rgba(255,152,0,0.15);color:#ff9800;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Promo</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($code->isFreeAccess())
                                        {{ $code->tier === 'plus_plus' ? 'Plus+' : 'Plus' }} &middot; {{ $code->duration_days }} hari
                                    @else
                                        {{ $code->discount_type === 'percent' ? "{$code->discount_value}%" : 'Rp'.number_format($code->discount_value, 0, ',', '.') }}
                                        @if ($code->show_popup) &nbsp;🪟 @endif
                                    @endif
                                </td>
                                <td style="font-size:0.8rem;">{{ $code->plan?->name ?? 'All' }}</td>
                                <td style="color:var(--muted);">{{ $code->used_count }} / {{ $code->max_uses === 0 ? '∞' : $code->max_uses }}</td>
                                <td>
                                    @if ($code->is_active)
                                        <span style="color:#22c55e;">Aktif</span>
                                    @else
                                        <span style="color:var(--danger);">Nonaktif</span>
                                    @endif
                                    @if ($code->expires_at && now()->greaterThan($code->expires_at))
                                        <span style="color:var(--danger);margin-left:4px;">(Expired)</span>
                                    @endif
                                </td>
                                <td style="color:var(--muted);">{{ $code->creator?->name ?? '-' }}</td>
                                <td style="color:var(--muted);font-size:0.78rem;">{{ $code->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.promo-redeem.redeem-codes.show', $code) }}" style="color:var(--muted);text-decoration:none;font-size:0.78rem;margin-right:8px;">Detail</a>
                                    @if ($code->is_active)
                                        <form method="POST" action="{{ route('admin.promo-redeem.redeem-codes.destroy', $code) }}" style="display:inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="background:none;border:none;color:var(--danger);font-size:0.78rem;cursor:pointer;padding:0;" onclick="return confirm('Nonaktifkan kode ini?')">Nonaktifkan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.promo-redeem.redeem-codes.activate', $code) }}" style="display:inline">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">Aktifkan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="admin-pagination">{{ $codes->links() }}</div>
        @endif
    @endif
@endsection
