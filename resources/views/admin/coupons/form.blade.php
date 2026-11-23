<form action="{{ $coupon ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" method="POST" class="admin-form">
    @csrf
    @if($coupon)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-form-grid">
        <div class="form-group">
            <label for="code">Coupon code</label>
            <input id="code" name="code" class="form-control" value="{{ old('code', $coupon?->code) }}" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="type">Discount type</label>
            <select id="type" name="type" class="form-control" required>
                <option value="fixed" @selected(old('type', $coupon?->type) === 'fixed')>Fixed amount</option>
                <option value="percentage" @selected(old('type', $coupon?->type) === 'percentage')>Percentage</option>
            </select>
        </div>

        <div class="form-group">
            <label for="value">Discount value</label>
            <input id="value" name="value" type="number" min="0" step="0.01" class="form-control" value="{{ old('value', $coupon?->value) }}" required>
        </div>

        <div class="form-group">
            <label for="minimum_amount">Minimum order (NPR)</label>
            <input id="minimum_amount" name="minimum_amount" type="number" min="0" step="0.01" class="form-control" value="{{ old('minimum_amount', $coupon?->minimum_amount ?? 0) }}" required>
        </div>

        <div class="form-group">
            <label for="usage_limit">Usage limit</label>
            <input id="usage_limit" name="usage_limit" type="number" min="1" class="form-control" value="{{ old('usage_limit', $coupon?->usage_limit) }}" placeholder="Unlimited">
        </div>

        <div class="form-group">
            <label for="starts_at">Valid from</label>
            <input id="starts_at" name="starts_at" type="date" class="form-control" value="{{ old('starts_at', optional($coupon?->starts_at)->format('Y-m-d')) }}">
        </div>

        <div class="form-group">
            <label for="expires_at">Expires on</label>
            <input id="expires_at" name="expires_at" type="date" class="form-control" value="{{ old('expires_at', optional($coupon?->expires_at)->format('Y-m-d')) }}">
        </div>

        <div class="form-group admin-form-wide">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3" maxlength="255">{{ old('description', $coupon?->description) }}</textarea>
        </div>
    </div>

    <div class="admin-form-toggles">
        <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon?->is_active ?? true))> Active coupon</label>
    </div>

    <div class="admin-form-actions">
        <button class="admin-primary-action" type="submit">{{ $coupon ? 'Save coupon' : 'Create coupon' }}</button>
        <a class="admin-role-button" href="{{ route('admin.coupons.index') }}">Cancel</a>
    </div>
</form>
