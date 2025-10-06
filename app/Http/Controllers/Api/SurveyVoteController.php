<?php

namespace App\Http\Controllers\Api;

use App\Models\FormTemplate;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyParticipant;
use App\Models\SurveyVote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class SurveyVoteController extends Controller
{
    protected $today;
    protected $dateNow;

    public function __construct()
    {
        $this->today = Carbon::today();
        $this->dateNow = Carbon::now();
    }

    public function getSurveyVotes()
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub');
            $employee_id = $payload->get('employee_id');

            $datas = Survey::with(['surveyParticipant' => function ($query) use ($employee_id) {
                $query->where('employee_id', $employee_id);
            }, 'eventParticipant' => function ($query) use ($employee_id) {
                $query->where('employee_id', $employee_id);
            }])
            ->where(function ($query) {
                $query->where('status', '!=', 'Closed')
                      ->whereRaw("TIMESTAMP(start_date, time_start) <= ?", [$this->dateNow])
                      ->whereRaw("TIMESTAMP(end_date, time_end) >= ?", [$this->dateNow]);
            })
            ->get();
            
            return response()->json($datas);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Survey/Vote not found'], 400);
        }
    }

    public function getSurveyVotesDetails($id)
    {
        try {
            // Decrypt the ID
            $decryptedId = Crypt::decryptString($id);

            $payload = JWTAuth::parseToken()->getPayload();
            $fullname = $payload->get('fullname');

            $datas = Survey::where(function ($query) {
                $query->whereDate('start_date', '>=', $this->today)
                        ->orWhere('status', '!=', 'Closed');
            })
            ->findOrFail($decryptedId);

            $datas->fullname = $fullname;


            return response()->json($datas);
  
        } catch (\Exception $e) {
            return response()->json(['message' => 'Survey/Vote not found'], 400);
        }
    }

    public function getSurveyForm($id)
    {
      try {
        // Decrypt the ID
        $decryptedId = Crypt::decryptString($id);

        $event = Survey::findOrFail($decryptedId);

        // Check if form_id is null
        if (is_null($event->form_id)) {
            return response()->json(['message' => 'No form associated with this event', 'fields' => null], 200);
        }

        // Retrieve the form template directly by ID
        $eventForm = FormTemplate::findOrFail($event->form_id);

        // Parse the form_schema JSON string
        $formSchema = json_decode($eventForm->form_schema, true); // true = associative array

        // Check if 'fields' exists
        if (!isset($formSchema)) {
            return response()->json(['message' => 'Form schema not found'], 404);
        }

        // Return only the fields
        return response()->json($formSchema);
    
      } catch (\Exception $e) {
          return response()->json(['message' => 'Invalid event ID or error processing form'], 400);
      }
    }

    public function store(Request $request)
    {
      try {
        // Log untuk memeriksa token dan employee_id
        $payload = JWTAuth::parseToken()->getPayload();
        $userId = $payload->get('sub');
        $employee_id = $payload->get('employee_id');
        $fullname = $payload->get('fullname');
        $surveyId = Crypt::decryptString($request->surveyId);
        
        $cekSurvey = Survey::where(function ($query) {
          $query->whereDate('start_date', '>=', $this->today)
                ->orWhere('status', '!=', 'Closed');
        })->findOrFail($surveyId);

        $formId = $cekSurvey->form_id;
        
        if ($formId) {
          $validatedData = $request->validate([
            'formData' => 'required|array',
          ]);
          $formData = json_encode($validatedData['formData']);
        } else {
          $formData = json_encode([]);
        }

        // Create a new event (assuming you have an Event model)
        $event = new SurveyParticipant();
        $event->survey_id = $surveyId;
        $event->fullname = $fullname;
        $event->employee_id = $employee_id;
        $event->form_id = $formId;
        $event->form_data = $formData;
        $event->created_by = $userId;
      
        // Save the event to the database
        $event->save();
      
        // Return a success response
        return response()->json([
          'success' => true,
          'message' => 'Thankyou, your response has been recorded!',
        ], 201);
      } catch (\Exception $e) {
          Log::error('Error: ' . $e->getMessage());
          return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
      }

    }

    public function getVotingResult($id)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $surveyId = Crypt::decryptString($id);

            $survey = Survey::with(['surveyParticipant'])->findOrFail($surveyId);

            $voteCounts = [];
            $totalVotes = 0;

            foreach ($survey->surveyParticipant as $participant) {
                $formData = json_decode($participant->form_data, true);
                $answer = null;
                if (isset($formData['question_1'])) {
                    $answer = $formData['question_1'];
                } elseif (isset($formData['confirmation_1'])) {
                    $answer = $formData['confirmation_1'];
                }
                if ($answer !== null) {
                    $voteCounts[$answer] = ($voteCounts[$answer] ?? 0) + 1;
                    $totalVotes++;
                }
            }

            return response()->json([
                'surveyId' => $surveyId,
                'votes' => $voteCounts,
                'total' => $totalVotes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Voting data not found'], 400);
        }
    }

}
