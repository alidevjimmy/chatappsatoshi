<?php

namespace App\Http\Controllers\v1;

use App\Events\UserRegistred;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\UserVerificationMailable;
use App\User;
use App\Verification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Symfony\Component\HttpFoundation\Response as Code;

class UserController extends Controller implements JWTSubject
{
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $user = User::create($validatedData);
        $code = $this->createVerificationCode($user);
        $user->code = $code;
        event(new UserRegistred($user));
        return $user->toJson();
    }

    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        if (!$token = auth()->attempt($validatedData)) {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نمی باشد.'
            ], Code::HTTP_UNAUTHORIZED);
        }
        return $this->respondWithToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'با موفقیت خارج شدید.'], Code::HTTP_OK);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    protected function createVerificationCode($user)
    {
        $unExpiredVerificationCodeExists = false;
        $codes = Verification::where('user_id', $user->id)->latest()->get();
        foreach ($codes as $code) {
            if (strtotime("+5 minutes $code->created_at") > strtotime('now')) {
                $unExpiredVerificationCodeExists = true;
                break;
            }
        }
        if (!$unExpiredVerificationCodeExists) {
            $code = random_int(10000, 99999);
            $user->verifications()->create(compact('code'));
            return $code;
        }
        return false;
    }

    public function sendCode(Request $request)
    {
        $user = $request->user();
        if ($user->active) {
            return response()->json([
                'message' => 'حساب کاربری شما فعال است.'
            ], Code::HTTP_UNAUTHORIZED);
        }
        $code = $this->createVerificationCode($user);
        if ($code == false) {
            return response()->json([
                'message' => 'در حال حاضر امکان ارسال کد برای شما وجود ندارد ، دوباره تلاش کنید.'
            ], Code::HTTP_NOT_ACCEPTABLE);
        }
        $user->code = $code;
        Mail::to($user->email)->queue(new UserVerificationMailable($user));
        return response()->json([
            'message' => 'کد فعالسازی جدید برای شما ارسال شد.',
        ], Code::HTTP_CREATED);
    }

    public function checkCode(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required'
        ]);
        $user = $request->user();
        if ($user->active) {
            return response()->json([
                'message' => 'حساب کاربری شما فعال است.'
            ], Code::HTTP_UNAUTHORIZED);
        }
        $lastCode = Verification::where('user_id', $user->id)->latest()->first();
        if (strtotime("+5 minutes $lastCode->created_at") > strtotime('now')) {
            if ($lastCode == $request->code) {
                $user->update([
                    'active' => true
                ]);
                return response()->json([
                    'message' => 'حساب کاربری شما با موفقیت عال شد.'
                ], Code::HTTP_OK);
            }
            return response()->json([
                'message' => 'کد تایید نادرست است.'
            ], Code::HTTP_NOT_ACCEPTABLE);
        }
        return response()->json([
            'message' => 'کد تایید نادرست است.'
        ], Code::HTTP_NOT_ACCEPTABLE);
    }
}
