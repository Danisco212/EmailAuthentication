<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Http\Request;
use App\Notifications\SignupActivation;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    // in this signup function it accepts user input just like in the add
    function signUp(Request $request){
        // validate the inputs
        // $request->validate([
        //      'name'=>'string|required',
        //      'email'=>'email|unique:users|required',
        //      'password'=>'required|string|confirmed' // the confirms needs password_confirmation
        //  ]);

        // then we make the use from those inputs
        $user = new User([  // user = new User is to make it, User::create is to save it
            'name'=>$request->input('name'), // to get the inputed name
            'email'=>$request->input('email'),
            'password'=>bcrypt($request->input('password')), // encrypts the password
            'activation_token'=>str_random(60)
        ]);

        // manually checking the email
        $exisingUser = DB::table('users')->where('email', $user->email)->get();
        if(count($exisingUser)>0){
            return response()->json([
                'error'=>'email already exists'
            ], 404);
            
        }else{
            $user->save();
            $details = [
                'email'=>$request->input('email'),
                'password'=>$request->input('password')
            ];
            $details['deleted_at']=null;
            Auth::attempt($details);
            $user = Auth::user(); // gives us the authenticated user
            $activestate = $user->active;
            $token = $user->createToken($user->email.'-'.now()); // creates a token for the user

            return response()->json([
                'id'=>$user->id,
                'email'=>$user->email,
                'name'=>$user->name,
                'rating'=>$user->rating,
                'token'=>$token->accessToken // this gives us the acces token that they have
            ], 200);
        }
        // send the notification
        //$user->notify(new SignupActivation($user));

        // return response()->json([
        //     'name'=>$user->name,
        //     'email'=>$user->email,
        //     'password'=>$user->password,
        //     'active'=>$user->active
        // ], 200);

    }

    public function login(Request $request){
        // validate inputs
        // $request->validate([
        //     'email'=>'required|email|exists:users,email', // read carefully
        //     'password'=> 'required'
        // ]);  
        // done inside the android application

        //check for mail and password, and if the account if verified
        $details = [
            'email'=>$request->input('email'),
            'password'=>$request->input('password')
        ];
        $details['deleted_at']=null;

        // check if the user is correct
        if(Auth::attempt($details)){
            $user = Auth::user(); // gives us the authenticated user
            $activestate = $user->active;
            $token = $user->createToken($user->email.'-'.now()); // creates a token for the user

            if($activestate ==0){
                return response()->json([
                    'error'=>'please verify your email'
                ], 404);
            }

            return response()->json([
                'id'=>$user->id,
                'email'=>$user->email,
                'name'=>$user->name,
                'rating'=>$user->rating,
                'token'=>$token->accessToken // this gives us the acces token that they have
            ], 200);
        }else{
            return response()->json([
                'error'=>'invalid email or password'
            ], 404);
        }
    }

    // to validate the account we use
    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        return "Your account has been verified";
    }


    // test function to get user active status
    function findUser($id){
        $user = User::find($id);
        return $user->questions()->get();
    }
}
