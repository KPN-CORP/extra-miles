<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBisnisunit;
use App\Models\Location;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Event;
use App\Models\survey;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class SurveyController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Survey/Voting';

        $surveyToUpdate = survey::whereIn('status', ['Ongoing'])->whereNull('deleted_at')->get();
        $now = Carbon::now();

        foreach ($surveyToUpdate as $survey) {
            $start = Carbon::parse($survey->start_date . ' ' . $survey->time_start);
            $end = Carbon::parse($survey->end_date . ' ' . $survey->time_end);

            if ($now->greaterThan($end)) {
                $survey->status = 'Closed';
                $survey->save();
            }
        }

        $surveyList = survey::withCount('surveyParticipant')
        ->whereIn('status', ['Ongoing', 'Draft'])
        ->orderBy('created_at', 'desc')
        ->get();

        $surveyClosed = survey::withCount('surveyParticipant')
        ->whereIn('status', ['Closed'])
        ->orderBy('created_at', 'desc')
        ->get();

        $surveyArchive = survey::onlyTrashed()
        ->withCount('surveyParticipant')
        ->orderBy('created_at', 'desc')
        ->get();

        return view('pages.admin.survey.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'surveyList' => $surveyList,
            'surveyClosed' => $surveyClosed,
            'surveyArchive' => $surveyArchive,
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'survey');
        $parentLink = 'Survey Management';
        $link = $type === 'vote' ? 'Create Voting' : 'Create Survey';

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

        $events = Event::whereNotIn('status', ['Draft'])
            ->whereNull('deleted_at')
            ->get();

        return view('pages.admin.survey.create', [
            'link' => $link,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'locations' => $locations,
            'departments' => $departments,
            'grades' => $grades,
            'type' => $type,
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'end_date' => 'required|date',
            'banner' => 'nullable|image|max:2048',
            'participants' => 'nullable|integer',
        ]);

        $startDate = date('Y-m-d', strtotime($request->start_date));
        $timeStart = date('H:i:s', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $timeEnd = date('H:i:s', strtotime($request->end_date));

        $imagePath = null;
        $type = $request->input('type');

        if ($request->hasFile('banner')) {
            $folder = $request->survey_type === 'vote' ? 'assets/surveys/voting' : 'assets/surveys/survey';
            $imagePath = $request->file('banner')->store($folder, 'public');
        }

        survey::create([
            'category'         => $request->survey_type,
            'title'            => $request->form_name,
            'start_date'       => $startDate,
            'time_start'       => $timeStart,
            'end_date'         => $endDate,
            'time_end'         => $timeEnd,
            'event_id'         => $request->related,
            'description'      => $request->description,
            'banner'           => $imagePath,
            'icon'             => $request->survey_type === 'vote' ? 'vote-icon.png' : 'survey-icon.png',
            'status'           => $request->action === 'draft' ? 'Draft' : 'Ongoing',
            'quota'            => $request->participants,
            'businessUnit'     => $request->business_unit ? json_encode($request->business_unit) : null,
            'unit'             => $request->unit ? json_encode($request->unit) : null,
            'jobLevel'         => $request->job_level ? json_encode($request->job_level) : null,
            'location'         => $request->location ? json_encode($request->location) : null,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('admin.survey.index')->with('success', 'Survey has been created successfully.');
    }

    public function edit($id)
    {
        $survey = survey::findOrFail($id);
        $survey->businessUnit = json_decode($survey->businessUnit, true);
        $survey->unit = json_decode($survey->unit, true);
        $survey->jobLevel = json_decode($survey->jobLevel, true);
        $survey->location = json_decode($survey->location, true);

        $parentLink = 'Survey Management';
        $link = $survey->category === 'vote' ? 'Update Voting' : 'Update Survey';

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

        $events = Event::whereNotIn('status', ['Draft'])
            ->whereNull('deleted_at')
            ->get();

        return view('pages.admin.survey.edit', [
            'link' => $link,
            'survey' => $survey,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'locations' => $locations,
            'departments' => $departments,
            'grades' => $grades,
            'events' => $events,
        ]);
    }

    public function update(Request $request, $id)
    {
        $survey = survey::findOrFail($id);

        $request->validate([
            'end_date' => 'required|date',
            'banner' => 'nullable|image|max:2048',
            'participants' => 'nullable|integer',
        ]);

        $startDate = date('Y-m-d', strtotime($request->start_date));
        $timeStart = date('H:i:s', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $timeEnd = date('H:i:s', strtotime($request->end_date));

        $imagePath = null;
        $survey->start_date       = $startDate;
        $survey->time_start       = $timeStart;
        $survey->end_date         = $endDate;
        $survey->time_end         = $timeEnd;
        $survey->form_id          = $request->related;
        $survey->title            = $request->form_name;
        $survey->description      = $request->description;
        $survey->quota            = $request->participants;

        // JSON encode untuk multiple select fields
        $survey->businessUnit     = $request->business_unit ? json_encode($request->business_unit) : null;
        $survey->unit             = $request->unit ? json_encode($request->unit) : null;
        $survey->jobLevel         = $request->job_level ? json_encode($request->job_level) : null;
        $survey->location         = $request->location ? json_encode($request->location) : null;

        // Upload banner jika ada
        if ($request->hasFile('banner')) {
            $folder = $request->survey_type === 'vote' ? 'assets/surveys/voting' : 'assets/surveys/survey';
            $imagePath = $request->file('banner')->store($folder, 'public');
            $survey->banner = $imagePath;
        }
        
        // Simpan status draft jika ada
        if ($request->action == 'draft') {
            $survey->status = 'Draft';
        }else if ($request->action == 'update') {
            $survey->status = 'Ongoing';
        }

        $survey->save();

        return redirect()->route('admin.survey.index')->with('success', 'Survey updated successfully.');
    }

    public function archive($id)
    {
        $survey = Survey::findOrFail($id);
        $survey->delete(); // Soft delete (mengisi deleted_at)
        
        return redirect()->back()->with('success', 'Survey berhasil diarsipkan.');
    }

    public function listParticipants($encryptedId)
    {
        $id = Crypt::decryptString($encryptedId);
        $survey = survey::findOrFail($id);
        $survey->businessUnit = json_decode($survey->businessUnit, true);
        $survey->unit = json_decode($survey->unit, true);
        $survey->jobLevel = json_decode($survey->jobLevel, true);
        $survey->location = json_decode($survey->location, true);

        $parentLink = 'Survey Management';
        $link = 'Participant Survey';

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

        $events = Event::whereNotIn('status', ['Draft'])
            ->whereNull('deleted_at')
            ->get();

        return view('pages.admin.survey.surveyParticipants', [
            'link' => $link,
            'survey' => $survey,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'locations' => $locations,
            'departments' => $departments,
            'grades' => $grades,
            'events' => $events,
        ]);
    }
}
