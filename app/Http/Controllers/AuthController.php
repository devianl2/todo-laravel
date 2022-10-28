<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Passport\TokenRepository;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    protected Request $request;
    protected UserRepository $userRepo;

    public function __construct(Request $request)
    {
        $this->request = $request; // Global access request within controller
        $this->userRepo = new UserRepository(); // Repository to handle database query
    }

    /**
     * Social token validation
     * @param string $platform
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function socialTokenValidate(string $platform)
    {
        $validation = [
            'token' => 'required',
        ];

        $validator = Validator::make($this->request->all(), $validation);

        // Return validation error
        if ($validator->fails())
        {
            return response($validator->errors(), 422);
        }

        $socialUser = $this->validateSocialToken($this->request->input('token'), $platform);

        $user = $this->userRepo->findUserBySocialAuth($platform, $socialUser->getId());

        // Register an account if user not found
        if (!$user)
        {
            $formData   =   [
                'name'  =>  $socialUser->getName(),
                'email' =>  $socialUser->getEmail(),
                'password'  =>  bcrypt(Str::random(10)),
                'socialId' =>  $socialUser->getId(),
                'authType'  =>  $platform
            ];

            $user   =   $this->userRepo->saveUser($formData);
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        return response([
            'user' => $user,
            'access_token' => $accessToken,
        ]);
    }

    /**
     * Sign out and revoke token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function signOut()
    {
        $this->request->user()->token()->revoke();

        return response([
            'message' => "Sign out successfully",
        ]);
    }

    /**
     * Validate social token that obtained from frontend
     * @param $token
     * @param $platform
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function validateSocialToken($token, $platform)
    {
        $socialUser = null;

        // facebook, apple, google
        switch ($platform) {
            case 'github':
                $socialUser = Socialite::driver('github')->userFromToken($token);

                break;

            // Add more supported login method below (E.g: FB/Google/Apple..etc)

            default:

                return response([
                    'status' => 'fail',
                    'message' => 'Page not found',
                ], 404); // Not found status

        }

        // Invalid login
        if (!$socialUser) {

            return response([
                'status' => 'fail',
                'message' => 'Invalid credential',
            ], 401); // Unuthorization status
        }

        return $socialUser;
    }
}
