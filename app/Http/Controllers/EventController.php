<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBisnisunit;
use App\Models\Location;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Event Management';

        $events = Event::orderBy('created_at', 'desc')->get();

        return view('pages.admin.events.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'events' => $events,
        ]);
    }

    public function create()
    {
        $parentLink = 'Event Management';
        $link = 'Create Event';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');
        
        $locations = Location::select('company_name', 'area', 'work_area')
            ->orderBy('area')
            ->get();

        $departments = Department::select('parent_company_id', 'department_name', 'department_code')
            ->where('status', 'Active')
            ->orderBy('parent_company_id')
            ->orderBy('department_name')
            ->get();
        
        $grades = Grade::select('group_name')
            ->distinct()
            ->orderBy('id')
            ->get();

        return view('pages.admin.events.create', [
            'link' => $link,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'locations' => $locations,
            'departments' => $departments,
            'grades' => $grades,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'banner' => 'nullable|image|max:2048',
            'participants' => 'nullable|integer',
            'registration_deadline' => 'nullable|date',
        ]);

        $startDate = date('Y-m-d', strtotime($request->start_date));
        $timeStart = date('H:i:s', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $timeEnd = date('H:i:s', strtotime($request->end_date));

        $imagePath = null;
        if ($request->hasFile('banner')) {
            $imagePath = $request->file('banner')->store('event_banners', 'public');
        }

        Event::create([
            'category'         => $request->category,
            'start_date'       => $startDate,
            'time_start'       => $timeStart,
            'end_date'         => $endDate,
            'time_end'         => $timeEnd,
            'title'            => $request->event_name,
            'description'      => $request->description,
            'image'            => $imagePath,
            'status'           => $request->action === 'draft' ? 'Draft' : 'Ongoing',
            'status_survey'    => $request->has('need_survey') ? 'T' : 'F',
            'status_voting'    => $request->has('need_voting') ? 'T' : 'F',
            'quota'            => $request->participants,
            'regist_deadline'  => $request->registration_deadline,
            'businessUnit'     => $request->business_unit ? json_encode($request->business_unit) : null,
            'unit'             => $request->unit ? json_encode($request->unit) : null,
            'jobLevel'         => $request->job_level ? json_encode($request->job_level) : null,
            'location'         => $request->location ? json_encode($request->location) : null,
            'barcode_token'    => Str::uuid(),
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event has been created successfully.');
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $event->businessUnit = json_decode($event->businessUnit, true);
        $event->unit = json_decode($event->unit, true);
        $event->jobLevel = json_decode($event->jobLevel, true);
        $event->location = json_decode($event->location, true);
        
        $parentLink = 'Event Management';
        $link = 'Edit Event';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');
        
        $locations = Location::select('company_name', 'area', 'work_area')
            ->orderBy('area')
            ->get();

        $departments = Department::select('parent_company_id', 'department_name', 'department_code')
            ->where('status', 'Active')
            ->orderBy('parent_company_id')
            ->orderBy('department_name')
            ->get();
        
        $grades = Grade::select('group_name')
            ->distinct()
            ->orderBy('id')
            ->get();

        return view('pages.admin.events.edit', compact('link', 'parentLink', 'event', 'bisnisunits', 'departments', 'grades', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'event_name' => 'required|string',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|max:2048',
            'participants' => 'nullable|integer',
            'registration_deadline' => 'nullable|date',
        ]);

        // Format tanggal dan jam
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $timeStart = date('H:i:s', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $timeEnd = date('H:i:s', strtotime($request->end_date));

        // Update data
        $event->category         = $request->category;
        $event->start_date       = $startDate;
        $event->time_start       = $timeStart;
        $event->end_date         = $endDate;
        $event->time_end         = $timeEnd;
        $event->title            = $request->event_name;
        $event->description      = $request->description;
        $event->status_survey    = $request->has('need_survey') ? 'T' : 'F';
        $event->status_voting    = $request->has('need_voting') ? 'T' : 'F';
        $event->quota            = $request->participants;
        $event->regist_deadline  = $request->registration_deadline;

        // JSON encode untuk multiple select fields
        $event->businessUnit     = $request->business_unit ? json_encode($request->business_unit) : null;
        $event->unit             = $request->unit ? json_encode($request->unit) : null;
        $event->jobLevel         = $request->job_level ? json_encode($request->job_level) : null;
        $event->location         = $request->location ? json_encode($request->location) : null;

        // Upload banner jika ada
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('event_banners', 'public');
            $event->image = $path;
        }

        // Simpan status draft jika ada
        if ($request->action == 'draft') {
            $event->status = 'Draft';
        }

        $event->save();

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function listParticipants($encryptedId)
    {
        $parentLink = 'Event Management';
        $link = 'List Participant';

        $id = Crypt::decryptString($encryptedId);

        $event = Event::findOrFail($id);

        $participants = EventParticipant::where('event_id', $id)
                        ->where('status', 'Request')
                        ->paginate(10);
        
        $waitinglists = EventParticipant::where('event_id', $id)
                        ->where('status', 'Waiting List')
                        ->paginate(10);
        
        $approveparticipants = EventParticipant::where('event_id', $id)
                        ->where('status', 'Approved')
                        ->paginate(10);

        // Hitung status
        $countRequest = EventParticipant::where('event_id', $id)->where('status', 'Request')->count();
        $countWaitingList = EventParticipant::where('event_id', $id)->where('status', 'Waiting List')->count();
        $countApproved = EventParticipant::where('event_id', $id)->where('status', 'Approved')->count();
        $countConfirmation = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->whereNull('attending_status')
            ->count();
        $countConfirmed = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->where('attending_status', '!=','')
            ->count();
        $countAttending = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->where('attending_status', 'Attending')
            ->count();
        $countNotAttending = EventParticipant::where('event_id', $id)
            ->where('status', 'Approved')
            ->where('attending_status', 'Not Attending')
            ->count();

        return view('pages.admin.events.list-participants', compact(
            'event', 'participants',
            'countRequest', 'countWaitingList', 'countApproved',
            'countConfirmation', 'countConfirmed', 'countAttending', 'countNotAttending', 'parentLink', 'link', 'waitinglists', 'approveparticipants'
        ));
    }

    public function softDelete($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->back()->with('success', 'Event berhasil diarsipkan.');
    }

    public function closeRegistration($id)
    {
        $event = Event::findOrFail($id);
        $event->status = 'Full Booked';
        $event->save();

        return redirect()->back()->with('success', 'Event registration has been closed.');
    }

    public function toggleStatus($id)
    {
        $event = Event::findOrFail($id);

        if ($event->status === 'Full Booked') {
            $event->status = 'Open Registration';
        } else {
            $event->status = 'Full Booked';
        }

        $event->save();

        return redirect()->back()->with('success', 'Event status updated successfully.');
    }
}
