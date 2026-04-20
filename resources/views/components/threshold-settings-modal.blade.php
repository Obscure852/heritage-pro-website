{{-- Threshold Settings Modal Component --}}
{{-- Usage: @include('components.threshold-settings-modal', ['thresholdSettings' => $thresholdSettings]) --}}

<div class="modal fade" id="thresholdSettingsModal" tabindex="-1" aria-labelledby="thresholdSettingsModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 3px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header border-0" style="padding: 1.25rem 1.5rem 0.5rem;">
                <h5 class="modal-title fw-bold" id="thresholdSettingsModalLabel" style="color: #2d3748;">
                    <i class="fa-solid fa-sliders me-2"></i>Passing Threshold Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                <div class="alert alert-info border-0 d-flex align-items-start mb-3"
                    style="background: #e0f2fe; padding: 0.75rem; border-radius: 3px;">
                    <i class="fa-solid fa-circle-info"
                        style="font-size: 16px; color: #0284c7; margin-right: 0.5rem; margin-top: 2px;"></i>
                    <small style="color: #075985; line-height: 1.5;">
                        Configure score thresholds to highlight students below certain percentages.
                        These are your personal preferences and will override system defaults.
                    </small>
                </div>

                <form id="thresholdSettingsForm">
                    {{-- Enable/Disable Toggle --}}
                    <div class="mb-4 p-3" style="background: #f8fafc; border-radius: 3px;">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox" role="switch" id="highlightEnabled"
                                style="width: 3em; height: 1.5em; border-radius: 1em;">
                            <label class="form-check-label fw-semibold" for="highlightEnabled">
                                Enable threshold highlighting
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1 ms-5">
                            When enabled, score inputs will be highlighted based on the thresholds below
                        </small>
                    </div>

                    {{-- Thresholds Container --}}
                    <div id="thresholdOptions">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-semibold mb-0">Threshold Levels</label>
                            <small class="text-muted">Edit the 3 default threshold levels</small>
                        </div>

                        <div id="thresholdsContainer">
                            {{-- Threshold rows will be dynamically populated --}}
                        </div>

                        {{-- Quick Color Presets --}}
                        <div class="mt-3 p-3" style="background: #f8fafc; border-radius: 3px;">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;">Quick Color
                                Presets</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm preset-color" data-color="#fee2e2"
                                    style="background: #fee2e2; border: 2px solid #fca5a5; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Light Red (Failing)"></button>
                                <button type="button" class="btn btn-sm preset-color" data-color="#fef3c7"
                                    style="background: #fef3c7; border: 2px solid #fbbf24; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Amber (Warning)"></button>
                                <button type="button" class="btn btn-sm preset-color" data-color="#fefce8"
                                    style="background: #fefce8; border: 2px solid #facc15; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Light Yellow (Caution)"></button>
                                <button type="button" class="btn btn-sm preset-color" data-color="#fce7f3"
                                    style="background: #fce7f3; border: 2px solid #f9a8d4; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Pink"></button>
                                <button type="button" class="btn btn-sm preset-color" data-color="#e0e7ff"
                                    style="background: #e0e7ff; border: 2px solid #a5b4fc; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Indigo"></button>
                                <button type="button" class="btn btn-sm preset-color" data-color="#d1fae5"
                                    style="background: #d1fae5; border: 2px solid #6ee7b7; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Green"></button>
                                <button type="button" class="btn btn-sm preset-color" data-color="#fed7aa"
                                    style="background: #fed7aa; border: 2px solid #fdba74; width: 32px; height: 32px; border-radius: 3px;"
                                    title="Orange"></button>
                            </div>
                            <small class="text-muted d-block mt-2">Click a color, then click on a threshold's color
                                picker to apply</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 d-flex gap-2 justify-content-end"
                style="padding: 1.25rem 1.5rem; background: #f9fafb; border-radius: 0 0 3px 3px;">
                <button type="button" class="btn btn-secondary btn-loading" id="resetThresholdDefaults">
                    <span class="btn-text"><i class="bx bx-reset me-1"></i> Reset to Defaults</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Resetting...
                    </span>
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-loading" id="saveThresholdSettings">
                    <span class="btn-text"><i class="fas fa-save me-1"></i> Save Changes</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Threshold Row Template --}}
<template id="thresholdRowTemplate">
    <div class="threshold-row row align-items-center mb-2 p-2"
        style="background: #fff; border: 1px solid #e5e7eb; border-radius: 3px;">
        <div class="col-2">
            <span class="threshold-name-display badge"
                style="font-size: 12px; padding: 6px 10px; text-transform: capitalize; border-radius: 3px;"></span>
        </div>
        <div class="col-8 d-flex align-items-center gap-1 justify-content-center">
            <span style="color: #6b7280; font-size: 13px;">≤</span>
            <input type="number" class="form-control threshold-percentage" placeholder="0" min="0"
                max="100" step="1" style="text-align: center; padding: 8px 12px; font-size: 14px;">
            <span style="color: #6b7280; font-size: 13px;">%</span>
        </div>
        <div class="col-2 d-flex justify-content-center">
            <input type="color" class="form-control form-control-color threshold-color"
                style="width: 36px; height: 32px; padding: 2px; cursor: pointer; border-radius: 3px;"
                title="Choose color">
        </div>
        <input type="hidden" class="threshold-name">
    </div>
</template>

<style>
    /* Modal container - 3px radius throughout */
    #thresholdSettingsModal .modal-content {
        border-radius: 3px !important;
    }

    /* Form controls (matching admission-new) */
    #thresholdSettingsModal .form-control,
    #thresholdSettingsModal .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        transition: all 0.2s;
    }

    #thresholdSettingsModal .form-control:focus,
    #thresholdSettingsModal .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    #thresholdSettingsModal .input-group-text {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        background: #f8f9fa;
    }

    #thresholdSettingsModal .input-group>.form-control {
        border-radius: 0;
    }

    #thresholdSettingsModal .input-group>.input-group-text:first-child {
        border-radius: 3px 0 0 3px;
        border-right: none;
    }

    #thresholdSettingsModal .input-group>.input-group-text:last-child {
        border-radius: 0 3px 3px 0;
        border-left: none;
    }

    /* Color picker input */
    #thresholdSettingsModal .form-control-color {
        padding: 4px;
        border-radius: 3px;
        width: 44px;
        height: 38px;
    }

    /* Threshold rows */
    #thresholdSettingsModal .threshold-row {
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    #thresholdSettingsModal .threshold-row:hover {
        background: #f8fafc !important;
        border-color: #d1d5db !important;
    }

    /* Alert box */
    #thresholdSettingsModal .alert {
        border-radius: 3px;
    }

    /* Toggle switch container */
    #thresholdSettingsModal .form-check-input {
        border-radius: 3px;
    }

    /* Quick color presets */
    #thresholdSettingsModal .preset-color {
        cursor: pointer;
        border-radius: 3px !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    #thresholdSettingsModal .preset-color:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    #thresholdSettingsModal .preset-color.selected {
        box-shadow: 0 0 0 3px #4e73df;
    }

    /* Preset container */
    #thresholdSettingsModal #thresholdOptions>div:last-child {
        border-radius: 3px;
    }

    /* Toggle container */
    #thresholdSettingsModal .mb-4.p-3 {
        border-radius: 3px;
    }

    /* Threshold name badge */
    #thresholdSettingsModal .threshold-name-display {
        border-radius: 3px;
    }

    /* Score input threshold highlighting classes */
    .score-input.threshold-failing {
        background-color: #fee2e2 !important;
        border-color: #fca5a5 !important;
    }

    .score-input.threshold-warning {
        background-color: #fef3c7 !important;
        border-color: #fbbf24 !important;
    }

    .score-input.threshold-caution {
        background-color: #fefce8 !important;
        border-color: #facc15 !important;
    }

    /* Dynamic threshold highlighting via inline styles takes precedence */
    .score-input[data-threshold-color] {
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    /* Button styles (matching admission-new) */
    #thresholdSettingsModal .modal-footer .btn {
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 3px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    #thresholdSettingsModal .modal-footer .btn-primary {
        background: linear-gradient(135deg, #4e73df 0%, #5156BE 100%);
        color: white;
        min-width: 140px;
    }

    #thresholdSettingsModal .modal-footer .btn-primary:hover {
        background: linear-gradient(135deg, #3d5fc7 0%, #4145a8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
    }

    #thresholdSettingsModal .modal-footer .btn-secondary {
        background: #6c757d;
        color: white;
    }

    #thresholdSettingsModal .modal-footer .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    #thresholdSettingsModal .modal-footer .btn-light {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
    }

    #thresholdSettingsModal .modal-footer .btn-light:hover {
        background: #e9ecef;
    }

    /* Button loading animation styles */
    #thresholdSettingsModal .btn-loading.loading .btn-text {
        display: none;
    }

    #thresholdSettingsModal .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    #thresholdSettingsModal .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Stop bx-reset icon rotation */
    #thresholdSettingsModal .bx-reset {
        animation: none !important;
        transform: none !important;
    }
</style>

<script>
    (function() {
        'use strict';

        // Threshold settings module
        window.ThresholdSettings = window.ThresholdSettings || {};

        // Current settings (will be initialized from server data)
        var currentSettings = {
            highlight_enabled: true,
            thresholds: []
        };

        // Default thresholds
        var defaultThresholds = [{
                name: 'failing',
                max_percentage: 39,
                color: '#fee2e2'
            },
            {
                name: 'warning',
                max_percentage: 49,
                color: '#fef3c7'
            },
            {
                name: 'caution',
                max_percentage: 59,
                color: '#fefce8'
            }
        ];

        // Selected preset color for quick application
        var selectedPresetColor = null;

        /**
         * Initialize the threshold settings module
         */
        ThresholdSettings.init = function(settings) {
            currentSettings = settings || {
                highlight_enabled: true,
                thresholds: JSON.parse(JSON.stringify(defaultThresholds))
            };

            // Ensure thresholds exist
            if (!currentSettings.thresholds || currentSettings.thresholds.length === 0) {
                currentSettings.thresholds = JSON.parse(JSON.stringify(defaultThresholds));
            }

            // Apply initial highlighting
            ThresholdSettings.applyHighlightingToAll();

            // Setup modal event listeners
            setupModalListeners();
        };

        /**
         * Get current threshold settings
         */
        ThresholdSettings.getSettings = function() {
            return currentSettings;
        };

        /**
         * Apply threshold highlighting to a single score input
         */
        ThresholdSettings.applyHighlighting = function(input) {
            // Remove any existing threshold classes/styles
            input.classList.remove('threshold-failing', 'threshold-warning', 'threshold-caution');
            input.style.removeProperty('background-color');
            input.style.removeProperty('border-color');
            input.removeAttribute('data-threshold-color');

            // Skip if highlighting is disabled
            if (!currentSettings.highlight_enabled) {
                return;
            }

            // Get score value
            var value = parseFloat(input.value);
            if (isNaN(value) || input.value === '') {
                return;
            }

            // Get out_of value - try multiple approaches for robustness
            var outOfInput = input.parentElement.querySelector('input[name*="[out_of]"]');

            // If not found in parent, try the closest td cell
            if (!outOfInput) {
                var tdCell = input.closest('td');
                if (tdCell) {
                    outOfInput = tdCell.querySelector('input[name*="[out_of]"]');
                }
            }

            var outOf = outOfInput ? parseFloat(outOfInput.value) : null;

            // Fallback: try to get from placeholder attribute
            if (!outOf || outOf === 0) {
                var placeholder = input.getAttribute('placeholder');
                if (placeholder && !isNaN(parseFloat(placeholder))) {
                    outOf = parseFloat(placeholder);
                }
            }

            if (!outOf || outOf === 0) {
                return;
            }

            // Calculate percentage
            var percentage = (value / outOf) * 100;

            // Find matching threshold (sorted by max_percentage ascending)
            var thresholds = currentSettings.thresholds.slice().sort(function(a, b) {
                return a.max_percentage - b.max_percentage;
            });

            for (var i = 0; i < thresholds.length; i++) {
                if (percentage <= thresholds[i].max_percentage) {
                    // Use setProperty with important to override any CSS rules
                    input.style.setProperty('background-color', thresholds[i].color, 'important');
                    input.style.setProperty('border-color', adjustColorBrightness(thresholds[i].color, -20),
                        'important');
                    input.setAttribute('data-threshold-color', thresholds[i].color);
                    break;
                }
            }
        };

        /**
         * Apply highlighting to all score inputs
         */
        ThresholdSettings.applyHighlightingToAll = function() {
            document.querySelectorAll('.score-input').forEach(function(input) {
                ThresholdSettings.applyHighlighting(input);
            });
        };

        /**
         * Setup modal event listeners
         */
        function setupModalListeners() {
            var modal = document.getElementById('thresholdSettingsModal');
            if (!modal) return;

            // Populate modal when shown
            modal.addEventListener('show.bs.modal', function() {
                populateModal();
            });

            // Enable/disable toggle
            document.getElementById('highlightEnabled').addEventListener('change', function() {
                var options = document.getElementById('thresholdOptions');
                options.style.opacity = this.checked ? '1' : '0.5';
                options.style.pointerEvents = this.checked ? 'auto' : 'none';
            });

            // Preset color selection
            document.querySelectorAll('.preset-color').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.preset-color').forEach(function(b) {
                        b.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    selectedPresetColor = this.dataset.color;
                });
            });

            // Apply preset color to color picker when clicked
            document.getElementById('thresholdsContainer').addEventListener('click', function(e) {
                if (e.target.classList.contains('threshold-color') && selectedPresetColor) {
                    e.target.value = selectedPresetColor;
                    // Update the badge color to preview
                    var row = e.target.closest('.threshold-row');
                    if (row) {
                        var badge = row.querySelector('.threshold-name-display');
                        if (badge) {
                            badge.style.backgroundColor = selectedPresetColor;
                            badge.style.borderColor = adjustColorBrightness(selectedPresetColor, -20);
                        }
                    }
                }
            });

            // Update badge color when color picker changes
            document.getElementById('thresholdsContainer').addEventListener('input', function(e) {
                if (e.target.classList.contains('threshold-color')) {
                    var row = e.target.closest('.threshold-row');
                    if (row) {
                        var badge = row.querySelector('.threshold-name-display');
                        if (badge) {
                            badge.style.backgroundColor = e.target.value;
                            badge.style.borderColor = adjustColorBrightness(e.target.value, -20);
                        }
                    }
                }
            });

            // Save button
            document.getElementById('saveThresholdSettings').addEventListener('click', saveSettings);

            // Reset button
            document.getElementById('resetThresholdDefaults').addEventListener('click', resetToDefaults);
        }

        /**
         * Populate modal with current settings
         */
        function populateModal() {
            // Set enable toggle
            var enableToggle = document.getElementById('highlightEnabled');
            enableToggle.checked = currentSettings.highlight_enabled;
            enableToggle.dispatchEvent(new Event('change'));

            // Clear and populate thresholds
            var container = document.getElementById('thresholdsContainer');
            container.innerHTML = '';

            // Always use exactly 3 threshold levels (teachers can only edit, not add/remove)
            var thresholds = currentSettings.thresholds.length === 3 ?
                currentSettings.thresholds :
                defaultThresholds;

            // Sort by max_percentage for consistent display
            thresholds = thresholds.slice().sort(function(a, b) {
                return a.max_percentage - b.max_percentage;
            });

            thresholds.forEach(function(threshold) {
                addThresholdRow(threshold);
            });
        }

        /**
         * Add a threshold row to the container
         */
        function addThresholdRow(threshold) {
            var template = document.getElementById('thresholdRowTemplate');
            var clone = template.content.cloneNode(true);

            // Set the hidden name input
            clone.querySelector('.threshold-name').value = threshold.name || '';

            // Set the badge display with appropriate styling
            var badge = clone.querySelector('.threshold-name-display');
            badge.textContent = threshold.name || 'Level';
            badge.style.backgroundColor = threshold.color || '#e5e7eb';
            badge.style.borderColor = adjustColorBrightness(threshold.color || '#e5e7eb', -20);
            badge.style.color = getContrastColor(threshold.color || '#e5e7eb');

            clone.querySelector('.threshold-percentage').value = threshold.max_percentage || '';
            clone.querySelector('.threshold-color').value = threshold.color || '#e5e7eb';

            document.getElementById('thresholdsContainer').appendChild(clone);
        }

        /**
         * Get contrasting text color (dark or light) based on background
         */
        function getContrastColor(hexColor) {
            var hex = hexColor.replace('#', '');
            var r = parseInt(hex.substr(0, 2), 16);
            var g = parseInt(hex.substr(2, 2), 16);
            var b = parseInt(hex.substr(4, 2), 16);
            var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            return luminance > 0.6 ? '#374151' : '#ffffff';
        }

        /**
         * Collect thresholds from modal form
         */
        function collectThresholds() {
            var thresholds = [];
            document.querySelectorAll('.threshold-row').forEach(function(row) {
                var name = row.querySelector('.threshold-name').value.trim();
                var maxPercentage = parseFloat(row.querySelector('.threshold-percentage').value);
                var color = row.querySelector('.threshold-color').value;

                if (name && !isNaN(maxPercentage)) {
                    thresholds.push({
                        name: name,
                        max_percentage: maxPercentage,
                        color: color
                    });
                }
            });

            // Sort by max_percentage
            thresholds.sort(function(a, b) {
                return a.max_percentage - b.max_percentage;
            });

            return thresholds;
        }

        /**
         * Show loading state on button
         */
        function setButtonLoading(btn, loading) {
            if (loading) {
                btn.classList.add('loading');
                btn.disabled = true;
            } else {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        }

        /**
         * Save settings to server
         */
        function saveSettings() {
            var btn = document.getElementById('saveThresholdSettings');
            setButtonLoading(btn, true);

            var thresholds = collectThresholds();
            var highlightEnabled = document.getElementById('highlightEnabled').checked;

            // Validate
            if (highlightEnabled && thresholds.length === 0) {
                setButtonLoading(btn, false);
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please configure all threshold levels with valid percentages.'
                });
                return;
            }

            // Check for duplicate percentages
            var percentages = thresholds.map(function(t) {
                return t.max_percentage;
            });
            var uniquePercentages = [...new Set(percentages)];
            if (percentages.length !== uniquePercentages.length) {
                setButtonLoading(btn, false);
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Each threshold must have a unique max percentage value.'
                });
                return;
            }

            $.ajax({
                url: '{{ route('threshold.update-teacher-preference') }}',
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: JSON.stringify({
                    highlight_enabled: highlightEnabled,
                    thresholds: thresholds
                }),
                success: function(response) {
                    if (response.success) {
                        currentSettings.highlight_enabled = highlightEnabled;
                        currentSettings.thresholds = thresholds;

                        ThresholdSettings.applyHighlightingToAll();

                        bootstrap.Modal.getInstance(document.getElementById('thresholdSettingsModal'))
                            .hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Settings Saved',
                            text: 'Your threshold preferences have been updated.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(response.message || 'Unknown error');
                    }
                },
                error: function(xhr) {
                    var message = 'Failed to save settings. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        if (errors.thresholds) {
                            message = errors.thresholds.join('\n');
                        } else {
                            message = Object.values(errors).flat().join('\n');
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });
                },
                complete: function() {
                    setButtonLoading(btn, false);
                }
            });
        }

        /**
         * Reset settings to system defaults
         */
        function resetToDefaults() {
            Swal.fire({
                title: 'Reset to Defaults?',
                text: 'This will reset your threshold settings to system defaults.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, reset'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var btn = document.getElementById('resetThresholdDefaults');
                    setButtonLoading(btn, true);

                    $.ajax({
                        url: '{{ route('threshold.reset-teacher-preference') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                currentSettings = response.data;
                                populateModal();
                                ThresholdSettings.applyHighlightingToAll();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Reset Complete',
                                    text: 'Settings have been reset to system defaults.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to reset settings. Please try again.'
                            });
                        },
                        complete: function() {
                            setButtonLoading(btn, false);
                        }
                    });
                }
            });
        }

        /**
         * Adjust color brightness
         */
        function adjustColorBrightness(hex, percent) {
            var num = parseInt(hex.replace('#', ''), 16);
            var amt = Math.round(2.55 * percent);
            var R = (num >> 16) + amt;
            var G = (num >> 8 & 0x00FF) + amt;
            var B = (num & 0x0000FF) + amt;

            return '#' + (0x1000000 +
                (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 +
                (G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 +
                (B < 255 ? (B < 1 ? 0 : B) : 255)
            ).toString(16).slice(1);
        }

        /**
         * Robust initialization that works regardless of DOM state
         */
        function initializeThresholds() {
            // Initialize with server-provided settings if available
            @if (isset($thresholdSettings))
                ThresholdSettings.init(@json($thresholdSettings));
            @else
                ThresholdSettings.init(null);
            @endif
        }

        // Initialize when DOM is ready, or immediately if already loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeThresholds);
        } else {
            // DOM already loaded, initialize immediately
            initializeThresholds();
        }

        // Re-apply highlighting when score inputs change - use jQuery ready to ensure $ is available
        (function setupInputHandler() {
            if (typeof jQuery !== 'undefined') {
                // Use setTimeout(0) to ensure highlighting runs after other input handlers
                $(document).off('input.threshold', '.score-input').on('input.threshold', '.score-input',
                    function() {
                        var input = this;
                        setTimeout(function() {
                            ThresholdSettings.applyHighlighting(input);
                        }, 0);
                    });

                // Also re-apply on blur in case input event missed
                $(document).off('blur.threshold', '.score-input').on('blur.threshold', '.score-input',
                    function() {
                        ThresholdSettings.applyHighlighting(this);
                    });

                // Apply on change as well for better coverage
                $(document).off('change.threshold', '.score-input').on('change.threshold', '.score-input',
                    function() {
                        ThresholdSettings.applyHighlighting(this);
                    });
            } else {
                // jQuery not loaded yet, try again after a short delay
                setTimeout(setupInputHandler, 50);
            }
        })();

        // Also apply highlighting after any AJAX content loads (for dynamically loaded markbooks)
        $(document).ajaxComplete(function() {
            setTimeout(function() {
                ThresholdSettings.applyHighlightingToAll();
            }, 100);
        });
    })();
</script>
