<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use TimeHunter\LaravelGoogleReCaptchaV3\Validations\GoogleReCaptchaV3ValidationRule;

class userModify extends Controller
{

    public function adminModify($id, Request $request){
        //Find the user
        $user = User::findOrFail($id);
        User::all();
        //Has more "power" or be me
        if(
            $request->user()->admin > $user->admin
            or $_ENV['MASTER_EMAIL'] == $request->user()->email
        ){
            $validator = Validator::make($request->all(), [
                    'name' => 'required|alpha_dash|string|max:255',
                    'forename' => 'nullable|alpha|string|max:15',
                    'surname' => 'nullable|alpha|string|max:15',
                    'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                    'bio' => 'nullable|regex:^[\pL\-]+\s?[\pL\-]*$^|string|max:255',
                    'banner' => 'nullable|url|string|max:255',
                    'avatar' => 'nullable|url|string|max:255',
                    'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('adminusermodify')]
                ]);

            if ($validator->fails()) {
                return redirect()->route('user', ['id' => $user->id])->with(
                    'status', 'Query invalid'
                )->withErrors($validator);
            }else {
                //update the infos
                $user->name =$request->name;
                $user->forename =$request->forename;
                $user->surname =$request->surname;
                $user->email =$request->email ?? 'change@me.fr';
                $user->bio =$request->bio ?? 'nope';
                $user->banner =$request->banner ?? 'nope';
                $user->avatar=$request->avatar ?? 'nope';
                $user->updated_at = date_format(date_create(), 'Y-m-d H:i:s');
                $user->update();

                return redirect()->route('user', ['id' => $user->id])->with(
                    'status', 'The user: ' . $user->name . " has been edited 🗿"
                );
            }
        }
        else
        {
            return back()->with('status', 'Error on "modify" request');
        }
    }

    public function userMenu(Request $request){
        $user = $request->user();
        return view('settings.dashboard', compact('user'));
    }

    public function userModify(Request $request){
        $user = $request->user();
            $validator = Validator::make($request->all(), [
                'name' => 'required|alpha_dash|string|max:255',
                'forename' => 'nullable|alpha|string|max:15',
                'surname' => 'nullable|alpha|string|max:15',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'bio' => 'nullable|regex:^[\pL\-]+\s?[\pL\-]*$^|string|max:255',
                'banner' => 'nullable|url|string|max:255',
                'avatar' => 'nullable|url|string|max:255',
                'npass' => ['nullable', Rules\Password::defaults()],
                'cpass' => ['required', 'current_password'],
                'g-recaptcha-response' => [new GoogleReCaptchaV3ValidationRule('usermodify')]
            ]);

            if ($validator->fails()) {
                return redirect()->route('usermenu')->with(
                    'status', 'Query invalid'
                )->withErrors($validator);
            }else {
                //update the infos
                $user->name =$request->name;
                $user->forename =$request->forename;
                $user->surname =$request->surname;
                $user->email =$request->email;
                $user->bio =$request->bio ?? 'nope';
                $user->banner =$request->banner ?? 'nope';
                $user->avatar=$request->avatar ?? 'nope';
                if($request->npass !== null){
                    $user->password = Hash::make($request->npass);
                }
                $user->updated_at = date_format(date_create(), 'Y-m-d H:i:s');
                $user->update();

                return redirect()->route('usermenu')->with(
                    'status', 'Profile edited🗿'
                );
            }

    }

    public function changeMode($id)
    {
        $user = User::findOrFail($id);
        $mode = true;
        return view('admin.adminuserprofile', compact('user', 'mode'));
    }
}
