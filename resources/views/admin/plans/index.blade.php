@extends('admin.layout')

@section('title', 'Plans')
@section('subtitle', 'Manage subscription plans')

@section('content')
    <div style="margin-bottom:20px;">
        <button class="admin-btn admin-btn-primary" onclick="document.getElementById('createPlanModal').classList.add('active')">+ New Plan</button>
    </div>

    <div class="admin-card" style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sort</th>
                    <th>Name</th>
                    <th>Tier</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Theme</th>
                    <th>Recommended</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr>
                        <td>{{ $plan->sort_order }}</td>
                        <td>{{ $plan->name }}</td>
                        <td>{{ $plan->tierLabel() }}</td>
                        <td>{{ $plan->duration_days }} days</td>
                        <td>{{ $plan->priceFormatted() }}</td>
                        <td>{{ $plan->theme?->name ?? '-' }}</td>
                        <td>{{ $plan->is_recommended ? '✅' : '❌' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.plans.toggle-active', $plan) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="admin-btn admin-btn-sm" style="{{ $plan->is_active ? 'border-color:#4caf50;color:#4caf50;' : '' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="admin-btn admin-btn-sm">Edit</a>
                            @if ($plan->is_active)
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" style="display:inline;" onsubmit="return confirm('Disable this plan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Disable</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:24px;">No plans yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create Modal --}}
    <div class="admin-modal-overlay" id="createPlanModal" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="admin-modal" style="max-width:520px;" onclick="event.stopPropagation()">
            <h2 class="admin-modal-title">New Plan</h2>
            <form method="POST" action="{{ route('admin.plans.store') }}" class="admin-form">
                @csrf
                <div>
                    <label class="admin-form-label">Name</label>
                    <input type="text" name="name" class="admin-form-input" required>
                </div>
                <div>
                    <label class="admin-form-label">Tier</label>
                    <select name="tier" class="admin-form-input" required>
                        <option value="plus">Plus</option>
                        <option value="plus_plus">Plus+</option>
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Duration (days)</label>
                    <input type="number" name="duration_days" class="admin-form-input" min="1" required>
                </div>
                <div>
                    <label class="admin-form-label">Price (Rp)</label>
                    <input type="number" name="price" class="admin-form-input" min="0" required>
                </div>
                <div>
                    <label class="admin-form-label">Theme (optional)</label>
                    <select name="theme_id" class="admin-form-input">
                        <option value="">— None —</option>
                        @foreach ($themes as $theme)
                            <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="admin-form-input" min="0" value="0">
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);">
                        <input type="checkbox" name="is_recommended" value="1"> Recommended (Terbaik badge)
                    </label>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Create</button>
                    <button type="button" class="admin-btn" onclick="document.getElementById('createPlanModal').classList.remove('active')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

@endsection
