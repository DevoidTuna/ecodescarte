<?php

namespace App\Http\Controllers;

use App\Application\CollectionPoint\ListPublishedPoints;
use App\Application\CollectionPoint\SubmitCollectionPoint;
use App\Application\CollectionPoint\SubmitCollectionPointInput;
use App\Domain\CollectionPoint\WasteType;
use App\Infrastructure\Http\Presenter\CollectionPointPresenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The inbound HTTP adapter: it checks the shape of the request, delegates to a
 * use case and formats the response. No business rule lives here any more —
 * including the one saying a submission is born pending, which now belongs to
 * the entity.
 */
class CollectionPointController extends Controller
{
    /**
     * Public listing: returns approved points only.
     */
    public function index(ListPublishedPoints $listPublishedPoints)
    {
        return CollectionPointPresenter::collection($listPublishedPoints());
    }

    /**
     * Creates a new point submitted by the public.
     */
    public function store(Request $request, SubmitCollectionPoint $submitCollectionPoint)
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
        ]);

        // The status is not even accepted as input: the domain decides it.
        $point = $submitCollectionPoint(SubmitCollectionPointInput::fromValidated($data));

        return response()->json(CollectionPointPresenter::toArray($point), 201);
    }
}
