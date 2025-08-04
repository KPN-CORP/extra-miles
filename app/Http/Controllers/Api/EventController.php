<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\FormTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class EventController extends Controller
{

    protected $today;
    protected $dateNow;

    public function __construct()
    {
        $this->today = Carbon::today();
        $this->dateNow = Carbon::now();
    }

    public function getEvents()
    {
      $payload = JWTAuth::parseToken()->getPayload();
      $employee_id = $payload->get('employee_id');

      $employee = Employee::select('group_company', 'unit', 'office_area', 'job_level')->where('employee_id', $employee_id)->first();

      $events = Event::with(['eventParticipant' => function ($query) use ($employee_id) {
        $query->where('employee_id', $employee_id);
      }])
      ->where(function ($query) {
        $query->whereRaw("TIMESTAMP(start_date, time_start) <= ?", [$this->dateNow])
              ->whereRaw("TIMESTAMP(end_date, time_end) >= ?", [$this->dateNow])
              ->orWhere('status', '!=', 'Closed');;
      })
      ->where(function ($query) use ($employee) {
          $query->where(function ($q) use ($employee) {
              $q->whereNull('businessUnit')
                ->orWhereJsonLength('businessUnit', 0)
                ->orWhereJsonContains('businessUnit', $employee->group_company);
          })
          ->where(function ($q) use ($employee) {
              $q->whereNull('unit')
                ->orWhereJsonLength('unit', 0)
                ->orWhereJsonContains('unit', $employee->unit);
          })
          ->where(function ($q) use ($employee) {
              $q->whereNull('location')
                ->orWhereJsonLength('location', 0)
                ->orWhereJsonContains('location', $employee->office_area);
          })
          ->where(function ($q) use ($employee) {
              $q->whereNull('jobLevel')
                ->orWhereJsonLength('jobLevel', 0)
                ->orWhereJsonContains('jobLevel', $employee->job_level);
          });
      })
      ->orderBy('start_date', 'asc')
      ->get();

      return response()->json($events);
    }

    private function checkExpPayload($payload) {
      if (isset($payload['exp']) && $payload['exp'] < time()) {
        return response()->json(['error' => 'Token expired'], 401);
      }else{
        return response()->json(['success' => 'Active'], 200);
      }
    }

    public function myEvents()
    {
      $payload = JWTAuth::parseToken()->getPayload();
      $userId = $payload->get('sub');
      $employee_id = $payload->get('employee_id');

      $events = Event::whereHas('eventParticipant', function ($query) use ($employee_id) {
          $query->where('employee_id', $employee_id)
            ->whereIn('status', ['Confirmation', 'Registered']);
      })->with(['eventParticipant' => function ($query) use ($employee_id) {
          $query->where('employee_id', $employee_id)
            ->whereIn('status', ['Confirmation', 'Registered']);
      }])
      ->where(function ($query) {
        $query->whereDate('start_date', '>=', $this->today)
              ->orWhere('status', '!=', 'Closed');
      })
      ->get();

      return response()->json($events);
    }

    public function eventConfirmation(Request $request) {
      try {
        // Log untuk memeriksa token dan employee_id
        $payload = JWTAuth::parseToken()->getPayload();
        $userId = $payload->get('sub');
        $employee_id = $payload->get('employee_id');
        $eventId = Crypt::decryptString($request->eventId);
        $status = $request->status;
        $messages = $request->messages;

        if (!in_array($status, ['confirm', 'cancel'])) {
          return response()->json(['error' => 'Invalid status.'], 422);
        }

        if ($status === 'cancel' && empty($messages)) {
          return response()->json(['error' => 'Reason is required when cancelling.'], 422);
        }      
        
        $event = EventParticipant::where('employee_id', $employee_id)->where('event_id', $eventId)->first();

        if ($status === 'confirm') {
          $event->status = 'Registered';          
        } else {
          $event->status = 'Canceled';
          $event->messages = $messages;
        }
        
        $event->updated_by = $userId;
        $event->save();
      
        // Return a success response
        return response()->json([
          'success' => true,
          'message' => 'Event confirmed',
        ], 201);
      } catch (\Exception $e) {
          Log::error('Error submit event: ' . $e->getMessage());
          return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
      }
    }

    public function eventAttendance(Request $request)
    {
        try {
            // Validate required input
            $request->validate([
                'qrCode' => 'required|string',
                'eventId' => 'required|string',
            ]);

            // Parse JWT token
            try {
                $payload = JWTAuth::parseToken()->getPayload();
            } catch (TokenExpiredException $e) {
                return response()->json(['error' => 'Token expired'], 401);
            } catch (TokenInvalidException $e) {
                return response()->json(['error' => 'Token invalid'], 401);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $employee_id = $payload->get('employee_id');
            if (!$employee_id) {
                return response()->json(['error' => 'Employee ID not found in token'], 400);
            }

            // Decrypt QR code and event ID
            try {
                $qrCode = Crypt::decryptString($request->qrCode);
                $eventId = Crypt::decryptString($request->eventId);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                Log::error('Decryption failed: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid encrypted data'], 400);
            }

            // Match QR Code with Event ID
            if ($qrCode !== $eventId) {
                return response()->json(['error' => 'QR Code mismatch', 'match' => false], 400);
            }

            // Find the participant record
            $participant = EventParticipant::where('employee_id', $employee_id)
                ->where('event_id', $eventId)
                ->where('status', 'Registered')
                ->first();

            if (!$participant) {
                return response()->json(['error' => 'No registered event found for this user'], 404);
            }

            // Update attendance
            $participant->attending_at = now();
            $participant->attending_status = 'Attending';
            $participant->save();

            return response()->json([
                'success' => true,
                'message' => 'Attendance successfully!',
                'data' => [
                    'event_id' => $eventId,
                    'employee_id' => $employee_id,
                    'attending_at' => $participant->attending_at,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error submitting event attendance: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function getEventDetails($id)
    {
      try {
          // Decrypt the ID
          $decryptedId = Crypt::decryptString($id);

          $payload = JWTAuth::parseToken()->getPayload();
          $employee_id = $payload->get('employee_id');

          // Find the event by the decrypted ID
          $event = Event::with(['eventParticipant' => function ($query) use ($employee_id) {
            $query->where('employee_id', $employee_id)
                  ->whereIn('status', ['Confirmation', 'Registered', 'Waiting List']);
            }])
            ->where(function ($query) {
                $query->whereDate('start_date', '>=', $this->today)
                      ->orWhere('status', '!=', 'Closed');
            })
            ->findOrFail($decryptedId);
          return response()->json($event);
      } catch (\Exception $e) {
          return response()->json(['message' => 'Event not found'], 400);
      }
    }

    public function getEventForm($id)
    {
      try {
        // Decrypt the ID
        $decryptedId = Crypt::decryptString($id);

        $event = Event::findOrFail($decryptedId);

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
          // Parse JWT payload
          $payload = JWTAuth::parseToken()->getPayload();
          $userId = $payload->get('sub');
          $employee_id = $payload->get('employee_id');
          $fullname = $payload->get('fullname');

          // Decrypt event ID
          $eventId = Crypt::decryptString($request->eventId);

          $cekUser = Employee::where('employee_id', $employee_id)->first();

          // Validate event: must be active or not closed
          $cekEvent = Event::where(function ($query) {
                  $query->whereDate('start_date', '>=', now()->today())
                        ->orWhere('status', '!=', 'Closed');
              })
              ->findOrFail($eventId);

          $formId = $cekEvent->form_id;

          // Handle form data only if form is present
          $validatedData = $request->validate([
              'formData' => 'required|array',
              'personalMobileNumber' => 'required|string',
          ]);

          // Encode the default object into a JSON string
          $formData = json_encode($validatedData['formData']);
          $personalMobileNumber = "'" . $validatedData['personalMobileNumber'];

          // Update employee mobile number
          $employee = Employee::where('employee_id', $employee_id)->first();
          
          $employee->whatsapp_number = $personalMobileNumber;
          $employee->save();

          // Register event participation
          $eventParticipant = new EventParticipant();
          $eventParticipant->event_id = $eventId;
          $eventParticipant->fullname = $fullname;
          $eventParticipant->employee_id = $employee_id;
          $eventParticipant->form_id = $formId;
          $eventParticipant->form_data = $formData;
          $eventParticipant->job_level = $cekUser->job_level;
          $eventParticipant->unit = $cekUser->unit;
          $eventParticipant->business_unit = $cekUser->group_company;
          $eventParticipant->location = $cekUser->office_area;
          $eventParticipant->created_by = $userId;
          $eventParticipant->save();

          // Success response
          return response()->json([
              'success' => true,
              'message' => 'Event registered successfully.',
          ], 201);

      } catch (ValidationException $e) {
          return response()->json([
              'success' => false,
              'message' => 'Validation failed.',
              'errors' => $e->validator->errors(),
          ], 422);
      } catch (\Exception $e) {
          Log::error('Error submitting event: ' . $e->getMessage());
          return response()->json([
              'success' => false,
              'message' => 'Something went wrong.',
              'error' => $e->getMessage(),
          ], 500);
      }
    }

    public function checkRegistration($id)
    {

        $payload = JWTAuth::parseToken()->getPayload();
        $userId = $payload->get('sub');
        $eventId = Crypt::decryptString($id);
        $employee_id = $payload->get('employee_id');
        $registered = EventParticipant::where('employee_id', $employee_id)
                                        ->where('event_id', $eventId)
                                        ->first();

        return response()->json(['registered' => $registered]);
    }
}
