<div class="d-flex justify-content-between align-items-center">
    <div></div>
    <div class="input-group" style="width: 30%;">
        <div class="input-group-prepend">
            <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
        </div>
        <input type="text" wire:model.debounce.300ms="searchRequest" class="form-control border-dark-subtle border-left-0" placeholder="Search..">
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover table-sm dt-responsive nowrap mt-2" width="100%">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Participant</th>
                <th>Business Unit</th>
                <th>Job Level</th>
                <th>Location</th>
                <th>Status</th>
                <th>Action</th>
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
                <td>{{ $p->status }}</td>
                <td>
                    <button wire:click="approve({{ $p->id }})" class="btn btn-outline-success btn-sm">Approve</button>
                    <button wire:click="reject({{ $p->id }})" class="btn btn-outline-danger btn-sm">Reject</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">
        {{ $participants->links() }}
    </div>
</div>