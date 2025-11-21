<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use HandleResponseTrait;

    public function get(Request $request){
        $user = Customer::where('id', $request->user()->id)
        ->select("id", "first_name", "last_name", "username", "phone", "customer_rate", "picture","delivery", "verified")
        ->first();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "profile" => $user
            ],
            []
        );
    }

    public function edit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => ['nullable','string','max:255'],
                'last_name' => ['nullable','string','max:255'],
                'phone' => ['nullable','string','unique:customers,phone'],
                'picture'=> 'nullable|image|mimes:jpeg,png,jpg,gif'
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
            if($request->first_name){
            $user->first_name = $request->first_name;
            }
            if($request->last_name){
            $user->last_name = $request->last_name;
            }
            if($request->phone){
            $user->phone = $request->phone;
            }
            if ($request->picture) {
                $imagePath = $request->file('picture')->store('/storage/profile', 'public');
                $user->picture = $imagePath;
            }
            $user->save();
            
            return $this->handleResponse(
                true,
                __("profile.info updated"),
                [],
                [
                   "user" => $user,
                ],
                []
            );
            } catch (\Exception $e) {
                return $this->handleResponse(
                    false,
                    __("profile.error info update"),
                    [
                        // $e->getMessage()
                    ],
                    [],
                    []
                );
            }
    }
}
