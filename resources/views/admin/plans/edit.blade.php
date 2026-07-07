@extends('admin.layout')

@section('title', 'Edit Plan')
@section('subtitle', $plan->name)

@section('content')
    <div class="admin-card" style="max-width:520px;">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="admin-form">
            @csrf @method('PUT')
            <div>
                <label class="admin-form-label">Name</label>
                <input type="text" name="name" class="admin-form-input" value="{{ old('name', $plan->name) }}" required>
            </div>
            <div>
                <label class="admin-form-label">Tier</label>
                <select name="tier" class="admin-form-input" required>
                    <option value="plus" @if(old('tier', $plan->tier) === 'plus') selected @endif>Plus</option>
                    <option value="plus_plus" @if(old('tier', $plan->tier) === 'plus_plus') selected @endif>Plus+</option>
                </select>
            </div>
            <div>
                <label class="admin-form-label">Duration (days)</label>
                <input type="number" name="duration_days" class="admin-form-input" value="{{ old('duration_days', $plan->duration_days) }}" min="1" required>
            </div>
            <div>
                <label class="admin-form-label">Price (Rp)</label>
                <input type="number" name="price" class="admin-form-input" value="{{ old('price', $plan->price) }}" min="0" required>
            </div>
            <div>
                <label class="admin-form-label">Theme (optional)</label>
                <select name="theme_id" class="admin-form-input">
                    <option value="">— None —</option>
                    @foreach ($themes as $theme)
                        <option value="{{ $theme->id }}" @if(old('theme_id', $plan->theme_id) == $theme->id) selected @endif>{{ $theme->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-form-label">Sort Order</label>
                <input type="number" name="sort_order" class="admin-form-input" value="{{ old('sort_order', $plan->sort_order) }}" min="0">
            </div>
            <div>
                <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);">
                    <input type="checkbox" name="is_recommended" value="1" @if(old('is_recommended', $plan->is_recommended)) checked @endif> Recommended (Terbaik badge)
                </label>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Update</button>
                <a href="{{ route('admin.plans.index') }}" class="admin-btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection
