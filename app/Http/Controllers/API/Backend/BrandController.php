<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Models\Brand;
use App\Traits\ApiResponseTrait;
use App\Traits\ImageUploadTrait;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Cloudinary\Cloudinary;

class BrandController extends Controller
{
    use ApiResponseTrait, ApiResponseHelpers, LoggableTrait, ImageUploadTrait;

    const FOLDER_NAME = 'brands';

    protected $cloudinary;
    public function __construct()
    {
        $this->cloudinary = new Cloudinary();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data  = Brand::query()->latest('id')->get();

            if ($data->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            return $this->respondWithSuccess('Danh sách thương hiệu', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'], '-') : null;

            $uploadResult = null;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');

                $uploadResult = $this->uploadImage($logo, self::FOLDER_NAME);

                $data['logo'] = $uploadResult;
            }

            $brand = Brand::query()->create($data);

            DB::commit();

            return $this->respondCreated('Tạo mới thương hiệu thành công', $brand);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadResult) {
                $this->deleteImage($uploadResult, self::FOLDER_NAME);
            }

            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->errorResponse('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data  = Brand::query()->find($id);

            if (empty($data)) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            return $this->respondWithSuccess('Chi tiết thương hiệu: ' . $data->name, $data);
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
            DB::beginTransaction();

            $brand = Brand::query()->find($id);

            if (empty($brand)) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            $data = $request->validated();

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'], '-') : $brand->slug;

            $uploadResult = null;
            if ($request->hasFile('logo')) {
                $this->deleteImage($brand->logo, self::FOLDER_NAME);

                $logo = $request->file('logo');

                $uploadResult = $this->uploadImage($logo, self::FOLDER_NAME);

                $data['logo'] = $uploadResult;
            } else {
                $data['logo'] = $brand->logo;
            }

            $brand->update($data);

            DB::commit();

            return $this->respondOk('Cập nhật thương hiệu thành công', $brand);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadResult) {
                $this->deleteImage($uploadResult, self::FOLDER_NAME);
            }

            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->errorResponse('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data  = Brand::query()->find($id);

            if (empty($data)) {
                return $this->respondNotFound('Không tìm thấy dữ liệu');
            }

            $data->delete();

            if ($data->logo) {
                $this->deleteImage($data->logo, self::FOLDER_NAME);
            }

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
