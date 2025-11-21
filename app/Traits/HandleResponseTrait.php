<?php

namespace App\Traits;

trait HandleResponseTrait
{
    public function handleResponse($status, $msg, $errors, $data, $notes)
    {
      return response()->json([
        "status" => $status,
        "message" => count($errors) == 1 ? $errors[0] : $msg,
        "errors" => $errors,
        "data" => $data,
        "notes" => $notes
      ]);
    }
}