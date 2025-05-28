<div class="container-fluid">
    <style>
        .nav-tabs .nav-link.active {
            background-color: #ab2f2b !important;
            color: white !important;
            font-weight: bold;
            border-radius: 0.375rem;
        }

        .nav-tabs .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-warning text-dark"><h5>REQUEST : {{ $countRequest }}</h5></span>
            <span class="badge bg-danger"><h5>WAITING LIST : {{ $countWaitingList }}</h5></span>
            <span class="badge bg-success"><h5>APPROVED : {{ $countApproved }}</h5></span>
            <span class="badge bg-warning text-dark"><h5>CONFIRMATION NEEDED : {{ $countConfirmation }}</h5></span>
            <span class="badge bg-primary"><h5>CONFIRMED : {{ $countConfirmed }}</h5></span>
            <span class="badge bg-info"><h5>ATTENDING : {{ $countAttending }}</h5></span>
            <span class="badge bg-dark"><h5>NOT ATTENDING : {{ $countNotAttending }}</h5></span>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                @foreach(['Request', 'Waiting List', 'Approved', 'Attending', 'Not Attending'] as $tab)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->first) active @endif"
                                wire:click="$set('activeTab', '{{ strtolower(str_replace(' ', '_', $tab)) }}')"
                                type="button" role="tab">
                            {{ $tab }}
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- =============================== Detail tabel request =============================== --}}
            @if($activeTab === 'request')
                @include('livewire.partials.participants-request')
            @elseif($activeTab === 'waiting_list')
                @include('livewire.partials.participants-waitinglist')
            @elseif($activeTab === 'approved')
                @include('livewire.partials.participants-approved')
            @elseif($activeTab === 'attending')
                {{-- Buat jika perlu --}}
            @elseif($activeTab === 'not_attending')
                {{-- Buat jika perlu --}}
            @endif
        </div>
    </div>
</div>