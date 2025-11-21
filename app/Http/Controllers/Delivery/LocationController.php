<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HandleResponseTrait;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    use HandleResponseTrait;

    public function setDeliveryLocation(Request $request){
        $validator = Validator::make($request->all(), [
            'lng' => "required|string",
            'lat' => "required|string",
        ],[
            "required" => __("validation.required"),
            "string" => __("validation.string"),
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

        $delivery = $request->user();

        $delivery->update([
            "lng" => $request->lng,
            "lat" => $request->lat
        ]);

        return $this->handleResponse(
            true,
            "updated successfully",
            [],
            [],
            []
        );
    }
}
