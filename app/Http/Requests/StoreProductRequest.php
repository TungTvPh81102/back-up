<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends BaseFormRequest
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
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'sku' => 'required|string|max:255|unique:products,sku',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_variants_enabled' => 'required|boolean',
        ];

        if ($this->boolean('is_variants_enabled')) {
            $rules['attributes'] = 'required|array|min:1';
            $rules['attributes.*'] = 'required|integer|exists:attributes,id';

            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.sku'] = 'required|string|max:255|unique:product_variants,sku';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.discount_price'] = 'nullable|numeric|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.thumbnails.*'] = 'nullable|image|mimes:jpeg,png,jpg,webp';

            $rules['variants.*.attribute_values'] = 'required|array|min:1';
            $rules['variants.*.attribute_values.*.id'] = 'required|integer|exists:attribute_values,id';
        }

        return $rules;
    }
}
