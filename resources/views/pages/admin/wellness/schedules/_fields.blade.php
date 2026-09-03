{{-- Shared schedule fields. $prefix keeps the element ids unique between the
     create and edit modals on the same page. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label for="{{ $prefix }}-start_at" class="form-label">Starts At <span class="text-danger">*</span></label>
        <input type="datetime-local" class="form-control" id="{{ $prefix }}-start_at" name="start_at" required>
    </div>
    <div class="col-md-6">
        <label for="{{ $prefix }}-end_at" class="form-label">Ends At <span class="text-danger">*</span></label>
        <input type="datetime-local" class="form-control" id="{{ $prefix }}-end_at" name="end_at" required>
    </div>

    <div class="col-md-8">
        <label for="{{ $prefix }}-location" class="form-label">Location</label>
        <input type="text" class="form-control" id="{{ $prefix }}-location" name="location" maxlength="150">
    </div>
    <div class="col-md-4">
        <label for="{{ $prefix }}-quota" class="form-label">Quota</label>
        <input type="number" class="form-control" id="{{ $prefix }}-quota" name="quota" min="1" placeholder="Unlimited">
        <div class="form-text">Leave empty for unlimited seats.</div>
    </div>

    <div class="col-md-6">
        <label for="{{ $prefix }}-registration_start_at" class="form-label">Registration Opens</label>
        <input type="datetime-local" class="form-control" id="{{ $prefix }}-registration_start_at" name="registration_start_at">
    </div>
    <div class="col-md-6">
        <label for="{{ $prefix }}-registration_end_at" class="form-label">Registration Closes</label>
        <input type="datetime-local" class="form-control" id="{{ $prefix }}-registration_end_at" name="registration_end_at">
    </div>

    <div class="col-md-6">
        <label for="{{ $prefix }}-status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="{{ $prefix }}-status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($value === 'open')>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Only <strong>Open</strong> schedules accept new registrations.</div>
    </div>
</div>
