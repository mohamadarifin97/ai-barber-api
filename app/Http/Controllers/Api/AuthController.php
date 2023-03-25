<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) { 
            try {
                $token = Auth::user()->createToken('API TOKEN')->plainTextToken;

                $response = [
                    'status' => 'success',
                    'message' => 'Authorized',
                    'token' => $token,
                    'id' => Auth::user()->id,
                ];
                return response()->json($response);

            } catch (Exception $e) {
                Log::error($e);

                $response = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                return response()->json($response);
            }
        } else { 
            return response()->json(['message' => 'Unauthorised']);
        } 
    }

    public function logout(Request $request)
    {
        try {
            User::find($request->id)->tokens()->delete();
            return response()->json(['status' => 'success']);

        } catch (Exception $e) {
            Log::error($e);

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            return response()->json($response);
        }

    }

    // public function getToken(Request $request)
    // {
    //     if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) { 
    //         try {
    //             $response = [
    //                 'status' => 'success',
    //                 'token' => Auth::user()->tokens()->first()->token,
    //                 'message' => 'Authorised'
    //             ];
    //             return response()->json($response);

    //         } catch (Exception $e) {
    //             Log::error($e);

    //             $response = [
    //                 'status' => 'error',
    //                 'message' => $e->getMessage()
    //             ];
    //             return response()->json($response);
    //         }
    //     } else { 
    //         return response()->json(['message' => 'Unauthorised']);
    //     } 
    // }
}
