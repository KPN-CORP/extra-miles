<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DecryptsRouteId;
use App\Http\Requests\WellnessActivityTypeRequest;
use App\Models\WellnessActivityType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WellnessActivityTypeController extends Controller
{
    use DecryptsRouteId;

    private const FIELDS = [
        'name',
        'description',
        'is_active',
        'check_in_opens_minutes_before',
        'check_in_closes_minutes_after',
    ];

    public function index()
    {
        $types = WellnessActivityType::withCount('activities')
            ->orderBy('name')
            ->get();

        $archived = WellnessActivityType::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->get();

        return view('pages.admin.wellness.types.index', [
            'parentLink' => 'Wellness',
            'link' => 'Activity Types',
            'types' => $types,
            'archived' => $archived,
        ]);
    }

    public function store(WellnessActivityTypeRequest $request)
    {
        WellnessActivityType::create($request->safe()->only(self::FIELDS) + [
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', __('Activity type created.'));
    }

    public function update(WellnessActivityTypeRequest $request, string $encryptedId)
    {
        $type = WellnessActivityType::findOrFail($this->decryptId($encryptedId));

        $type->update($request->safe()->only(self::FIELDS) + [
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', __('Activity type updated.'));
    }

    public function archive(string $encryptedId)
    {
        $type = WellnessActivityType::withCount('activities')
            ->findOrFail($this->decryptId($encryptedId));

        // Activities reference the type with restrictOnDelete; archiving one that
        // is still in use would leave those activities pointing at a hidden type.
        if ($type->activities_count > 0) {
            return redirect()->back()->with('error', __('This type still has :count activity(ies). Archive those first.', ['count' => $type->activities_count]));
        }

        $type->delete();

        return redirect()->back()->with('success', __('Activity type archived.'));
    }

    public function restore(string $encryptedId)
    {
        $type = WellnessActivityType::onlyTrashed()->findOrFail($this->decryptId($encryptedId));
        $type->restore();

        return redirect()->back()->with('success', __('Activity type restored.'));
    }

    /**
     * Printable attendance QR. One code per type, reused by every session of
     * every activity under it.
     */
    public function qr(string $encryptedId)
    {
        $type = WellnessActivityType::findOrFail($this->decryptId($encryptedId));

        return view('pages.admin.wellness.types.qr', [
            'type' => $type,
        ]);
    }

    /**
     * Invalidates every printed copy of the current code, e.g. after a photo
     * of it has been shared around.
     */
    public function rotateQr(string $encryptedId)
    {
        $type = WellnessActivityType::findOrFail($this->decryptId($encryptedId));

        $type->update([
            'qr_token' => (string) Str::uuid(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', __('A new QR code has been generated. Reprint it wherever the old one is posted.'));
    }
}
