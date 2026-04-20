{{-- Payment Methods Tab Content --}}
<div class="help-text">
    <div class="help-title">Payment Methods</div>
    <div class="help-content">
        Enable or disable payment methods available for fee collection. Disabled methods will not appear
        as options when recording payments. At least one payment method must remain enabled.
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Cash --}}
        <div class="payment-method-item">
            <div class="payment-method-info">
                <div class="payment-method-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="payment-method-details">
                    <h6>Cash</h6>
                    <p>Accept cash payments at the school office</p>
                </div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox"
                    class="payment-method-toggle"
                    data-method="cash"
                    {{ ($paymentMethods['cash'] ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        {{-- Bank Transfer --}}
        <div class="payment-method-item">
            <div class="payment-method-info">
                <div class="payment-method-icon">
                    <i class="fas fa-university"></i>
                </div>
                <div class="payment-method-details">
                    <h6>Bank Transfer</h6>
                    <p>Accept payments via direct bank transfer or deposit</p>
                </div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox"
                    class="payment-method-toggle"
                    data-method="bank_transfer"
                    {{ ($paymentMethods['bank_transfer'] ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        {{-- Mobile Money --}}
        <div class="payment-method-item">
            <div class="payment-method-info">
                <div class="payment-method-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="payment-method-details">
                    <h6>Mobile Money</h6>
                    <p>Accept payments via mobile money (Orange Money, etc.)</p>
                </div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox"
                    class="payment-method-toggle"
                    data-method="mobile_money"
                    {{ ($paymentMethods['mobile_money'] ?? false) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        {{-- Card Payment --}}
        <div class="payment-method-item">
            <div class="payment-method-info">
                <div class="payment-method-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="payment-method-details">
                    <h6>Card Payment</h6>
                    <p>Accept debit/credit card payments (requires POS terminal)</p>
                </div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox"
                    class="payment-method-toggle"
                    data-method="card"
                    {{ ($paymentMethods['card'] ?? false) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        {{-- Other --}}
        <div class="payment-method-item">
            <div class="payment-method-info">
                <div class="payment-method-icon">
                    <i class="fas fa-ellipsis-h"></i>
                </div>
                <div class="payment-method-details">
                    <h6>Other</h6>
                    <p>Other payment methods (scholarships, grants, etc.)</p>
                </div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox"
                    class="payment-method-toggle"
                    data-method="other"
                    {{ ($paymentMethods['other'] ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="help-text" style="background: #f0fdf4; border-left-color: #10b981;">
            <div class="help-title" style="color: #065f46;">Quick Tips</div>
            <div class="help-content" style="color: #166534;">
                <ul class="mb-0 ps-3" style="font-size: 12px;">
                    <li class="mb-2">Toggle switches save automatically</li>
                    <li class="mb-2">Disabled methods won't appear in payment forms</li>
                    <li class="mb-2">Existing payments with disabled methods remain in records</li>
                    <li>Consider your school's payment infrastructure when enabling methods</li>
                </ul>
            </div>
        </div>
    </div>
</div>
