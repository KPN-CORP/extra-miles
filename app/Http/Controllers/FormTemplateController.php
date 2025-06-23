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
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'title' => 'required|string',
            'type' => 'required|array',
            'label' => 'required|array',
        ]);
        // dd($request);
        $fields = [];
        foreach ($request->type as $index => $type) {
            $label = $request->label[$index] ?? '';
            $required = isset($request->required[$index]);
            $validation = $request->validation[$index] ?? '';

            if (in_array($type, ['checkbox', 'radio']) && !empty($request->options[$index])) {
                $options = array_map('trim', explode(',', $request->options[$index]));

                if ($type === 'radio' && isset($request->label_confirmation[$index])) {
                    $fields[] = [
                        'name' => 'confirmation_' . ($index + 1),
                        'type' => $type,
                        'label' => $label,
                        'options' => $options,
                        'required' => $required,
                        'validation' => $validation,
                    ];

                    $fields[] = [
                        'name' => 'confirmation_' . ($index + 1) . '_reason',
                        'type' => 'text',
                        'label' => $request->label_confirmation[$index],
                        'required' => false,
                        'validation' => '',
                    ];
                } else {
                    $fields[] = [
                        'name' => 'question_' . ($index + 1),
                        'type' => $type,
                        'label' => $label,
                        'options' => $options,
                        'required' => $required,
                        'validation' => $validation,
                    ];
                }
            } else {
                $fields[] = [
                    'name' => 'question_' . ($index + 1),
                    'type' => $type,
                    'label' => $label,
                    'required' => $required,
                    'validation' => $validation,
                ];
            }
        }

        $schema = [
            'title' => $request->title,
            'fields' => $fields,
        ];
        // dd($schema);

        FormTemplate::create([
            'category' => $request->category,
            'title' => $request->title,
            'form_schema' => json_encode($schema),
        ]);

        return redirect()->route('form.index')->with('success', 'Form berhasil disimpan!');
    }
    public function edit($id)
    {
        $form = FormTemplate::findOrFail($id);
        $formSchema = json_decode($form->form_schema, true);
        $parentLink = 'Form Builder';
        $link = 'Create Form';
        $back = 'form.index';

        return view('pages.admin.form.edit', compact('form', 'formSchema', 'parentLink', 'link', 'back'));
    }

    public function update(Request $request, $id)
    {
        $form = FormTemplate::findOrFail($id);

        $form->title = $request->input('title');
        $form->category = $request->input('category');
        
        // dd($request->all());
        $fields = [];
        foreach ($request->type as $index => $type) {
            $label = $request->edit_label[$index] ?? '';
            $required = isset($request->required[$index]);
            $validation = $request->validation[$index] ?? '';

            if (in_array($type, ['checkbox', 'radio']) && !empty($request->options[$index])) {
                $options = array_map('trim', explode(',', $request->options[$index]));

                if ($type === 'radio' && isset($request->label_confirmation[$index])) {
                    $fields[] = [
                        'name' => 'confirmation_' . ($index + 1),
                        'type' => $type,
                        'label' => $label,
                        'options' => $options,
                        'required' => $required,
                        'validation' => $validation,
                    ];

                    $fields[] = [
                        'name' => 'confirmation_' . ($index + 1) . '_reason',
                        'type' => 'text',
                        'label' => $request->label_confirmation[$index],
                        'required' => false,
                        'validation' => '',
                    ];
                } else {
                    $fields[] = [
                        'name' => 'question_' . ($index + 1),
                        'type' => $type,
                        'label' => $label,
                        'options' => $options,
                        'required' => $required,
                        'validation' => $validation,
                    ];
                }
            } else {
                $fields[] = [
                    'name' => 'question_' . ($index + 1),
                    'type' => $type,
                    'label' => $label,
                    'required' => $required,
                    'validation' => $validation,
                ];
            }
        }

        $form->form_schema = json_encode([
            'title' => $form->title,
            'fields' => $fields,
        ]);

        $form->save();

        return redirect()->route('form.index')->with('success', 'Form updated successfully.');
    }
    public function archive($id)
    {
        $form = FormTemplate::findOrFail($id);
        $form->delete(); // Ini soft delete

        return redirect()->back()->with('success', 'Form archived (soft deleted) successfully.');
    }
    public function getSchema($id)
    {
        $form = FormTemplate::findOrFail($id);
        
        return response()->json(json_decode($form->form_schema, true));
    }
}
