<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\PopularPlace;
use App\Traits\CalculateDistanceTrait;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PopularPlaceController extends Controller
{
    use HandleResponseTrait, CalculateDistanceTrait;

    public function getAll(Request $request){
        $validator = Validator::make($request->all(), [
            "favorite_id" => "nullable|exists:favorites,id",
        ]);

        if ($validator->fails()){
            return $this->handleResponse(false,"", [$validator->errors()->first()],[],
            [
                "choose a favorite address or set the lng & lat to get the distance in KM"
            ]);
        }
        $popularPlaces = PopularPlace::all();

        $favorite = $request->favorite_id ? Favorite::findOrFail($request->favorite_id) : null;
        $lat = $favorite ? $favorite->lat : ($request->lat ?: null);
        $lng = $favorite ? $favorite->lng : ($request->lng ?: null);

        if($lng && $lat){
            foreach($popularPlaces as $place){
                $distance = $this->calcDistance($place->lat, $place->lng, $lat, $lng);
                $place->distance = $distance;
            }
        }

        return $this->handleResponse(
            true,
            "",
            [],
            [
                "popular_places" => $popularPlaces
            ],
            [
                "choose a favorite address or set the lng & lat to get the distance in KM"
            ]
        );
    }
    public function getPaginate(Request $request){
        $validator = Validator::make($request->all(), [
            "favorite_id" => "nullable|exists:favorites,id",
        ]);

        if ($validator->fails()){
            return $this->handleResponse(false,"", [$validator->errors()->first()],[],
            [
                "choose a favorite address or set the lng & lat to get the distance in KM"
            ]);
        }
        $perPage = $request->per_page ?: 10;
        $popularPlaces = PopularPlace::paginate($perPage);

        $favorite = $request->favorite_id ? Favorite::findOrFail($request->favorite_id) : null;
        $lat = $favorite ? $favorite->lat : ($request->lat ?: null);
        $lng = $favorite ? $favorite->lng : ($request->lng ?: null);

        if($lng && $lat){
            foreach($popularPlaces as $place){
                $distance = $this->calcDistance($place->lat, $place->lng, $lat, $lng);
                $place->distance = $distance;
            }
        }

        return $this->handleResponse(
            true,
            "",
            [],
            [
                "popular_places" => $popularPlaces
            ],
            [
                "choose a favorite address or set the lng & lat to get the distance in KM"
            ]
        );
    }

    public function get(Request $request){
        $validator = Validator::make($request->all(),[
            "popular_place_id" => "required|exists:popular_places,id",
            "favorite_id" => "nullable|exists:favorites,id",
        ]);

        if($validator->fails()){
            return $this->handleResponse(false,"", [$validator->errors()->first()],[],[]);
        }

        $place = PopularPlace::findOrFail($request->popular_place_id);
        $favorite = $request->favorite_id ? Favorite::findOrFail($request->favorite_id) : null;
        $lat = $favorite ? $favorite->lat : ($request->lat ?: null);
        $lng = $favorite ? $favorite->lng : ($request->lng ?: null);
        if($lng && $lat){
            $distance = $this->calcDistance($place->lat, $place->lng, $lat, $lng);
            $place->distance = $distance;
        }

        return $this->handleResponse(
            true,
            "",
            [],
            [
                "popular_place" => $place
            ],
            []
        );
    }
}
