<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AttributeValueController extends Controller
{
    use ApiResponseTrait, ApiResponseHelpers, LoggableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = AttributeValue::query()
                ->with('attribute')
                ->latest('id')
                ->paginate(10);

            if ($data->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            return $this->respondOk('Danh sách dữ liệu', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui liệu thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'attribute_id' => 'required|exists:attributes,id',
                'value' => 'required|string|unique:attribute_values,value|max:255',
                'color_code' => 'nullable|string|max:255|unique:attribute_values,color_code',
                'status' => 'required|boolean',
            ]);

            $attributeValue = AttributeValue::query()->create($data);

            return $this->respondCreated('Tạo mới giá trị thuộc tính thành công', $attributeValue);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Có lỗi xảy ra, vui liệu thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attributeValue = AttributeValue::query()
                ->with('attribute')
                ->find($id);

            if (!$attributeValue) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            return $this->respondOk('Chi tiết giá trị thuộc tính: ' . $attributeValue->value, $attributeValue);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui liệu thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $attributeValue = AttributeValue::query()->find($id);

            if (!$attributeValue) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            $data = $request->validate([
                'attribute_id' => 'sometimes|required|exists:attributes,id',
                'value' => 'sometimes|required|string|max:255|unique:attribute_values,value,' . $id,
                'color_code' => 'sometimes|nullable|string|max:255|unique:attribute_values,color_code,' . $id,
                'status' => 'sometimes|required|boolean',
            ]);

            $attributeValue->update($data);

            return $this->respondOk('Cập nhật giá trị thuộc tính thành công', $attributeValue);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Có lỗi xảy ra, vui liệu thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attributeValue = AttributeValue::query()->find($id);

            if (!$attributeValue) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            $attributeValue->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui liệu thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
