<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail;
use App\Mail\PasswordReset;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;


use Carbon\Carbon;
use App\customer;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\App;
use SebastianBergmann\CodeCoverage\Report\BuilderTest;
use Illuminate\Database\Eloquent\ModelNotFoundException;




class UserController extends Controller
{
    //

    public function register(Request $request){

        
		$customerEmailCount = User::where("email",$request->input('email'))->count();
        if($customerEmailCount > 0) 
        return response()->json([
            'success' => false,
            'message' => 'Email already exists',
        ], 401);

        $customerPhoneCount = User::where("phone",$request->input('phone'))->count();
        
        if($customerPhoneCount > 0) 
        return response()->json([
            'success' => false,
            'message' => 'Phone already exists',
        ], 401);

        $validator = Validator::make($request->all(), [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email:rfc,dns|max:255',
            'phone' => 'required',
            'password' => 'required|min:8',
            // 'city' => 'required',
            // 'state' => 'required',
            // 'confirm_password' => 'required|same:password',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        
        $plainPassword=$request->password;
        $password=bcrypt($request->password);
        $request->request->add(['password' => $password]);
        // create the user account 
        //Convert this input fields to the one of springbeds then add the validation and that's all
        $created=User::create($request->all());
        $request->request->add(['password' => $plainPassword]);
             
        // login now..
        return $this->login($request);
    }
    public function login(Request $request)
    {
        
        $input = $request->only('email', 'password');
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
        // get the user 
        $user = Auth::user();
       
        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => $user
        ]);
    }
    public function logout(Request $request)
    {
        if(!User::checkToken($request)){
            return response()->json([
             'message' => 'Token is required',
             'success' => false,
            ],422);
        }
        
        try {
            JWTAuth::invalidate(JWTAuth::parseToken($request->token));
            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }

    public function getCurrentUser(Request $request){
       if(!User::checkToken($request)){
           return response()->json([
            'message' => 'Token is required'
           ],422);
       }
        
        $user = JWTAuth::parseToken()->authenticate();
       // add isProfileUpdated....
       $isProfileUpdated=false;
        if($user->isPicUpdated==1 && $user->isEmailUpdated){
            $isProfileUpdated=true;
            
        }
        $user->isProfileUpdated=$isProfileUpdated;

        return $user;
    }

   
    public function update(Request $request){
        $user=$this->getCurrentUser($request);
        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'User is not found'
            ]);
        }
    
        unset($data['token']);

        $updatedUser = User::where('id', $user->id)->update($data);
        $user =  User::find($user->id);

        return response()->json([
            'success' => true, 
            'message' => 'Information has been updated successfully!',
            'user' =>$user
        ]);

        
    }
}
