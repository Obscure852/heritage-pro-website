@extends('layouts.master')
@section('title')
    Advanced Settings
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .btn-outline-info {
            background: transparent;
            color: #0dcaf0;
            border: 1px solid #0dcaf0;
            font-size: 13px;
            padding: 8px 16px;
        }

        .btn-outline-info:hover {
            background: #0dcaf0;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 202, 240, 0.3);
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .param-hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('timetable.index') }}">Timetable</a>
        @endslot
        @slot('title')
            Advanced Settings
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">Generation Settings</h1>
                @can('view-system-admin')
                    <a href="{{ route('timetable.generation.documentation') }}" class="btn btn-outline-info" title="Detailed documentation on the scheduling engine">
                        <i class="fas fa-info-circle"></i> Documentation
                    </a>
                @endcan
            </div>

            {{-- Help text --}}
            <div class="help-text">
                <div class="help-title">How it works</div>
                <div class="help-content">
                    The genetic algorithm uses these parameters to control how it searches for the best timetable.
                    Select a profile that matches your school size, or fine-tune individual values.
                    Changes are applied the next time you generate a timetable.
                </div>
            </div>

            {{-- Profile Section --}}
            <h5 class="section-title">Auto-Scaling Profile</h5>

            <div class="help-text mb-3">
                <div class="help-content">
                    Based on ~{{ $geneCount }} allocations, we recommend the
                    <strong>{{ $gaProfiles[$recommendedProfile]['label'] }}</strong> profile.
                </div>
            </div>

            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 24px;">
                <div class="form-group">
                    <label class="form-label" for="gaProfileSelect">Profile</label>
                    <select class="form-select" id="gaProfileSelect">
                        @foreach($gaProfiles as $key => $profile)
                            <option value="{{ $key }}" {{ $key === $recommendedProfile ? 'selected' : '' }}>
                                {{ $profile['label'] }} &mdash; {{ $profile['description'] }}
                            </option>
                        @endforeach
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="form-group d-flex align-items-end">
                    <button type="button" class="btn btn-secondary" id="applyProfileBtn">
                        <i class="fas fa-magic"></i> Apply Profile
                    </button>
                </div>
            </div>

            {{-- Parameters --}}
            <h5 class="section-title">Algorithm Parameters</h5>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="gaPopulationSize">Population Size</label>
                    <input type="number" class="form-control ga-param" id="gaPopulationSize"
                           data-key="population_size"
                           value="{{ $savedParameters['population_size'] ?? $defaultParameters['population_size'] }}"
                           min="10" max="500">
                    <div class="param-hint">Number of solutions per generation (10-500)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaMaxGenerations">Max Generations</label>
                    <input type="number" class="form-control ga-param" id="gaMaxGenerations"
                           data-key="max_generations"
                           value="{{ $savedParameters['max_generations'] ?? $defaultParameters['max_generations'] }}"
                           min="50" max="5000">
                    <div class="param-hint">Maximum iterations before stopping (50-5000)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaStagnationLimit">Stagnation Limit</label>
                    <input type="number" class="form-control ga-param" id="gaStagnationLimit"
                           data-key="stagnation_limit"
                           value="{{ $savedParameters['stagnation_limit'] ?? $defaultParameters['stagnation_limit'] }}"
                           min="5" max="500">
                    <div class="param-hint">Generations without improvement before adapting (5-500)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaRepairMoves">Repair Moves</label>
                    <input type="number" class="form-control ga-param" id="gaRepairMoves"
                           data-key="repair_moves"
                           value="{{ $savedParameters['repair_moves'] ?? $defaultParameters['repair_moves'] }}"
                           min="1" max="30">
                    <div class="param-hint">Local repair attempts per generation (1-30)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaMutationRate">Mutation Rate</label>
                    <input type="number" class="form-control ga-param" id="gaMutationRate"
                           data-key="mutation_rate"
                           value="{{ $savedParameters['mutation_rate'] ?? $defaultParameters['mutation_rate'] }}"
                           min="0.01" max="0.5" step="0.01">
                    <div class="param-hint">Probability of random changes (0.01-0.5)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaCrossoverRate">Crossover Rate</label>
                    <input type="number" class="form-control ga-param" id="gaCrossoverRate"
                           data-key="crossover_rate"
                           value="{{ $savedParameters['crossover_rate'] ?? $defaultParameters['crossover_rate'] }}"
                           min="0.1" max="1.0" step="0.05">
                    <div class="param-hint">Probability of combining parents (0.1-1.0)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaTournamentSize">Tournament Size</label>
                    <input type="number" class="form-control ga-param" id="gaTournamentSize"
                           data-key="tournament_size"
                           value="{{ $savedParameters['tournament_size'] ?? $defaultParameters['tournament_size'] }}"
                           min="2" max="20">
                    <div class="param-hint">Selection pool size (2-20)</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="gaEliteCount">Elite Count</label>
                    <input type="number" class="form-control ga-param" id="gaEliteCount"
                           data-key="elite_count"
                           value="{{ $savedParameters['elite_count'] ?? $defaultParameters['elite_count'] }}"
                           min="1" max="20">
                    <div class="param-hint">Top solutions preserved unchanged (1-20)</div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="resetParamsBtn">
                    <i class="fas fa-undo"></i> Reset to Defaults
                </button>
                <button type="button" class="btn btn-primary btn-loading" id="saveParamsBtn">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var defaultParameters = @json($defaultParameters);
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        function collectGaParams() {
            var params = {};
            $('.ga-param').each(function () {
                var key = $(this).data('key');
                var val = $(this).val();
                params[key] = ($(this).attr('step') && parseFloat($(this).attr('step')) < 1)
                    ? parseFloat(val)
                    : parseInt(val, 10);
            });
            return params;
        }

        function setGaInputs(params) {
            $('.ga-param').each(function () {
                var key = $(this).data('key');
                if (params[key] !== undefined) {
                    $(this).val(params[key]);
                }
            });
        }

        $('#saveParamsBtn').on('click', function () {
            var btn = $(this);
            btn.addClass('loading').prop('disabled', true);
            $.ajax({
                url: '{{ route("timetable.generation.save-parameters") }}',
                method: 'POST',
                data: collectGaParams(),
                headers: {'X-CSRF-TOKEN': csrfToken},
                success: function (res) {
                    btn.removeClass('loading').prop('disabled', false);
                    Swal.fire({icon: 'success', title: 'Saved', text: res.message, timer: 1500, showConfirmButton: false});
                    $('#gaProfileSelect').val('custom');
                },
                error: function (xhr) {
                    btn.removeClass('loading').prop('disabled', false);
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to save parameters';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        $('#applyProfileBtn').on('click', function () {
            var profile = $('#gaProfileSelect').val();
            if (profile === 'custom') {
                Swal.fire('Info', 'Select a profile to apply, or edit the values manually.', 'info');
                return;
            }
            $.ajax({
                url: '{{ route("timetable.generation.apply-profile") }}',
                method: 'POST',
                data: {profile: profile},
                headers: {'X-CSRF-TOKEN': csrfToken},
                success: function (res) {
                    setGaInputs(res.parameters);
                    Swal.fire({icon: 'success', title: 'Applied', text: res.message, timer: 1500, showConfirmButton: false});
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to apply profile';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        $('#resetParamsBtn').on('click', function () {
            setGaInputs(defaultParameters);
            $('#gaProfileSelect').val('custom');
        });

        // Switch profile select to "custom" when user manually edits a parameter
        $('.ga-param').on('change input', function () {
            $('#gaProfileSelect').val('custom');
        });
    </script>
@endsection
