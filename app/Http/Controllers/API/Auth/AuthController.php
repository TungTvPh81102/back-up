<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use F9Web\ApiResponseHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use LoggableTrait, ApiResponseTrait, ApiResponseHelpers;

    public function signUp(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validate([
                'last_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|same:password',
                'phone' => 'required|string|max:255',
            ]);

            $user = User::query()->create($data);
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->remember_token = $token;
            $user->save();

            DB::commit();

            return $this->respondCreated('Đăng ký tài khoản thành công', [
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $this->logError($e, $request->all());
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            $this->logError($e, $request->all());

            return $this->respondServerError('Đăng ký tài khoản thất bại, vui lòng thử lại',  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function signIn(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:8',
            ]);

            $user = User::query()->where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return $this->respondError('Email hoặc mật khẩu không chính xác');
            }

            if (is_null($user->email_verified_at)) {
                return $this->respondUnAuthenticated('Tài khoản chưa được kích hoạt, vui lòng thử lại');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->respondOk('Đăng nhập thành công', [
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $this->logError($e, $request->all());
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            $this->logError($e, $request->all());

            return $this->respondServerError('Đăng nhập thất bại, vui lòng thử lại sau',  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->respondOk('Đăng xuất thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại',  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
