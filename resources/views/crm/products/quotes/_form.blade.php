@php
    $defaultAccountType = old('account_type');

    if (! $defaultAccountType) {
        if (($quote->lead_id ?? null) || ($defaultSelections['lead_id'] ?? null)) {
            $defaultAccountType = 'lead';
        } elseif (($quote->customer_id ?? null) || ($defaultSelections['customer_id'] ?? null)) {
            $defaultAccountType = 'customer';
        } else {
            $defaultAccountType = 'contact';
        }
    }

    $formLineItems = old('items', $lineItems);
    $selectedLeadId = old('lead_id', $quote->lead_id ?? $defaultSelections['lead_id']);
    $selectedCustomerId = old('customer_id', $quote->customer_id ?? $defaultSelections['customer_id']);
    $selectedContactId = old('contact_id', $quote->contact_id ?? $defaultSelections['contact_id']);
    $selectedRequestId = old('request_id', $quote->request_id ?? $defaultSelections['request_id']);
    $selectedCurrencyId = old('currency_id', $defaultSelections['currency_id']);
    $contactOptions = $contacts->map(function ($contact) {
        $accountLabel = $contact->customer?->company_name ?: $contact->lead?->company_name;

        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'label' => trim($contact->name . ($accountLabel ? ' · ' . $accountLabel : '')),
            'lead_id' => $contact->lead_id,
            'customer_id' => $contact->customer_id,
        ];
    })->values()->all();
    $salesRequestOptions = $salesRequests->map(function ($crmRequest) {
        return [
            'id' => $crmRequest->id,
            'title' => $crmRequest->title,
            'lead_id' => $crmRequest->lead_id,
            'customer_id' => $crmRequest->customer_id,
            'contact_id' => $crmRequest->contact_id,
        ];
    })->values()->all();
    $productOptions = $products->mapWithKeys(function ($product) {
        return [
            (string) $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'unit_label' => $product->default_unit_label,
                'unit_price' => (float) $product->default_unit_price * (1 + ((float) $product->cpi_increase_rate / 100)),
                'base_unit_price' => (float) $product->default_unit_price,
                'cpi_increase_rate' => (float) $product->cpi_increase_rate,
                'tax_rate' => (float) $product->default_tax_rate,
            ],
        ];
    })->all();
    $currencyOptions = $currencies->mapWithKeys(function ($currency) {
        return [
            (string) $currency->id => [
                'id' => $currency->id,
                'code' => $currency->code,
                'precision' => (int) $currency->precision,
            ],
        ];
    })->all();
    $historicalInactiveProducts = collect($historicalInactiveProducts ?? [])->keyBy('id');
@endphp

<form method="POST" action="{{ $action }}" class="crm-form" id="quote-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-help">
        Quotes can be linked to a lead, a customer renewal account, or sent directly to a recipient contact with an optional sales request. Catalog values are copied into the quote at save time so later product edits do not change historical documents.
    </div>

    @if (isset($quote))
        <div class="crm-grid cols-3">
            <div class="crm-metric">
                <span>Quote number</span>
                <strong>{{ $quote->quote_number }}</strong>
            </div>
            <div class="crm-metric">
                <span>Status</span>
                <strong>{{ $quoteStatuses[$quote->status] ?? ucfirst($quote->status) }}</strong>
            </div>
            <div class="crm-metric">
                <span>Current total</span>
                <strong>{{ $quote->currency_code }} {{ number_format((float) $quote->total_amount, 2) }}</strong>
            </div>
        </div>
    @endif

    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Quote context</p>
                <h2>Header information</h2>
            </div>
        </div>

        <div class="crm-field-grid">
            <div class="crm-field">
                <label for="account_type">Account type</label>
                <select id="account_type" name="account_type" data-account-type required>
                    <option value="lead" @selected($defaultAccountType === 'lead')>Lead</option>
                    <option value="customer" @selected($defaultAccountType === 'customer')>Customer</option>
                    <option value="contact" @selected($defaultAccountType === 'contact')>Direct contact</option>
                </select>
            </div>
            <div class="crm-field" data-account-panel="lead">
                <label for="lead_id">Lead</label>
                <select id="lead_id" name="lead_id" data-account-select="lead">
                    <option value="">Select a lead</option>
                    @foreach ($leads as $lead)
                        <option value="{{ $lead->id }}" @selected((int) $selectedLeadId === $lead->id)>{{ $lead->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field" data-account-panel="customer">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" data-account-select="customer">
                    <option value="">Select a customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((int) $selectedCustomerId === $customer->id)>{{ $customer->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="contact_id">Recipient contact</label>
                <select id="contact_id" name="contact_id" data-contact-select data-selected="{{ $selectedContactId ?? '' }}" required>
                    <option value="">Select a contact</option>
                </select>
            </div>
            <div class="crm-field">
                <label for="request_id">Linked sales request</label>
                <select id="request_id" name="request_id" data-request-select data-selected="{{ $selectedRequestId ?? '' }}">
                    <option value="">No linked request</option>
                </select>
            </div>
            <div class="crm-field">
                <label for="currency_id">Currency</label>
                <select id="currency_id" name="currency_id" data-currency-select required>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->id }}" @selected((int) $selectedCurrencyId === $currency->id)>{{ $currency->code }} · {{ $currency->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-field">
                <label for="document_tax_rate">Document tax rate (%)</label>
                <input id="document_tax_rate" name="document_tax_rate" type="number" step="0.01" min="0" max="100" value="{{ old('document_tax_rate', number_format((float) ($quote->document_tax_rate ?? $defaultSelections['document_tax_rate'] ?? $settings->default_tax_rate), 2, '.', '')) }}" data-document-tax-rate>
            </div>
            <div class="crm-field">
                <label for="quote_date">Quote date</label>
                <input id="quote_date" name="quote_date" type="date" value="{{ old('quote_date', isset($quote) ? $quote->quote_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
            </div>
            <div class="crm-field">
                <label for="valid_until">Valid until</label>
                <input id="valid_until" name="valid_until" type="date" value="{{ old('valid_until', isset($quote) && $quote->valid_until ? $quote->valid_until->format('Y-m-d') : now()->addDays(14)->format('Y-m-d')) }}" required>
            </div>
            <div class="crm-field full">
                <label for="subject">Subject</label>
                <input id="subject" name="subject" value="{{ old('subject', $quote->subject ?? '') }}" placeholder="Proposal title or commercial summary">
            </div>
            <div class="crm-field">
                <label for="document_discount_type">Document discount</label>
                @if ($settings->allow_document_discounts)
                    <select id="document_discount_type" name="document_discount_type" data-document-discount-type>
                        @foreach ($discountTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('document_discount_type', $quote->document_discount_type ?? 'none') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input value="Document discounts disabled in commercial settings" disabled>
                    <input type="hidden" name="document_discount_type" value="none">
                @endif
            </div>
            <div class="crm-field">
                <label for="document_discount_value">Discount value</label>
                @if ($settings->allow_document_discounts)
                    <input id="document_discount_value" name="document_discount_value" type="number" step="0.01" min="0" value="{{ old('document_discount_value', isset($quote) ? number_format((float) $quote->document_discount_value, 2, '.', '') : '0.00') }}" data-document-discount-value>
                @else
                    <input value="0.00" disabled>
                    <input type="hidden" name="document_discount_value" value="0">
                @endif
            </div>
            <div class="crm-field full">
                <label for="notes">Internal notes</label>
                <textarea id="notes" name="notes" placeholder="Add internal selling notes, scope assumptions, or negotiation context">{{ old('notes', $quote->notes ?? '') }}</textarea>
            </div>
            <div class="crm-field full">
                <label for="terms">Quote terms</label>
                <textarea id="terms" name="terms" placeholder="Add commercial terms, implementation assumptions, or validity notes">{{ old('terms', $quote->terms ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Line items</p>
                <h2>Quote composition</h2>
                <p>Mix catalog products with manual commercial lines. Values below are snapshotted into the quote on save.</p>
            </div>
            <div class="crm-page-tools">
                <button type="button" class="btn btn-light crm-btn-light" id="add-quote-line">
                    <i class="bx bx-plus"></i> Add line
                </button>
            </div>
        </div>

        <div class="crm-list" id="quote-line-list">
            @foreach ($formLineItems as $index => $item)
                <div class="crm-card" data-quote-line data-line-index="{{ $index }}" style="padding: 18px;">
                    <div class="crm-inline" style="justify-content: space-between; margin-bottom: 16px;">
                        <strong data-line-label>Line {{ $loop->iteration }}</strong>
                        <button type="button" class="btn btn-light crm-btn-light" data-remove-line>
                            <i class="bx bx-trash"></i> Remove
                        </button>
                    </div>

                    <div class="crm-field-grid">
                        <div class="crm-field">
                            <label>Catalog product</label>
                            <select name="items[{{ $index }}][product_id]" data-line-product>
                                <option value="">Custom line</option>
                                @php
                                    $historicalProduct = $historicalInactiveProducts->get((int) ($item['product_id'] ?? 0));
                                @endphp
                                @if ($historicalProduct)
                                    <option value="{{ $historicalProduct['id'] }}" selected>
                                        {{ $historicalProduct['name'] }}{{ $historicalProduct['code'] ? ' (' . $historicalProduct['code'] . ')' : '' }} [Inactive]
                                    </option>
                                @endif
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected((int) ($item['product_id'] ?? 0) === $product->id)>{{ $product->name }}{{ $product->code ? ' (' . $product->code . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="crm-field">
                            <label>Item name</label>
                            <input name="items[{{ $index }}][item_name]" value="{{ $item['item_name'] ?? '' }}" data-line-name required>
                        </div>
                        <div class="crm-field">
                            <label>Unit label</label>
                            <input name="items[{{ $index }}][unit_label]" value="{{ $item['unit_label'] ?? 'unit' }}" data-line-unit-label required>
                        </div>
                        <div class="crm-field">
                            <label>Quantity</label>
                            <input name="items[{{ $index }}][quantity]" type="number" step="0.01" min="0.01" value="{{ $item['quantity'] ?? '1.00' }}" data-line-quantity required>
                        </div>
                        <div class="crm-field">
                            <label>Unit price</label>
                            <input name="items[{{ $index }}][unit_price]" type="number" step="0.01" min="0" value="{{ $item['unit_price'] ?? '0.00' }}" data-line-unit-price required>
                        </div>
                        <div class="crm-field">
                            <label>Tax rate (%)</label>
                            <input name="items[{{ $index }}][tax_rate]" type="number" step="0.01" min="0" max="100" value="{{ $item['tax_rate'] ?? number_format((float) $settings->default_tax_rate, 2, '.', '') }}" data-line-tax-rate>
                        </div>
                        <div class="crm-field">
                            <label>Line discount</label>
                            @if ($settings->allow_line_discounts)
                                <select name="items[{{ $index }}][discount_type]" data-line-discount-type>
                                    @foreach ($discountTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(($item['discount_type'] ?? 'none') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input value="Line discounts disabled in commercial settings" disabled>
                                <input type="hidden" name="items[{{ $index }}][discount_type]" value="none" data-line-discount-type>
                            @endif
                        </div>
                        <div class="crm-field">
                            <label>Discount value</label>
                            @if ($settings->allow_line_discounts)
                                <input name="items[{{ $index }}][discount_value]" type="number" step="0.01" min="0" value="{{ $item['discount_value'] ?? '0.00' }}" data-line-discount-value>
                            @else
                                <input value="0.00" disabled>
                                <input type="hidden" name="items[{{ $index }}][discount_value]" value="0" data-line-discount-value>
                            @endif
                        </div>
                        <div class="crm-field full">
                            <label>Description</label>
                            <textarea name="items[{{ $index }}][item_description]" data-line-description placeholder="Optional scope or delivery detail">{{ $item['item_description'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="crm-inline" style="justify-content: space-between; margin-top: 14px;">
                        <div class="crm-inline">
                            <span class="crm-pill muted">Gross <span data-line-gross>0.00</span></span>
                            <span class="crm-pill muted">Discount <span data-line-discount>0.00</span></span>
                            <span class="crm-pill muted">Tax <span data-line-tax>0.00</span></span>
                        </div>
                        <span class="crm-pill primary">Line total <span data-line-total>0.00</span></span>
                    </div>
                </div>
            @endforeach
        </div>

        <template id="quote-line-template">
            <div class="crm-card" data-quote-line data-line-index="__INDEX__" style="padding: 18px;">
                <div class="crm-inline" style="justify-content: space-between; margin-bottom: 16px;">
                    <strong data-line-label>Line __NUMBER__</strong>
                    <button type="button" class="btn btn-light crm-btn-light" data-remove-line>
                        <i class="bx bx-trash"></i> Remove
                    </button>
                </div>

                <div class="crm-field-grid">
                    <div class="crm-field">
                        <label>Catalog product</label>
                        <select name="items[__INDEX__][product_id]" data-line-product>
                            <option value="">Custom line</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}{{ $product->code ? ' (' . $product->code . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label>Item name</label>
                        <input name="items[__INDEX__][item_name]" value="" data-line-name required>
                    </div>
                    <div class="crm-field">
                        <label>Unit label</label>
                        <input name="items[__INDEX__][unit_label]" value="unit" data-line-unit-label required>
                    </div>
                    <div class="crm-field">
                        <label>Quantity</label>
                        <input name="items[__INDEX__][quantity]" type="number" step="0.01" min="0.01" value="1.00" data-line-quantity required>
                    </div>
                    <div class="crm-field">
                        <label>Unit price</label>
                        <input name="items[__INDEX__][unit_price]" type="number" step="0.01" min="0" value="0.00" data-line-unit-price required>
                    </div>
                    <div class="crm-field">
                        <label>Tax rate (%)</label>
                        <input name="items[__INDEX__][tax_rate]" type="number" step="0.01" min="0" max="100" value="{{ number_format((float) $settings->default_tax_rate, 2, '.', '') }}" data-line-tax-rate>
                    </div>
                    <div class="crm-field">
                        <label>Line discount</label>
                        @if ($settings->allow_line_discounts)
                            <select name="items[__INDEX__][discount_type]" data-line-discount-type>
                                @foreach ($discountTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($value === 'none')>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input value="Line discounts disabled in commercial settings" disabled>
                            <input type="hidden" name="items[__INDEX__][discount_type]" value="none" data-line-discount-type>
                        @endif
                    </div>
                    <div class="crm-field">
                        <label>Discount value</label>
                        @if ($settings->allow_line_discounts)
                            <input name="items[__INDEX__][discount_value]" type="number" step="0.01" min="0" value="0.00" data-line-discount-value>
                        @else
                            <input value="0.00" disabled>
                            <input type="hidden" name="items[__INDEX__][discount_value]" value="0" data-line-discount-value>
                        @endif
                    </div>
                    <div class="crm-field full">
                        <label>Description</label>
                        <textarea name="items[__INDEX__][item_description]" data-line-description placeholder="Optional scope or delivery detail"></textarea>
                    </div>
                </div>

                <div class="crm-inline" style="justify-content: space-between; margin-top: 14px;">
                    <div class="crm-inline">
                        <span class="crm-pill muted">Gross <span data-line-gross>0.00</span></span>
                        <span class="crm-pill muted">Discount <span data-line-discount>0.00</span></span>
                        <span class="crm-pill muted">Tax <span data-line-tax>0.00</span></span>
                    </div>
                    <span class="crm-pill primary">Line total <span data-line-total>0.00</span></span>
                </div>
            </div>
        </template>
    </section>

    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Preview</p>
                <h2>Calculated totals</h2>
                <p>The server recalculates these values on save using the same discount and tax rules.</p>
            </div>
        </div>

        <div class="crm-grid cols-4">
            <div class="crm-metric">
                <span>Subtotal</span>
                <strong data-preview-subtotal>0.00</strong>
            </div>
            <div class="crm-metric">
                <span>Document discount</span>
                <strong data-preview-document-discount>0.00</strong>
            </div>
            <div class="crm-metric">
                <span>Tax</span>
                <strong data-preview-tax>0.00</strong>
            </div>
            <div class="crm-metric">
                <span>Total</span>
                <strong data-preview-total>0.00</strong>
            </div>
        </div>
    </section>

    <div class="form-actions">
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="{{ $submitIcon ?? 'fas fa-save' }}"></i> {{ $submitLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('quote-form');

        if (!form) {
            return;
        }

        var contacts = @json($contactOptions);
        var salesRequests = @json($salesRequestOptions);
        var products = @json($productOptions);
        var currencies = @json($currencyOptions);
        var lineTemplate = document.getElementById('quote-line-template');
        var lineList = document.getElementById('quote-line-list');
        var accountTypeSelect = form.querySelector('[data-account-type]');
        var leadSelect = form.querySelector('[data-account-select="lead"]');
        var customerSelect = form.querySelector('[data-account-select="customer"]');
        var contactSelect = form.querySelector('[data-contact-select]');
        var requestSelect = form.querySelector('[data-request-select]');
        var currencySelect = form.querySelector('[data-currency-select]');
        var documentTaxRate = form.querySelector('[data-document-tax-rate]');
        var documentDiscountType = form.querySelector('[data-document-discount-type]');
        var documentDiscountValue = form.querySelector('[data-document-discount-value]');
        var nextLineIndex = Array.from(lineList.querySelectorAll('[data-quote-line]')).reduce(function (max, line) {
            return Math.max(max, parseInt(line.getAttribute('data-line-index'), 10) || 0);
        }, -1) + 1;

        function selectedAccountId() {
            if (accountTypeSelect.value === 'lead') {
                return leadSelect.value;
            }

            if (accountTypeSelect.value === 'customer') {
                return customerSelect.value;
            }

            return '';
        }

        function currentPrecision() {
            var selectedCurrency = currencies[currencySelect.value] || null;

            return selectedCurrency ? selectedCurrency.precision : 2;
        }

        function round(value, precision) {
            var factor = Math.pow(10, precision);

            return Math.round((Number(value) || 0) * factor) / factor;
        }

        function formatNumber(value) {
            return round(value, currentPrecision()).toFixed(currentPrecision());
        }

        function discountAmount(baseAmount, type, value) {
            baseAmount = Math.max(0, Number(baseAmount) || 0);
            value = Math.max(0, Number(value) || 0);

            if (type === 'fixed') {
                return Math.min(baseAmount, round(value, currentPrecision()));
            }

            if (type === 'percent') {
                return Math.min(baseAmount, round(baseAmount * (value / 100), currentPrecision()));
            }

            return 0;
        }

        function allocateAmount(baseAmounts, discountAmountValue) {
            discountAmountValue = Math.max(0, round(discountAmountValue, currentPrecision()));

            var totalBase = round(baseAmounts.reduce(function (sum, value) {
                return sum + (Number(value) || 0);
            }, 0), currentPrecision());

            if (discountAmountValue === 0 || totalBase === 0) {
                return baseAmounts.map(function () { return 0; });
            }

            var remaining = discountAmountValue;

            return baseAmounts.map(function (baseAmount, index) {
                baseAmount = Math.max(0, round(baseAmount, currentPrecision()));

                if (index === baseAmounts.length - 1) {
                    return Math.min(baseAmount, round(remaining, currentPrecision()));
                }

                var share = round(discountAmountValue * (baseAmount / totalBase), currentPrecision());
                share = Math.min(baseAmount, share, remaining);
                remaining = round(remaining - share, currentPrecision());

                return share;
            });
        }

        function syncAccountPanels() {
            form.querySelectorAll('[data-account-panel]').forEach(function (panel) {
                var isActive = panel.getAttribute('data-account-panel') === accountTypeSelect.value;
                panel.style.display = isActive ? '' : 'none';
                panel.querySelector('select').disabled = !isActive;
            });
        }

        function repopulateSelect(select, options, placeholder, selectedValue) {
            select.innerHTML = '';

            var placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            select.appendChild(placeholderOption);

            options.forEach(function (option) {
                var optionNode = document.createElement('option');
                optionNode.value = option.id;
                optionNode.textContent = option.label || option.name;

                if (String(selectedValue || '') === String(option.id)) {
                    optionNode.selected = true;
                }

                select.appendChild(optionNode);
            });
        }

        function syncContextSelects() {
            syncAccountPanels();

            var accountId = selectedAccountId();
            var selectedContact = contactSelect.getAttribute('data-selected') || contactSelect.value;
            var selectedRequest = requestSelect.getAttribute('data-selected') || requestSelect.value;

            var filteredContacts = contacts
                .filter(function (contact) {
                    if (accountTypeSelect.value === 'contact') {
                        return true;
                    }

                    if (!accountId) {
                        return false;
                    }

                    return accountTypeSelect.value === 'lead'
                        ? String(contact.lead_id || '') === String(accountId)
                        : String(contact.customer_id || '') === String(accountId);
                })
                .map(function (contact) {
                    return {
                        id: contact.id,
                        label: contact.label || contact.name
                    };
                });

            repopulateSelect(contactSelect, filteredContacts, 'Select a contact', selectedContact);

            var contactId = accountTypeSelect.value === 'contact' ? contactSelect.value : '';
            var filteredRequests = salesRequests
                .filter(function (crmRequest) {
                    if (accountTypeSelect.value === 'contact') {
                        return contactId
                            && String(crmRequest.contact_id || '') === String(contactId);
                    }

                    if (!accountId) {
                        return false;
                    }

                    return accountTypeSelect.value === 'lead'
                        ? String(crmRequest.lead_id || '') === String(accountId)
                        : String(crmRequest.customer_id || '') === String(accountId);
                })
                .map(function (crmRequest) {
                    return {
                        id: crmRequest.id,
                        label: crmRequest.title
                    };
                });

            repopulateSelect(requestSelect, filteredRequests, 'No linked request', selectedRequest);
            contactSelect.setAttribute('data-selected', '');
            requestSelect.setAttribute('data-selected', '');
        }

        function lineNodes() {
            return Array.from(lineList.querySelectorAll('[data-quote-line]'));
        }

        function updateLineLabels() {
            lineNodes().forEach(function (line, index) {
                var label = line.querySelector('[data-line-label]');

                if (label) {
                    label.textContent = 'Line ' + (index + 1);
                }
            });
        }

        function syncTaxControls() {
            var useDocumentTax = lineNodes().length > 1;

            lineNodes().forEach(function (line) {
                var lineTaxRate = line.querySelector('[data-line-tax-rate]');

                if (lineTaxRate) {
                    lineTaxRate.disabled = useDocumentTax;
                }
            });
        }

        function applyProductDefaults(line) {
            var productSelect = line.querySelector('[data-line-product]');
            var product = products[productSelect.value] || null;

            if (!product) {
                return;
            }

            line.querySelector('[data-line-name]').value = product.name || '';
            line.querySelector('[data-line-description]').value = product.description || '';
            line.querySelector('[data-line-unit-label]').value = product.unit_label || 'unit';
            line.querySelector('[data-line-unit-price]').value = formatNumber(product.unit_price || 0);
            line.querySelector('[data-line-tax-rate]').value = Number(product.tax_rate || 0).toFixed(2);
        }

        function calculateTotals() {
            syncTaxControls();

            var lines = lineNodes().map(function (line) {
                var quantity = Number(line.querySelector('[data-line-quantity]').value || 0);
                var unitPrice = Number(line.querySelector('[data-line-unit-price]').value || 0);
                var discountType = (line.querySelector('[data-line-discount-type]') || {}).value || 'none';
                var discountValue = Number((line.querySelector('[data-line-discount-value]') || {}).value || 0);
                var taxRate = Number(line.querySelector('[data-line-tax-rate]').value || 0);
                var grossAmount = round(quantity * unitPrice, currentPrecision());
                var lineDiscountAmount = discountAmount(grossAmount, discountType, discountValue);
                var netBeforeDocumentDiscount = round(grossAmount - lineDiscountAmount, currentPrecision());

                return {
                    node: line,
                    quantity: quantity,
                    unit_price: unitPrice,
                    discount_type: discountType,
                    discount_value: discountValue,
                    tax_rate: taxRate,
                    gross_amount: grossAmount,
                    line_discount_amount: lineDiscountAmount,
                    net_before_document_discount: netBeforeDocumentDiscount
                };
            });

            var documentDiscountAmount = discountAmount(
                lines.reduce(function (sum, line) {
                    return sum + line.net_before_document_discount;
                }, 0),
                documentDiscountType ? documentDiscountType.value : 'none',
                documentDiscountValue ? documentDiscountValue.value : 0
            );

            var allocatedDocumentDiscounts = allocateAmount(
                lines.map(function (line) { return line.net_before_document_discount; }),
                documentDiscountAmount
            );

            var subtotalAmount = 0;
            var taxAmount = 0;
            var totalAmount = 0;
            var useDocumentTax = lines.length > 1;
            var documentTaxAmount = 0;
            var allocatedDocumentTaxes = [];

            lines.forEach(function (line, index) {
                var allocatedDocumentDiscount = allocatedDocumentDiscounts[index] || 0;
                var totalDiscount = round(line.line_discount_amount + allocatedDocumentDiscount, currentPrecision());
                var netAmount = round(line.gross_amount - totalDiscount, currentPrecision());

                line.total_discount_amount = totalDiscount;
                line.net_amount = netAmount;
                subtotalAmount = round(subtotalAmount + netAmount, currentPrecision());
            });

            if (useDocumentTax) {
                documentTaxAmount = round(subtotalAmount * ((Number(documentTaxRate ? documentTaxRate.value : 0) || 0) / 100), currentPrecision());
                allocatedDocumentTaxes = allocateAmount(lines.map(function (line) { return line.net_amount; }), documentTaxAmount);
            }

            lines.forEach(function (line, index) {
                var lineTaxAmount = useDocumentTax
                    ? (allocatedDocumentTaxes[index] || 0)
                    : round(line.net_amount * (line.tax_rate / 100), currentPrecision());
                var lineTotalAmount = round(line.net_amount + lineTaxAmount, currentPrecision());

                taxAmount = round(taxAmount + lineTaxAmount, currentPrecision());
                totalAmount = round(totalAmount + lineTotalAmount, currentPrecision());

                line.node.querySelector('[data-line-gross]').textContent = formatNumber(line.gross_amount);
                line.node.querySelector('[data-line-discount]').textContent = formatNumber(line.total_discount_amount);
                line.node.querySelector('[data-line-tax]').textContent = formatNumber(lineTaxAmount);
                line.node.querySelector('[data-line-total]').textContent = formatNumber(lineTotalAmount);
            });

            form.querySelector('[data-preview-subtotal]').textContent = formatNumber(subtotalAmount);
            form.querySelector('[data-preview-document-discount]').textContent = formatNumber(documentDiscountAmount);
            form.querySelector('[data-preview-tax]').textContent = formatNumber(taxAmount);
            form.querySelector('[data-preview-total]').textContent = formatNumber(totalAmount);
        }

        function wireLine(line) {
            line.addEventListener('input', function () {
                calculateTotals();
            });

            line.addEventListener('change', function (event) {
                if (event.target.matches('[data-line-product]')) {
                    applyProductDefaults(line);
                }

                calculateTotals();
            });

            var removeButton = line.querySelector('[data-remove-line]');

            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    var lines = lineNodes();

                    if (lines.length === 1) {
                        line.querySelector('[data-line-product]').value = '';
                        line.querySelector('[data-line-name]').value = '';
                        line.querySelector('[data-line-description]').value = '';
                        line.querySelector('[data-line-unit-label]').value = 'unit';
                        line.querySelector('[data-line-quantity]').value = '1.00';
                        line.querySelector('[data-line-unit-price]').value = '0.00';
                        line.querySelector('[data-line-tax-rate]').value = Number({{ (float) $settings->default_tax_rate }}).toFixed(2);

                        if (line.querySelector('[data-line-discount-type]')) {
                            line.querySelector('[data-line-discount-type]').value = 'none';
                        }

                        if (line.querySelector('[data-line-discount-value]')) {
                            line.querySelector('[data-line-discount-value]').value = '0.00';
                        }
                    } else {
                        line.remove();
                        updateLineLabels();
                    }

                    calculateTotals();
                });
            }
        }

        document.getElementById('add-quote-line').addEventListener('click', function () {
            var markup = lineTemplate.innerHTML
                .replace(/__INDEX__/g, String(nextLineIndex))
                .replace(/__NUMBER__/g, String(lineNodes().length + 1));
            var wrapper = document.createElement('div');

            wrapper.innerHTML = markup.trim();
            lineList.appendChild(wrapper.firstElementChild);
            wireLine(lineList.lastElementChild);
            nextLineIndex += 1;
            updateLineLabels();
            calculateTotals();
        });

        lineNodes().forEach(wireLine);

        accountTypeSelect.addEventListener('change', function () {
            syncContextSelects();
        });

        leadSelect.addEventListener('change', syncContextSelects);
        customerSelect.addEventListener('change', syncContextSelects);
        contactSelect.addEventListener('change', syncContextSelects);
        currencySelect.addEventListener('change', calculateTotals);

        if (documentTaxRate) {
            documentTaxRate.addEventListener('input', calculateTotals);
        }

        if (documentDiscountType) {
            documentDiscountType.addEventListener('change', calculateTotals);
        }

        if (documentDiscountValue) {
            documentDiscountValue.addEventListener('input', calculateTotals);
        }

        syncContextSelects();
        calculateTotals();
    });
</script>
