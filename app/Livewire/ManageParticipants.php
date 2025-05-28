<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Crypt;

class ManageParticipants extends Component
{
    use WithPagination;

    public $layout = 'layouts.app';

    public $event;
    public $encryptedId;
    public $parentLink = 'Event Management';
    public $link = 'List Participant';

    public string $activeTab = 'request';

    public string $searchRequest = '';
    public string $searchWaitingList = '';
    public string $searchApproved = '';
    public string $searchAttending = '';
    public string $searchNotAttending = '';

    public function mount($encryptedId)
    {
        $this->encryptedId = $encryptedId;
        $id = Crypt::decryptString($encryptedId);
        $this->event = Event::findOrFail($id);
    }

    public function updatingActiveTab()
    {
        $this->resetPage();
    }

    public function render()
    {
        $id = $this->event->id;

        $participants = collect();
        $waitinglists = collect();
        $approveparticipants = collect();
        $attending = collect();
        $notattending = collect();

        switch ($this->activeTab) {
            case 'request':
                $participants = EventParticipant::where('event_id', $id)
                    ->where('status', 'Request')
                    ->where('fullname', 'like', "%{$this->searchRequest}%")
                    ->paginate(10);
                break;

            case 'waiting_list':
                $waitinglists = EventParticipant::where('event_id', $id)
                    ->where('status', 'Waiting List')
                    ->where('fullname', 'like', "%{$this->searchWaitingList}%")
                    ->paginate(10);
                break;

            case 'approved':
                $approveparticipants = EventParticipant::where('event_id', $id)
                    ->where('status', 'Approved')
                    ->where('fullname', 'like', "%{$this->searchApproved}%")
                    ->paginate(10);
                break;

            case 'attending':
                $attending = EventParticipant::where('event_id', $id)
                    ->where('status', 'Approved')
                    ->where('attending_status', 'Attending')
                    ->where('fullname', 'like', "%{$this->searchAttending}%")
                    ->paginate(10);
                break;

            case 'not_attending':
                $notattending = EventParticipant::where('event_id', $id)
                    ->where('status', 'Approved')
                    ->where('attending_status', 'Not Attending')
                    ->where('fullname', 'like', "%{$this->searchNotAttending}%")
                    ->paginate(10);
                break;
        }

        $countRequest = EventParticipant::where('event_id', $id)->where('status', 'Request')->count();
        $countWaitingList = EventParticipant::where('event_id', $id)->where('status', 'Waiting List')->count();
        $countApproved = EventParticipant::where('event_id', $id)->where('status', 'Approved')->count();
        $countConfirmation = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->whereNull('attending_status')
            ->count();
        $countConfirmed = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->where('attending_status', '!=', '')
            ->count();
        $countAttending = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->where('attending_status', 'Attending')
            ->count();
        $countNotAttending = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->where('attending_status', 'Not Attending')
            ->count();

        return view('livewire.manage-participants', [
            'participants' => $participants,
            'waitinglists' => $waitinglists,
            'approveparticipants' => $approveparticipants,
            'attending' => $attending,
            'notattending' => $notattending,
            'countRequest' => $countRequest,
            'countWaitingList' => $countWaitingList,
            'countApproved' => $countApproved,
            'countConfirmation' => $countConfirmation,
            'countConfirmed' => $countConfirmed,
            'countAttending' => $countAttending,
            'countNotAttending' => $countNotAttending,
            'parentLink' => $this->parentLink,
            'link' => $this->link,
            'event' => $this->event,
            'activeTab' => $this->activeTab,
        ]);
    }
}