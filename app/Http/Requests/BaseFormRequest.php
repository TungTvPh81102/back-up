<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BaseFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $errors->messages(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

        Log::error('Validation Error', [
            'class' => static::class,
            'errors' => $errors->messages(),
        ]);

        throw new HttpResponseException($response);
    }
}
