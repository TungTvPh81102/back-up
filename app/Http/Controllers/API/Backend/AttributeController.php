<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    use ApiResponseTrait, ApiResponseHelpers, LoggableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Attribute::query()->latest('id')->paginate();

            if ($data->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy dữ liệu',);
            }

            return $this->respondWithSuccess('Danh sách thuộc tính', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'status' => 'required|boolean',
            ]);

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'], '-') : null;

            $attribute = Attribute::query()->create($data);

            return $this->respondCreated('Tạo mới thuộc tính thành công', $attribute);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attribute = Attribute::query()->find($id);

            if (empty($attribute)) {
                return $this->respondNotFound('Không tìm thấy dữ liệu',);
            }

            return $this->respondWithSuccess('Chi tiết thuộc tính: ' . $attribute->name, $attribute);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $attribute = Attribute::query()->find($id);

            if (empty($attribute)) {
                return $this->respondNotFound('Không tìm thấy dữ liệu',);
            }

            $data = $request->validate([
                'name' => 'sometimes|required|string',
                'status' => 'sometimes|required|boolean',
            ]);

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'], '-') : $attribute->slug;

            $attribute->update($data);

            return $this->respondOk('Cập nhật thuộc tính thành công', $attribute);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attribute = Attribute::query()->find($id);

            if (empty($attribute)) {
                return $this->respondNotFound('Không tìm thấy dữ liệu',);
            }

            $attribute->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
