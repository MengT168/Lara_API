<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
       public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=> 'required|max:191',
            'email'=> 'required|email|max:191',
            'password'=> 'required|max:191',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>422,
                'error' => $validator->messages()
            ],422);
        }else{
            $registerCheck = User::where('email',$request->email)->orWhere('name',$request->name)->get();
            if(count($registerCheck)!=0){
                return response()->json([
                    'status' => 409,
                    'Message'   => "User already exists"
                ], 409);
            }else{
                $adminExists = User::where('is_admin', 1)->exists();
                $is_admin = $adminExists ? 0 : 1;
                $register = User::insert([
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'is_admin'    => $is_admin,
                     'created_at' => date('Y-m-d H:i:s', strtotime('+7 hours')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('+7 hours')),
                ]);
                if($register){
                    return response()->json([
                        'status' => 200,
                        'Message' => "Register Successful"
                    ]);
                }else{
                    return response()->json([
                        'status' => 500,
                        'Message' => "Register fail"
                    ]);
                }
            }
            
        }
    }

     public function loginSubmit(Request $request){
            $validator = Validator::make($request->all(),[
            'name' => 'required|max:191',
            'password' => 'required|max:191',
        ]);
    
        if($validator->fails()){
            return response()->json([
                'status' => 422,
                'error' => $validator->messages()
            ], 422);
        } else {
            if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
                if (Auth::user()->is_admin === 1) {
                    $token= Auth::user()->createToken('auth-token')->plainTextToken;
                    $username = Auth()->user()->name;
                    return response()->json([
                        'status' => 200,
                        'username'  => $username,
                        'Message' => 'Login Successful',
                        'access_token' => $token
                    ], 200);
                } 
                
                elseif(Auth::user()->is_admin === 0){
                    $token= Auth::user()->createToken('auth-token')->plainTextToken;
                    $username = Auth()->user()->name;
                    return response()->json([
                        'status' => 200,
                        'username'  => $username,
                        'Message' => 'Login Successful',
                        'access_token' => $token
                    ], 200);
                }
                else {
                    Auth::logout();
                    return response()->json([
                        'status' => 403,
                        'Message' => 'Your account is not active. Please contact the administrator.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status' => 404,
                    'Message' => 'Login Failed: Wrong Username or Password'
                ], 404);
            }
        }
    }
    public function getUser(){       
        $users = User::all();
        echo $users;
    }
}
