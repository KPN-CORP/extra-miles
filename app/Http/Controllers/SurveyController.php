<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBisnisunit;
use App\Models\Location;
use App\Models\Department;
use App\Models\Grade;

class SurveyController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Survey/Voting';

        return view('pages.admin.survey.index', [
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }

    public function create()
    {
        $parentLink = 'Survey Management';
        $link = 'Create Survey';

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

        return view('pages.admin.survey.create', [
            'link' => $link,
            'parentLink' => $parentLink,
            'bisnisunits' => $bisnisunits,
            'locations' => $locations,
            'departments' => $departments,
            'grades' => $grades,
        ]);
    }
}
