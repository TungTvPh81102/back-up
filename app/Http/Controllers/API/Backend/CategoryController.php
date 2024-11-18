<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryController extends Controller
{
    use ApiResponseTrait, ApiResponseHelpers, LoggableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Category::query()->with('parent')->get();

            if ($data->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondWithSuccess('Danh sách danh mục', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'] . '-') : null;

            $category = Category::query()->create($data);

            return $this->respondWithSuccess('Thêm mới danh mục thành công', $category);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
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
            $data = Category::query()->with('parent')->find($id);

            if (empty($data)) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondWithSuccess('Chi tiết danh mục: ' . $data->name, $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $category = Category::query()->find($id);

            if (empty($category)) {
                return $this->respondNotFound('Không tìm thấy danh mục');
            }

            $data = $request->validated();

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'] . '-') : $category->slug;

            $category->update($data);

            return $this->respondWithSuccess('Cập nhật danh mục thành công', $category);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
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
            $category = Category::query()
                ->with(['children'])
                ->find($id);

            if (empty($category)) {
                return $this->respondNotFound('Không tìm thấy danh mục');
            }

            if ($category->children->count() > 0) {
                return $this->respondError('Danh mục này có danh mục con, không thể xóa');
            }

            if ($category->products->count() > 0) {
                return $this->respondError('Danh mục này có sản phẩm, không thể xóa');
            }

            $category->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
