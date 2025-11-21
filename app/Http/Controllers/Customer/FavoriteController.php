<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Traits\HandleResponseTrait;
use Illuminate\Support\Facades\Validator;
class FavoriteController extends Controller
{
    use HandleResponseTrait;

    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "address" => "required|string|max:255",
            "lng" => "required|string",
            "lat" => "required|string",
        ],[
            "required" => __('validation.required'),
            "string" => __("validation.string"),
            "max" => __('validation.max.string')
        ]);

        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }
        $user = $request->user();
        $favorite = Favorite::create([
            "customer_id" => $user->id,
            "name" => $request->name,
            "address" => $request->address,
            "lng" => $request->lng,
            "lat" => $request->lat,
        ]);

        if ($favorite){
            return $this->handleResponse(
                true,
                __("favorite.address added successfully"),
                [],
                [
                    "favorite_address" => $favorite,
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("favorite.couldn't add your address"),
            [],
            [],
            []
        );
    }

    public function edit(Request $request){
        $validator = Validator::make($request->all(), [
            "favorite_id" => "required|numeric|exists:favorites,id",
            "name" => "nullable|string|max:255",
            "address" => "nullable|string|max:255",
            "lng" => "nullable|string",
            "lat" => "nullable|string",
        ],[
            "required" => __('validation.required'),
            "string" => __("validation.string"),
            "numeric" => __("validation.numeric"),
            "max" => __('validation.max.string'),
            "exists" => __("validation.exists")
        ]);

        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $user = $request->user();
        $favorite = Favorite::find($request->favorite_id);
        if ($favorite){
            if ($request->name){
                $favorite->name = $request->name;
            }
            if ($request->address){
                $favorite->address = $request->address;
            }
            if ($request->lng){
                $favorite->lng = $request->lng;
            }
            if ($request->lat){
                $favorite->lat = $request->lat;
            }

            $favorite->save();

            return $this->handleResponse(
                true,
                __("favorite.address updated"),
                [],
                [
                    "favorite_address" => $favorite
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("favorite.undefined address"),
            [],
            [],
            []
        );
    }

    public function delete(Request $request){
        $validator = Validator::make($request->all(), [
            "favorite_id" => "required|numeric|exists:favorites,id",
        ],[
            "required" => __('validation.required'),
            "numeric" => __("validation.numeric"),
            "exists" => __("validation.exists")
        ]);

        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $favorite = Favorite::find($request->favorite_id);
        if ($favorite){
            $favorite->delete();
            return $this->handleResponse(
                true,
                __("favorite.deleted"),
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("favorite.undefined address"),
            [],
            [],
            []
        );
    }

    public function get(Request $request){
        $validator = Validator::make($request->all(), [
            "favorite_id" => "required|numeric|exists:favorites,id",
        ],[
            "required" => __('validation.required'),
            "numeric" => __("validation.numeric"),
            "exists" => __("validation.exists")
        ]);

        if ($validator->fails()){
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $favorite = Favorite::find($request->favorite_id);
        if($favorite){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "favorite" => $favorite
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("favorite.undefined address"),
            [],
            [],
            []
        );
    }

    public function getAll(Request $request){
        $user = $request->user();

        $favorites = Favorite::where('customer_id', $user->id)->get();
        
        if(count($favorites) > 0){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "favorites" => $favorites
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            __('favorite.no results'),
            [],
            [],
            []
        );
    }
}
