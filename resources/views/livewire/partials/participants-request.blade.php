<div class="d-flex justify-content-between align-items-center">
    <div></div>
    <div class="input-group" style="width: 30%;">
        <div class="input-group-prepend">
            <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
        </div>
        <input type="text" wire:model.debounce.300ms="searchRequest" class="form-control border-dark-subtle border-left-0" placeholder="{{ __('Search..') }}">
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover table-sm dt-responsive nowrap mt-2" width="100%">
        <thead class="table-light">
            <tr>
                <th>{{ __('No') }}</th>
                <th>{{ __('Participant') }}</th>
                <th>{{ __('Business Unit') }}</th>
                <th>{{ __('Job Level') }}</th>
                <th>{{ __('Location') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participants as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $p->fullname }}</td>
                <td>{{ $p->business_unit }}</td>
                <td>{{ $p->job_level }}</td>
                <td>{{ $p->location }}</td>
                <td>{{ __($p->status) }}</td>
                <td>
                    <button wire:click="approve({{ $p->id }})" class="btn btn-outline-success btn-sm">{{ __('Approve') }}</button>
                    <button wire:click="reject({{ $p->id }})" class="btn btn-outline-danger btn-sm">{{ __('Reject') }}</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">{{ __('No data found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">
        {{ $participants->links() }}
    </div>
</div>