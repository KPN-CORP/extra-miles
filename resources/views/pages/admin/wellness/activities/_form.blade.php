@php
    $activity = $activity ?? null;
    $currentStatus = old('status', $activity?->status?->value ?? 'draft');
    $currentMethod = old('registration_method', $activity?->registration_method?->value ?? 'fifo');

    // Element ids are prefixed `wa_` on purpose. The admin theme ships a bare
    // `#status { position:absolute; ... }` rule for the preloader spinner
    // (scss/custom/components/_preloader.scss), and script.blade.php globally
    // runs ClassicEditor.create('#description'). A plain id="status" or
    // id="description" here silently inherits both.
    $statusHelp = [
        'draft' => 'Only visible here. Use while you are still setting up schedules.',
        'active' => 'Listed in the employee app, so people can register.',
        'inactive' => 'Hidden from the employee app. Existing registrations are kept.',
    ];
    $statusIcon = [
        'draft' => 'ri-draft-line',
        'active' => 'ri-checkbox-circle-line',
        'inactive' => 'ri-eye-off-line',
    ];
@endphp

<div class="row g-3">
    {{-- ------------------------------------------------ main details --}}
    <div class="col-lg-8">
        <div class="card h-100 mb-0">
            <div class="card-header bg-transparent py-2">
                <h5 class="card-title mb-0 fs-6">Activity Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="wa_name" class="form-label">
                        Activity Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        id="wa_name" name="name" maxlength="150" required
                        placeholder="e.g. Morning Yoga"
                        value="{{ old('name', $activity?->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="wa_type" class="form-label">
                        Activity Type <span class="text-danger">*</span>
                    </label>

                    @if ($types->isEmpty())
                        <div class="alert alert-warning d-flex align-items-start gap-2 mb-0" role="alert">
                            <i class="ri-error-warning-line mt-1"></i>
                            <div>
                                <strong>No active activity types yet.</strong>
                                <div class="small">
                                    Every activity belongs to a type, so create one first.
                                    @can('viewmenuwellnesstype')
                                        <a href="{{ route('admin.wellness.types.index') }}" class="alert-link">
                                            Go to Activity Types
                                        </a>
                                    @else
                                        Ask an administrator to add one.
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @else
                        <select class="form-select @error('wellness_activity_type_id') is-invalid @enderror"
                            id="wa_type" name="wellness_activity_type_id" required>
                            <option value="">Select a type...</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    @selected(old('wellness_activity_type_id', $activity?->wellness_activity_type_id) == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('wellness_activity_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">
                        Registration Method <span class="text-danger">*</span>
                    </label>

                    @foreach ($methods as $value => $label)
                        @php $case = \App\Enums\WellnessRegistrationMethod::from($value); @endphp
                        <div class="form-check border rounded p-2 ps-4 mb-2 {{ $currentMethod === $value ? 'border-primary bg-primary-subtle' : '' }}">
                            <input class="form-check-input js-method" type="radio" name="registration_method"
                                id="wa_method_{{ $value }}" value="{{ $value }}"
                                @checked($currentMethod === $value) required>
                            <label class="form-check-label d-block" for="wa_method_{{ $value }}">
                                <span class="fw-semibold">
                                    <i class="{{ $case->icon() }} me-1"></i>{{ $label }}
                                </span>
                                <span class="d-block text-muted small lh-sm">{{ $case->description() }}</span>
                                <!-- <span class="d-block small mt-1">
                                    @foreach ($case->statuses() as $status)
                                        <span class="badge {{ $status->badgeClass() }} me-1">{{ $status->label() }}</span>
                                    @endforeach
                                </span> -->
                            </label>
                        </div>
                    @endforeach

                    @error('registration_method')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    @if ($activity && $activity->registrations()->exists())
                        <div class="form-text text-warning">
                            <i class="ri-alert-line me-1"></i>This activity already has registrations.
                            Changing the method does not restatus them.
                        </div>
                    @endif
                </div>

                <div class="mb-0">
                    <label for="wa_description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                        id="wa_description" name="description" rows="8"
                        placeholder="What is this activity, who is it for, and what should people bring?">{{ old('description', $activity?->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Shown to employees on the activity page in the app.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------ sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-transparent py-2">
                <h5 class="card-title mb-0 fs-6">Visibility</h5>
            </div>
            <div class="card-body">
                @foreach ($statuses as $value => $label)
                    <div class="form-check border rounded p-2 ps-4 mb-2 {{ $currentStatus === $value ? 'border-primary bg-primary-subtle' : '' }}">
                        <input class="form-check-input" type="radio" name="status"
                            id="wa_status_{{ $value }}" value="{{ $value }}"
                            @checked($currentStatus === $value) required>
                        <label class="form-check-label d-block" for="wa_status_{{ $value }}">
                            <span class="fw-semibold">
                                <i class="{{ $statusIcon[$value] ?? 'ri-circle-line' }} me-1"></i>{{ $label }}
                            </span>
                            <span class="d-block text-muted small lh-sm">{{ $statusHelp[$value] ?? '' }}</span>
                        </label>
                    </div>
                @endforeach

                @error('status')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card mb-0">
            <div class="card-header bg-transparent py-2">
                <h5 class="card-title mb-0 fs-6">Banner Image</h5>
            </div>
            <div class="card-body">
                {{-- Preview: the saved banner on edit, swapped live when a new file is picked. --}}
                <img id="wa_image_preview"
                    src="{{ $activity?->image ? url('/images/'.$activity->image) : '' }}"
                    alt="Banner preview"
                    class="img-fluid rounded border mb-2 {{ $activity?->image ? '' : 'd-none' }}"
                    style="max-height: 150px;">

                <div id="wa_image_empty" class="text-center text-muted border rounded py-3 mb-2 {{ $activity?->image ? 'd-none' : '' }}">
                    <i class="ri-image-add-line fs-3 d-block"></i>
                    <span class="small">No banner selected</span>
                </div>

                <input type="file"
                    class="form-control @error('image') is-invalid @enderror"
                    id="wa_image" name="image" accept="image/png, image/jpeg">
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="form-text">
                    JPG or PNG, max 2&nbsp;MB.
                    @if ($activity?->image)
                        Uploading a new file replaces the current one.
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    {{-- CKEditor is loaded globally by script.blade.php, which also auto-binds
         to #description. This form deliberately uses its own id and initialises
         the editor here, so the field does not depend on that global hook. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textarea = document.getElementById('wa_description');

            if (textarea && typeof ClassicEditor !== 'undefined') {
                ClassicEditor
                    .create(textarea, {
                        toolbar: ['heading', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'link', '|', 'undo', 'redo'],
                        removePlugins: ['Image', 'ImageToolbar', 'EasyImage', 'ImageUpload', 'MediaEmbed', 'CKFinder'],
                    })
                    .catch(function (error) {
                        // Leaving the plain textarea in place is a fine fallback.
                        console.error('Wellness description editor failed to load:', error);
                    });
            }

            // Highlight the selected card in each radio group (visibility, method).
            ['status', 'registration_method'].forEach(function (group) {
                var inputs = document.querySelectorAll('input[name="' + group + '"]');
                inputs.forEach(function (input) {
                    input.addEventListener('change', function () {
                        inputs.forEach(function (other) {
                            var card = other.closest('.form-check');
                            card.classList.toggle('border-primary', other.checked);
                            card.classList.toggle('bg-primary-subtle', other.checked);
                        });
                    });
                });
            });

            // Live banner preview.
            var file = document.getElementById('wa_image');
            var preview = document.getElementById('wa_image_preview');
            var empty = document.getElementById('wa_image_empty');

            if (file && preview && empty) {
                file.addEventListener('change', function () {
                    var chosen = this.files && this.files[0];

                    if (!chosen) {
                        return;
                    }

                    preview.src = URL.createObjectURL(chosen);
                    preview.classList.remove('d-none');
                    empty.classList.add('d-none');
                });
            }
        });
    </script>
@endpush
