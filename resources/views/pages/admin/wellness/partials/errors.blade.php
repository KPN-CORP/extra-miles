{{-- Validation feedback for the wellness admin pages. Flash success/error
     messages are already turned into SweetAlerts by resources/views/script.blade.php. --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading mb-1"><i class="ri-error-warning-line me-1"></i> {{ __('Please fix the following') }}</h5>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
    </div>
@endif
