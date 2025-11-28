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
use Illuminate\Contracts\Encryption\DecryptException;
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
      ->where('category' ,'!=' ,'EVO')
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
      ->where('category' ,'!=' ,'EVO')
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

            if (is_null($event->form_id)) {
                return response()->json([
                    'message' => 'No form associated with this event',
                    'fields' => null,
                    'programQuota' => []
                ], 200);
            }

            // Retrieve form template
            $eventForm = FormTemplate::findOrFail($event->form_id);

            // Parse JSON schema
            $formSchema = json_decode($eventForm->form_schema, true);

            if (!isset($formSchema)) {
                return response()->json(['message' => 'Form schema not found'], 404);
            }

            /* ============================================
            *  ADD PROGRAM QUOTA STATUS HERE
            * ============================================ */

            // Ambil semua program dari schema (question_1 only)
            $programOptions = collect($formSchema['fields'] ?? [])
                ->where('name', 'question_1')
                ->flatMap(fn ($f) => $f['options'] ?? [])
                ->values();

            // Hitung quota per program
            $programQuota = $programOptions->map(function ($program) use ($event) {
                $count = EventParticipant::where('event_id', $event->id)
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.question_1')) = ?", [$program])
                    ->count();

                return [
                    'program'    => $program,
                    'count'      => $count,
                    'quota'      => $event->quota,
                    'quota_full' => $event->quota !== null && $count >= $event->quota,
                ];
            })->values();

            /* ============================================
            *  RETURN JSON WITH QUOTA
            * ============================================ */

            return response()->json([
                'fields'       => $formSchema['fields'],  // tetap sama
                'programQuota' => $programQuota          // tambahan quotas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid event ID or error processing form'
            ], 400);
        }
    }


    public function store(Request $request)
    {
        try {
            // 🔐 Ambil payload dari token
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub');
            $employeeId = $payload->get('employee_id');
            $fullname = $payload->get('fullname');

            // 🔓 Dekripsi event ID
            $eventId = Crypt::decryptString($request->eventId);

            // 🧾 Validasi data input
            $validated = $request->validate([
                'formData' => 'required|array',
                'personalMobileNumber' => 'required|string',
            ]);

            // 🧍‍♂️ Ambil data employee
            $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

            // 🎯 Validasi event masih aktif / belum closed
            $event = Event::where(function ($q) {
                    $q->whereDate('start_date', '>=', now()->today())
                      ->orWhere('status', '!=', 'Closed');
                })
                ->findOrFail($eventId);

            // 🧩 Cek apakah sudah pernah terdaftar
            $alreadyRegistered = EventParticipant::where('event_id', $eventId)
                ->where('employee_id', $employeeId)
                ->exists();

            if ($alreadyRegistered) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already registered for this event.',
                ], 409);
            }

            // 📞 Update nomor WhatsApp (tanpa tanda kutip tambahan)
            $employee->update([
                'whatsapp_number' => $validated['personalMobileNumber'],
            ]);

            // 🧠 Encode form data
            $formDataJson = json_encode($validated['formData']);

            // 📝 Simpan ke tabel peserta
            EventParticipant::create([
                'event_id' => $eventId,
                'fullname' => $fullname,
                'employee_id' => $employeeId,
                'form_id' => $event->form_id,
                'form_data' => $formDataJson,
                'job_level' => $employee->job_level,
                'unit' => $employee->unit,
                'business_unit' => $employee->group_company,
                'location' => $employee->office_area,
                'created_by' => $userId,
            ]);

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
        } catch (DecryptException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid event ID.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error submitting event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            // 🔐 Ambil payload JWT
            $payload = JWTAuth::parseToken()->getPayload();
            $employeeId = $payload->get('employee_id');
            $userId = $payload->get('sub');

            // 🔓 Dekripsi event ID
            $eventId = Crypt::decryptString($request->eventId);

            // 🧾 Validasi input
            $validated = $request->validate([
                'formData' => 'required|array',
                'personalMobileNumber' => 'required|string',
            ]);

            // 🧍 Ambil data peserta
            $participant = EventParticipant::where('event_id', $eventId)
                ->where('employee_id', $employeeId)
                ->firstOrFail();

            // 📱 Update nomor WhatsApp employee
            Employee::where('employee_id', $employeeId)->update([
                'whatsapp_number' => $validated['personalMobileNumber'],
            ]);

            $participant->form_data = json_encode($validated['formData']);
            $participant->updated_by = $userId;

            $participant->save();

            return response()->json([
                'success' => true,
                'message' => 'Event registration updated successfully.',
                'formData' => json_encode($validated['formData']),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (DecryptException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid event ID.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error updating event registration: ' . $e->getMessage());
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

    public function getEvo()
    {
      $payload = JWTAuth::parseToken()->getPayload();
      $employee_id = $payload->get('employee_id');

      $employee = Employee::select('group_company', 'unit', 'office_area', 'job_level')->where('employee_id', $employee_id)->first();

      $events = Event::with(['eventParticipant' => function ($query) use ($employee_id) {
        $query->where('employee_id', $employee_id);
      }])
      ->where(function ($query) {
        $query->orWhere('status', '!=', 'Closed')
              // ->whereRaw("TIMESTAMP(start_date, time_start) <= ?", [$this->dateNow])
              // ->whereRaw("TIMESTAMP(end_date, time_end) >= ?", [$this->dateNow])
              ;
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
      ->where('category', 'EVO')
      ->get();

      return response()->json($events);
    }

    public function checkEvo()
    {

        $payload = JWTAuth::parseToken()->getPayload();
        $userId = $payload->get('sub');
        $employee_id = $payload->get('employee_id');
        $registered = EventParticipant::with('event')
        ->whereHas('event', fn($q) => $q->where('category', 'EVO'))
        ->where('employee_id', $employee_id)
        ->first();


        return response()->json(['registered' => $registered]);
    }

    public function storeEvo(Request $request)
    {
        try {
            // Ambil payload
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub');
            $employeeId = $payload->get('employee_id');
            $fullname = $payload->get('fullname');

            $eventId = Crypt::decryptString($request->eventId);

            // Validasi
            $validated = $request->validate([
                'formData' => 'required|array',
                'personalMobileNumber' => 'required|string',
            ]);

            $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

            $event = Event::findOrFail($eventId);

            // Hanya EVO memakai multi-transaksi
            $isEvo = ($event->category === 'EVO');

            // Update nomor WA
            $employee->update([
                'whatsapp_number' => $validated['personalMobileNumber'],
            ]);

            // Ambil list pilihan
            $selections = $validated['formData']['question_1'] ?? [];

            if (!is_array($selections)) {
                $selections = [$selections];
            }

            if ($isEvo) {

                // Cek duplikasi tiap program
                foreach ($selections as $program) {
                    $already = EventParticipant::where('event_id', $eventId)
                        ->where('employee_id', $employeeId)
                        ->whereJsonContains('form_data->question_1', $program)
                        ->exists();

                    if ($already) {
                        return response()->json([
                            'success' => false,
                            'message' => "You are already registered for program: $program",
                        ], 409);
                    }
                }

                // Insert 1 row per program
                foreach ($selections as $program) {
                    EventParticipant::create([
                        'event_id'      => $eventId,
                        'fullname'      => $fullname,
                        'employee_id'   => $employeeId,
                        'form_id'       => $event->form_id,
                        'form_data'     => json_encode([
                            'question_1'      => $program,
                            'countryCode'     => $validated['formData']['countryCode'] ?? '+62',
                            'whatsapp_number' => $validated['formData']['whatsapp_number'] ?? '',
                        ]),
                        'job_level'     => $employee->job_level,
                        'unit'          => $employee->unit,
                        'business_unit' => $employee->group_company,
                        'location'      => $employee->office_area,
                        'created_by'    => $userId,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'EVO registration completed successfully.',
                ], 201);
            }

            // NORMAL (non-EVO)
            EventParticipant::create([
                'event_id'      => $eventId,
                'fullname'      => $fullname,
                'employee_id'   => $employeeId,
                'form_id'       => $event->form_id,
                'form_data'     => json_encode($validated['formData']),
                'job_level'     => $employee->job_level,
                'unit'          => $employee->unit,
                'business_unit' => $employee->group_company,
                'location'      => $employee->office_area,
                'created_by'    => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event registered successfully.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error submitting event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEvo(Request $request)
    {
        try {
            $payload     = JWTAuth::parseToken()->getPayload();
            $employeeId  = $payload->get('employee_id');
            $userId      = $payload->get('sub');

            $eventId = Crypt::decryptString($request->eventId);

            $validated = $request->validate([
                'formData' => 'required|array',
                'personalMobileNumber' => 'required|string',
            ]);

            $event = Event::findOrFail($eventId);
            $isEvo = ($event->category === 'EVO');

            Employee::where('employee_id', $employeeId)->update([
                'whatsapp_number' => $validated['personalMobileNumber'],
            ]);

            $selections = $validated['formData']['question_1'] ?? [];
            if (!is_array($selections)) $selections = [$selections];

            if ($isEvo) {
                // Hapus semua transaksi lama peserta
                EventParticipant::where('event_id', $eventId)
                    ->where('employee_id', $employeeId)
                    ->delete();

                // Insert ulang
                foreach ($selections as $program) {
                    EventParticipant::create([
                        'event_id'      => $eventId,
                        'fullname'      => $payload->get('fullname'),
                        'employee_id'   => $employeeId,
                        'form_id'       => $event->form_id,
                        'form_data'     => json_encode([
                            'question_1'      => $program,
                            'countryCode'     => $validated['formData']['countryCode'] ?? '+62',
                            'whatsapp_number' => $validated['formData']['whatsapp_number'] ?? '',
                        ]),
                        'created_by'    => $userId,
                        'updated_by'    => $userId,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'EVO registration updated successfully.',
                ], 200);
            }

            // Non-EVO Normal Update
            $participant = EventParticipant::where('event_id', $eventId)
                ->where('employee_id', $employeeId)
                ->firstOrFail();

            $participant->form_data = json_encode($validated['formData']);
            $participant->updated_by = $userId;
            $participant->save();

            return response()->json([
                'success' => true,
                'message' => 'Event registration updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating event registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


}
