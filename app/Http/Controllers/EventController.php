<?php

namespace App\Http\Controllers;

use App\Exports\EvoParticipantsExport;
use Illuminate\Http\Request;
use App\Models\MasterBisnisunit;
use App\Models\Location;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Event;
use App\Models\FormTemplate;
use App\Models\EventParticipant;
use App\Models\ModelHasRole;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $parentLink = 'Event Management';
        $link = 'Events';
        
        $user = Auth::user();
        $userRoleIds = $user->roles->pluck('id');

        $modelIds = ModelHasRole::whereIn('role_id', $userRoleIds)
        ->pluck('model_id');

        $eventsToUpdate = Event::whereIn('status', ['Open Registration', 'Full Booked'])->whereNull('deleted_at')->get();
        $now = Carbon::now();

        foreach ($eventsToUpdate as $event) {
            $start = Carbon::parse($event->start_date . ' ' . $event->time_start);
            $end = Carbon::parse($event->end_date . ' ' . $event->time_end);

            if ($now->greaterThanOrEqualTo($start) && $now->lessThan($end)) {
                $event->status = 'Ongoing';
                $event->save();
            } elseif ($now->greaterThan($end)) {
                $event->status = 'Closed';
                $event->save();
            }
        }

        $events = Event::withCount('participants')
        ->whereIn('status', ['Open Registration', 'Full Booked', 'Draft', 'Ongoing'])
        ->whereIn('created_by', $modelIds)
        ->orderBy('created_at', 'desc')
        ->where('category', '!=', 'EVO')
        ->get();

        $eventClosed = Event::withCount('participants')
        ->whereIn('status', ['Closed'])
        ->whereIn('created_by', $modelIds)
        ->orderBy('created_at', 'desc')
        ->where('category', '!=', 'EVO')
        ->get();

        $eventArchive = Event::onlyTrashed()
        ->withCount('participants')
        ->whereIn('created_by', $modelIds)
        ->orderBy('created_at', 'desc')
        ->where('category', '!=', 'EVO')
        ->get();

        return view('pages.admin.events.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'events' => $events,
            'eventClosed' => $eventClosed,
            'eventArchive' => $eventArchive,
        ]);
    }

    public function showQRPNG($encryptedId)
    {
        try {
            $eventId = Crypt::decryptString($encryptedId);
            $event = Event::findOrFail($eventId);
            // $url = url($encryptedId);
            $url = $encryptedId;

            return view('pages.admin.events.qr_png', compact('url','event'));
        } catch (\Exception $e) {
            abort(403, 'Invalid QR request');
        }
    }

    public function create()
    {
        $parentLink = 'Event Management';
        $link = 'Create Event';
        $back = 'admin.events.index';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');
        
        $locations = Employee::select('group_company', 'office_area')
            ->whereNull('deleted_at')
            ->groupBy('group_company', 'office_area')
            ->orderBy('office_area')
            ->get();

        $departments = Employee::select('group_company', 'unit', 'office_area')
            ->groupBy('group_company', 'unit', 'office_area')
            ->orderBy('group_company')
            ->orderBy('unit')
            ->get();
        
        $grades = Grade::select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->get();

        $formTemplates = FormTemplate::select('id','title','form_schema','created_at')
            ->where('category','event')
            ->orderBy('title')
            ->get();

        return view('pages.admin.events.create', [
            'back' => $back,
            'link' => $link,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'locations' => $locations,
            'departments' => $departments,
            'grades' => $grades,
            'formTemplates' => $formTemplates,
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
            $imagePath = $request->file('banner')->store('assets/images/events', 'public');
        }

        $formSchema = null;

        if ($request->form_id) {
            $formTemplate = FormTemplate::find($request->form_id);
            if ($formTemplate) {
                $formSchema = $formTemplate->form_schema;
            }
        }

        Event::create([
            'category'         => $request->category,
            'start_date'       => $startDate,
            'time_start'       => $timeStart,
            'end_date'         => $endDate,
            'time_end'         => $timeEnd,
            'title'            => $request->event_name, 
            'event_location'   => $request->event_location,
            'description'      => $request->description,
            'image'            => $imagePath,
            'status'           => $request->action === 'draft' ? 'Draft' : 'Open Registration',
            'status_survey'    => $request->has('need_survey') ? 'T' : 'F',
            'status_voting'    => $request->has('need_voting') ? 'T' : 'F',
            'quota'            => $request->participants,
            'regist_deadline'  => $request->registration_deadline,
            'businessUnit'     => $request->business_unit ? json_encode($request->business_unit) : null,
            'unit'             => $request->unit ? json_encode($request->unit) : null,
            'jobLevel'         => $request->job_level ? json_encode($request->job_level) : null,
            'location'         => $request->location ? json_encode($request->location) : null,
            'form_id'          => $request->form_id,
            'form_schema'      => $formSchema,
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
        $back = 'admin.events.index';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');
        
        $locations = Employee::select('group_company', 'office_area')
            ->whereNull('deleted_at')
            ->groupBy('group_company', 'office_area')
            ->orderBy('office_area')
            ->get();

        $departments = Employee::select('group_company', 'unit', 'office_area')
            ->groupBy('group_company', 'unit', 'office_area')
            ->orderBy('group_company')
            ->orderBy('unit')
            ->get();
        
        $grades = Grade::select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->get();
        
        $formTemplates = FormTemplate::select('id','title','form_schema','created_at')
            ->where('category','event')
            ->orderBy('title')
            ->get();

        return view('pages.admin.events.edit', compact('back','link', 'parentLink', 'event', 'bisnisunits', 'departments', 'grades', 'locations', 'formTemplates'));
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

        $formSchema = null;

        if ($request->form_id) {
            $formTemplate = FormTemplate::find($request->form_id);
            if ($formTemplate) {
                $formSchema = $formTemplate->form_schema;
            }
        }

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
        $event->form_id          = $request->form_id;
        $event->form_schema      = $formSchema;
        // Upload banner jika ada
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('assets/images/events', 'public');
            $event->image = $path;
        }

        // Simpan status draft jika ada
        if ($request->action == 'draft') {
            $event->status = 'Draft';
        }else if($request->action == 'update'){
            $event->status = 'Open Registration';
        }

        $event->save();

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
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

    public function evoIndex()
    {
        $parentLink = 'Event Management';
        $link = 'EVO';     
        $username = Auth::user()->name;

        $data = Event::withCount('participants')
            ->with('participants', 'formTemplates')
            ->where('category', 'EVO')
            ->orderBy('created_at', 'desc')
            ->first();

        // Kalau belum ada event EVO
        if (!$data) {
            return view('pages.admin.events.evoindex', [
                'link'        => $link,
                'parentLink'  => $parentLink,
                'data'        => null,
                'options'     => [],
                'programs'    => [],
                'username'    => $username,
            ]);
        }

        // TAB: tetap pakai schema (question_1.options)
        $schema = $data->form_schema ? json_decode($data->form_schema, true) : null;
        $options = collect($schema['fields'] ?? [])
            ->where('name', 'question_1')
            ->flatMap(fn ($f) => $f['options'] ?? [])
            ->unique()
            ->values()
            ->toArray();

        // REPORT: ambil semua nilai question_1 DISTINCT dari transaksi
        $programs = EventParticipant::where('event_id', $data->id)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.question_1')) AS program")
            ->whereNotNull('form_data')
            ->distinct()
            ->pluck('program')
            ->filter()      // buang null / kosong
            ->values()
            ->toArray();

        return view('pages.admin.events.evoindex', [
            'link'        => $link,
            'parentLink'  => $parentLink,
            'data'        => $data,
            'options'     => $options,   // untuk TAB
            'programs'    => $programs,  // untuk filter di modal export
            'username'    => $username,
        ]);
    }

    public function evoManage($id)
    {
        $event = Event::findOrFail($id);
        $event->businessUnit = json_decode($event->businessUnit, true);
        $event->unit = json_decode($event->unit, true);
        $event->jobLevel = json_decode($event->jobLevel, true);
        $event->location = json_decode($event->location, true);
        
        $parentLink = 'EVO';
        $link = 'Manage Event';
        $back = 'admin.evo.index';

        $bisnisunits = MasterBisnisunit::whereNotIn('nama_bisnis', ['KPN Plantations', 'Others', 'Katingan'])
            ->orderBy('nama_bisnis')
            ->pluck('nama_bisnis');
        
        $locations = Employee::select('group_company', 'office_area')
            ->whereNull('deleted_at')
            ->groupBy('group_company', 'office_area')
            ->orderBy('office_area')
            ->get();

        $departments = Employee::select('group_company', 'unit', 'office_area')
            ->groupBy('group_company', 'unit', 'office_area')
            ->orderBy('group_company')
            ->orderBy('unit')
            ->get();
        
        $grades = Grade::select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->get();
        
        $formTemplates = FormTemplate::select('id','title','form_schema','created_at')
            ->where('category','event')
            ->orderBy('title')
            ->get();

        return view('pages.admin.events.evomanage', compact('back','link', 'parentLink', 'event', 'bisnisunits', 'departments', 'grades', 'locations', 'formTemplates'));
    }

    public function evoUpdate(Request $request, $id)
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

        $formSchema = null;

        if ($request->form_id) {
            $formTemplate = FormTemplate::find($request->form_id);
            if ($formTemplate) {
                $formSchema = $formTemplate->form_schema;
            }
        }

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
        $event->form_id          = $request->form_id;
        $event->form_schema      = $formSchema;
        // Upload banner jika ada
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('assets/images/events', 'public');
            $event->image = $path;
        }

        // Simpan status draft jika ada
        if ($request->action == 'draft') {
            $event->status = 'Draft';
        }

        $event->save();

        return redirect()->route('admin.evo.index')->with('success', 'EVO updated successfully.');
    }

    public function exportEvoParticipants(Request $request)
    {
        $username = Auth::user()->name;

        // Ambil value option dari request
        $option = $request->get('option', 'all'); 
        $option = urldecode($option);

        $event = Event::with('participants')
            ->where('category', 'EVO')
            ->orderBy('created_at', 'desc')
            ->first();

        $fileName = 'participants_' . Str::slug($option) . '.xlsx';

        return Excel::download(new EvoParticipantsExport($event, $option, $username), $fileName);
    }

}
