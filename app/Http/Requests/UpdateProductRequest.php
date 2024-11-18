<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('products');

        $rules = [
            'category_id' => 'sometimes|required|exists:categories,id',
            'brand_id' => 'sometimes|required|exists:brands,id',
            'sku' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_price' => 'sometimes|nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'thumbnail' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'sometimes|nullable|string|max:255',
            'content' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:active,inactive',
            'is_variants_enabled' => 'sometimes|required|boolean',
        ];

        if ($this->boolean('is_variants_enabled')) {
            $rules['attributes'] = 'sometimes|required|array|min:1';
            $rules['attributes.*'] = 'sometimes|required|integer|exists:attributes,id';

            $rules['variants'] = 'sometimes|required|array|min:1';
            $rules['variants.*.sku'] = 'sometimes|required|string|max:255';
            $rules['variants.*.price'] = 'sometimes|required|numeric|min:0';
            $rules['variants.*.discount_price'] = 'sometimes|nullable|numeric|min:0';
            $rules['variants.*.stock'] = 'sometimes|required|integer|min:0';
            $rules['variants.*.thumbnails.*'] = 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp';

            $rules['variants.*.attribute_values'] = 'sometimes|required|array|min:1';
            $rules['variants.*.attribute_values.*.id'] = 'sometimes|required|integer|exists:attribute_values,id';
        }

        return $rules;
    }
}
