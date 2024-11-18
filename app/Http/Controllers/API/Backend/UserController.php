<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\ImageUploadTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;
use F9Web\ApiResponseHelpers;

class UserController extends Controller
{
    use ApiResponseHelpers, ApiResponseTrait, LoggableTrait, ImageUploadTrait;

    const FOLDER_NAME = 'users';

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
            $data = User::query()->latest('id')->paginate();

            if ($data->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondWithSuccess('Danh sách người dùng', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $uploadResult = null;
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');

                $uploadResult = $this->uploadImage($avatar, self::FOLDER_NAME);

                $data['avatar'] = $uploadResult;
            }

            $user = User::query()->create($data);

            DB::commit();

            return $this->respondWithSuccess('Tạo người dùng thành công', $user);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadResult) {
                $this->deleteImage($uploadResult, self::FOLDER_NAME);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::query()->find($id);

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            return $this->respondWithSuccess('Thông tin người dùng: ' . $user->email, $user);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $user = User::query()->find($id);

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            $uploadResult = null;

            if ($request->hasFile('avatar')) {
                $this->deleteImage($user->avatar, self::FOLDER_NAME);

                $avatar = $request->file('avatar');

                $uploadResult = $this->uploadImage($avatar, self::FOLDER_NAME);

                $data['avatar'] = $uploadResult;
            } else {
                $data['avatar'] = $user->avatar;
            }

            $user->update($data);

            DB::commit();

            return $this->respondWithSuccess('Cập nhật người dùng thành công', $user);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadResult) {
                $this->deleteImage($uploadResult, self::FOLDER_NAME);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $this->logError($e, $request->all());
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::query()->find($id);

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            $user->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
