<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormTemplate;

class FormTemplateController extends Controller
{
    public function index()
    {
        $parentLink = 'Dashboard';
        $link = 'Form Builder';

        $formTemplates = FormTemplate::latest()->get();

        $formTemplateArchive = FormTemplate::onlyTrashed()
        ->orderBy('created_at', 'desc')
        ->get();

        return view('pages.admin.form.index', [
            'link' => $link,
            'parentLink' => $parentLink,
            'formTemplates' => $formTemplates,
            'formTemplateArchive' => $formTemplateArchive,
        ]);
    }
    public function create()
    {
        $parentLink = 'Form Builder';
        $link = 'Create Form';
        $back = 'form.index';

        return view('pages.admin.form.create', [
            'back' => $back,
            'link' => $link,
            'parentLink' => $parentLink,
        ]);
    }
}
