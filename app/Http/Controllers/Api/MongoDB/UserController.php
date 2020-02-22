<?php

namespace App\Http\Controllers\Api\MongoDB;

use App\Http\Controllers\Controller;
use App\Models\MongoDB\{Role, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Validator};
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Get all MongoDB users.
     *
     * @param 
     *
     * @return response
     */
    public function getUsers()
    {
        try {
            return response()->json(User::with('roles')->get(), 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => "Internal server error!"], 500);
        }
    }

    /**
     * Get all MongoDB users with pagination.
     *
     * @param 
     *
     * @return response
     */
    public function getUsersPaginate()
    {
        try {
            return response()->json(User::with('roles')->paginate(10), 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => "Internal server error!"], 500);
        }
    }

    /**
     * Finds a user using entered _id.
     *
     * @param 
     *
     * @return response
     */
    public function getUser($_id)
    {
        try {
            return response()->json(User::with('roles')->find($_id), 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => "Internal server error!"], 500);
        }
    }

    /**
     * Create new User.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return response
     */
    public function addUser(Request $request)
    {
        try {
            $data = $request->json()->all();

            $validator = Validator::make($data, [
                'fname' => 'required|string|min:6|max:255',
                'lname' => 'required|string|min:6|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => [
                    'required',
                    'min:6',
                    'max:255',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/',
                    'confirmed'
                ],
            ]);

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()->all()], 422);
            }

            $user = new User();
            $user->profile = [
                'fname' => $data['fname'],
                'mname' => $data['mname'],
                'lname' => $data['lname'],
            ];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->api_token = Str::random(60);
            $user->save();
            $user->roles()->save(
                new Role(['type' => 'user', 'description' => 'Newly created user'])
            );
            if ($user) {
                return response()->json(["success" => "User created successfully!"], 201);
            } else {
                return response()->json(["error" => "Could not create user!"], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(["error" => "Internal server error!"], 500);
        }
    }
}
