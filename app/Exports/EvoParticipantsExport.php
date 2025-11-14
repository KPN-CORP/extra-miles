<?php

namespace App\Exports;

use App\Models\EventParticipant;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EvoParticipantsExport implements FromView
{
    protected $option;
    protected $data;
    protected $username;

    public function __construct($data, $option, $username)
    {
        $this->data = $data;
        $this->option = $option;
        $this->username = $username;
    }

    public function view(): View
    {
        // filter participants berdasarkan option (program/tab aktif)
        $participants = $this->data->participants->filter(function ($p) {
            $formData = json_decode($p->form_data, true);
            return in_array($this->option, $formData['question_1'] ?? []);
        });

        return view('exports.evoparticipants', [
            'participants' => $participants,
            'option' => $this->option,
            'username' => $this->username,
        ]);
    }
}
