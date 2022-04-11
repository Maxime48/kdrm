<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\Console\Input\Input;

class usersController extends Controller
{
    public function all($id=1, Request $request)
    {
        $id2 = $id;
        $id = intval($id);
        $users = [];
        $allusers = User::all();

        if(!empty($request->input('search')))
        {
            $id2 = $request->input('search');
        }

        if( $id == 0 and $id2 != "1" )
        {
            for($i = 0; $i<count($allusers); $i++)
            {
                //checks in whole user-object not just in $allusers->name
                if(Str::contains($allusers[$i], $id2))
                {
                    array_push($users, $allusers[$i]);
                }
            }

            $buttons = false;
            $pageid = false;
            //config for more users needed
        }
        else
        {

            if ($id < 1) {
                $id = 1;
            }
            $perpage = 4;
            $users = User::all()->reverse()->slice(($id - 1) * $perpage, $perpage);
            $buttons = ceil(count(User::all()) / $perpage);
            $pageid = $id; // it's dumb

        }

        return view('admin.adminusers', compact('users','buttons', 'pageid'));
    }

    public function show($id)
    {

        $user = User::findOrFail($id);
        $mode = false;
        return view('admin.adminuserprofile', compact('user', 'mode'));
    }
}
