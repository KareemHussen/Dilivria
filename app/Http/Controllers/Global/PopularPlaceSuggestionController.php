<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\PopularPlaceSuggestion;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PopularPlaceSuggestionController extends Controller
{
    use HandleResponseTrait;

    /**
     * Create a new popular place suggestion
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:png,jpg,jpeg,gif|max:5120',
            'address' => 'required|string|max:255',
            'lng' => 'required|string',
            'lat' => 'required|string',
            'type' => 'nullable|string|in:restaurant,pharmacy,market,gas_station,metro_station,hospital,bank,school,mall,cafe,hotel,park,cinema,other',
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(false, "", [$validator->errors()->first()], [], []);
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('place_suggestions', 'public');
                $images[] = $path;
            }
        }

        $suggestion = PopularPlaceSuggestion::create([
            'customer_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'images' => $images,
            'address' => $request->address,
            'lng' => $request->lng,
            'lat' => $request->lat,
            'type' => $request->type,
        ]);

        return $this->handleResponse(
            true,
            __("Suggestion submitted successfully. It will be reviewed by our team."),
            [],
            [
                'suggestion' => $suggestion,
            ],
            []
        );
    }

    /**
     * Get all suggestions for the authenticated user
     */
    public function getMySuggestions(Request $request)
    {
        $perPage = $request->per_page ?: 10;
        $suggestions = PopularPlaceSuggestion::where('customer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->handleResponse(
            true,
            "",
            [],
            [
                'suggestions' => $suggestions,
            ],
            []
        );
    }

    /**
     * Get a specific suggestion
     */
    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suggestion_id' => 'required|exists:popular_place_suggestions,id',
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(false, "", [$validator->errors()->first()], [], []);
        }

        $suggestion = PopularPlaceSuggestion::findOrFail($request->suggestion_id);

        // Only allow viewing own suggestions
        if ($suggestion->customer_id !== Auth::id()) {
            return $this->handleResponse(false, "", [__("Unauthorized")], [], []);
        }

        return $this->handleResponse(
            true,
            "",
            [],
            [
                'suggestion' => $suggestion,
            ],
            []
        );
    }
}
