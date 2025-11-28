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
        $participants = $this->data->participants->filter(function ($p) {
            $formData = json_decode($p->form_data, true);

            $value = $formData['question_1'] ?? null;

            // EVO baru: question_1 berupa string
            if (is_string($value)) {
                return $value === $this->option;
            }

            // Data lama (checkbox array)
            if (is_array($value)) {
                return in_array($this->option, $value);
            }

            return false;
        });

        return view('exports.evoparticipants', [
            'participants' => $participants,
            'option' => $this->option,
            'username' => $this->username,
        ]);
    }

}
