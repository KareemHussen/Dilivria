<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChangeModeController extends Controller
{
    use HandleResponseTrait;

    public function setDocs(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                "full_name" => ['required', "string", 'regex:/^([A-Za-zÀ-ÖØ-öø-ÿ]+(?:\s+[A-Za-zÀ-ÖØ-öø-ÿ]+){3})$/'],
                "national_id" => ['required', 'string', 'numeric' ,'digits:14'],
                'id_front'=> ['required','image'],
                'id_back'=> ['required','image'],
                'selfie'=> ['required','image'],
            ]);

            if ($validator->fails()) {
                    return $this->handleResponse(
                    false,
                    "",
                    [$validator->errors()->first()],
                    [],
                    []
                );
            }
            $user = $request->user();
            //Set customer to delivery mode
            $user->delivery = 1;
            $user->delivery_status = "waiting";
            $user->full_name = $request->full_name;
            $user->national_id = $request->national_id;
            $user->id_front = $request->file('id_front')->store('/storage/docs', 'public');
            $user->id_back = $request->file('id_back')->store('/storage/docs', 'public');
            $user->selfie = $request->file('selfie')->store('/storage/docs', 'public');
            $user->save();

            return $this->handleResponse(
                true,
                __("profile.delivery docs sent"),
                [],
                [
                    "customer" => $user
                ],
                []
            );
        } catch (\Exception $e){
            return $this->handleResponse(
                false,
                __("profile.error info update"),
                [],
                [
                    $e->getMessage()
                ],
                []
            );
        }
    }
}
