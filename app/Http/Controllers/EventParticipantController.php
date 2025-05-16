<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Crypt;

class EventParticipantController extends Controller
{
    public function approve($id)
    {
        $participant = EventParticipant::findOrFail($id);
        $participant->status = 'Approved';
        $participant->save();

        $encryptedId = Crypt::encryptString($participant->event_id);
        return redirect()->route('events.participants', $encryptedId)->with('success', 'Participant approved successfully.');
    }

    public function reject($id)
    {
        $participant = EventParticipant::findOrFail($id);
        $participant->status = 'Waiting List';
        $participant->save();

        $encryptedId = Crypt::encryptString($participant->event_id);
        return redirect()->route('events.participants', $encryptedId)->with('success', 'Participant moved to waiting list.');
    }

    public function reinvite($id)
    {
        $participant = EventParticipant::findOrFail($id);
        $participant->status = 'Approved';
        $participant->save();

        $encryptedId = Crypt::encryptString($participant->event_id);
        return redirect()->route('events.participants', $encryptedId)->with('success', 'Participant approved successfully.');
    }
}
