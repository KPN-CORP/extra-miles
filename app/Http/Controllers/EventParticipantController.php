<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventParticipant;
use App\Models\Event;
use Illuminate\Support\Facades\Crypt;
use App\Exports\ParticipantsExport;
use Maatwebsite\Excel\Facades\Excel;

class EventParticipantController extends Controller
{
    public function listParticipants($encryptedId)
    {
        $parentLink = 'Event Management';
        $link = 'List Participant';
        $back = 'admin.events.index';

        $id = Crypt::decryptString($encryptedId);

        $event = Event::findOrFail($id);

        $participants = EventParticipant::where('event_id', $id)->orderBy('status', 'desc')->get();
        
        $waitinglists = EventParticipant::where('event_id', $id)
                        ->where('status', 'Waiting List')->get();
        
        $approveparticipants = EventParticipant::with('employee')
                        ->where('event_id', $id)
                        ->whereIn('status', ['Registered', 'Confirmation'])
                        ->get();
        
        $attending = EventParticipant::where('event_id', $id)
                        ->whereIn('status', ['Registered', 'Confirmation'])
                        ->where('attending_status', '!=','')
                        ->whereIn('attending_status', ['Attending','Not Attending'])->get();
        
        $notattending = EventParticipant::where('event_id', $id)
                        ->whereIn('status', ['Registered', 'Confirmation'])
                        ->where('attending_status', 'Not Attending')->get();

        // Hitung status
        $countRequest = EventParticipant::where('event_id', $id)->count();
        $countWaitingList = EventParticipant::where('event_id', $id)->where('status', 'Waiting List')->count();
        $countApproved = EventParticipant::where('event_id', $id)->whereIn('status', ['Registered', 'Confirmation'])->count();
        $countConfirmation = EventParticipant::where('event_id', $id)
            ->whereIn('status', ['Confirmation'])
            ->count();
        $countConfirmed = EventParticipant::where('event_id', $id)
            ->whereIn('status', ['Registered', 'Confirmation'])
            ->where('attending_status', '!=','')
            ->count();
        $countAttending = EventParticipant::where('event_id', $id)
            ->whereIn('status', ['Registered', 'Confirmation'])
            ->where('attending_status', 'Attending')
            ->count();
        $countNotAttending = EventParticipant::where('event_id', $id)
            ->whereIn('status', ['Registered', 'Confirmation'])
            ->where('attending_status', 'Not Attending')
            ->count();
        $countCanceled = EventParticipant::where('event_id', $id)->where('status', 'Canceled')->count();

        return view('pages.admin.events.list-participants', compact(
            'event', 'participants', 'back',
            'countRequest', 'countWaitingList', 'countApproved',
            'countConfirmation', 'countConfirmed', 'countAttending', 'countNotAttending', 'parentLink', 'link', 'waitinglists', 'approveparticipants', 'attending', 'notattending', 'countCanceled'
        ));
    }
    
    public function approve($id)
    {
        
        $participant = EventParticipant::findOrFail($id);

        $event = Event::findOrFail($participant->event_id);
        $approveparticipants = EventParticipant::where('event_id', $participant->event_id)
                        ->whereIn('status', ['Registered', 'Confirmation'])->count();

        if($approveparticipants >= $event->quota){
            $encryptedId = Crypt::encryptString($participant->event_id);
            return redirect()->route('events.participants', $encryptedId)->with('error', 'Full Quota.');
        }else{
            $participant->status = 'Confirmation';
            $participant->save();

            $encryptedId = Crypt::encryptString($participant->event_id);
            return redirect()->route('events.participants', $encryptedId)->with('success', 'Participant Registered.');
        }
    }

    public function reject(Request $request, $id)
    {
        $participant = EventParticipant::findOrFail($id);
        $participant->status = 'Canceled';

        if ($request->has('messages')) {
            $participant->messages = $request->input('messages');
        }

        $participant->save();

        $encryptedId = Crypt::encryptString($participant->event_id);
        return redirect()->route('events.participants', $encryptedId)->with('success', 'Participant Canceled.');
    }

    public function export($event_id)
    {
        return Excel::download(new ParticipantsExport($event_id), 'participants_event_'.$event_id.'.xlsx');
    }
}
