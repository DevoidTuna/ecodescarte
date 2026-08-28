<?php

namespace App\Http\Controllers\Admin;

use App\Application\CollectionPoint\ApproveCollectionPoint;
use App\Application\CollectionPoint\ListPendingSubmissions;
use App\Domain\CollectionPoint\Exception\CollectionPointNotFound;
use App\Domain\CollectionPoint\WasteType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Presenter\CollectionPointPresenter;
use App\Models\CollectionPoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The two routes of the moderation flow — the queue and the approval — go
 * through use cases. The maintenance ones (full listing, edit, delete) stay on
 * plain Eloquent: they are CRUD with no rules of their own, and wrapping them
 * in layers would add indirection and nothing else.
 */
class CollectionPointController extends Controller
{
    /**
     * Lists ALL points, approved and pending alike, for management.
     */
    public function index()
    {
        return CollectionPoint::query()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Lists only the points awaiting approval.
     */
    public function pending(ListPendingSubmissions $listPendingSubmissions)
    {
        return CollectionPointPresenter::collection($listPendingSubmissions());
    }

    /**
     * Approves a pending point, making it visible on the public map.
     */
    public function approve(string $point, ApproveCollectionPoint $approveCollectionPoint)
    {
        try {
            $approved = $approveCollectionPoint((int) $point);
        } catch (CollectionPointNotFound) {
            abort(404);
        }

        return response()->json(CollectionPointPresenter::toArray($approved));
    }

    /**
     * Edits an existing point (any field, including the status).
     */
    public function update(Request $request, CollectionPoint $point)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'waste_types' => ['required', 'array', 'min:1'],
            'waste_types.*' => [Rule::in(WasteType::values())],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['pending', 'approved'])],
        ]);

        $point->update($data);

        return response()->json($point);
    }

    /**
     * Deletes a point.
     */
    public function destroy(CollectionPoint $point)
    {
        $point->delete();

        return response()->noContent();
    }
}
