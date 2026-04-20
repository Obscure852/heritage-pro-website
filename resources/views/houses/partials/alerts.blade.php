@php
    $viewErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

@if (session('message'))
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline label-icon"></i><strong>{{ session('error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

@if ($viewErrors->any())
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline label-icon"></i>
                <div>
                    <div class="fw-semibold mb-1">Please fix the following issues:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($viewErrors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif
