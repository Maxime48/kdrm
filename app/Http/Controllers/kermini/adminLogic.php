<?php

namespace App\Http\Controllers\kermini;

use App\Http\Controllers\Controller;
use App\Models\images;
use App\Models\Logs;
use App\Models\payloads;
use App\Models\servers;
use App\Models\user_payloads;
use DateTime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Table;

/**
 * Class made to handle all the logic associated with moderation actions
 */
class adminLogic extends Controller
{

    /**
     * @var int How many logs should be displayed on admin view
     */
    private $logsperpage = 5;

    /**
     * @var int How many servers should be displayed on admin view
     */
    private $serversperpage = 20;

    /**
     * @var int How many images should be displayed on admin view
     */
    private $imagesperpage = 8;

    /**
     * @var int How many payloads should be displayed on admin view
     */
    private $payloadsperpage = 5;

    //tools
    /**
     * Calculates the time elapsed between a date and the current one
     *
     * @param  string  $datetime a date
     * @param  boolean   $full change the date format (untested)
     * @return String
     */
    public static function time_elapsed_string(String $datetime, bool $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * Gets a user's object based on an id
     *
     * @param  string  $id user's id
     * @return object user's object
     */
    public static function getUserById($id){
        return DB::table('users')->where('id', $id)->first();
    }

    /**
     * Returns the admin view for the logs by default
     *
     * @param $pageid page's number
     * @return Application|Factory|View|RedirectResponse
     */
    public function getLogs($pageid=null, Request $request){

        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmlogs = Logs::count();
        $buttons = ceil($hmlogs / $this->logsperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $logs = Logs::all()->reverse()
            ->splice(($pageid - 1) * $this->logsperpage, $this->logsperpage);

        return view('admin.logs', compact(
            'logs',
            'buttons'
        ));
    }

    /**
     * Returns the admin view for the servers
     *
     * @param $pageid page's number
     * @return Application|Factory|View|RedirectResponse
     */
    public function serverList($pageid=null, Request $request){

        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmservers = servers::count();
        $buttons = ceil($hmservers / $this->serversperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $servers = servers::all()->reverse()
            ->splice(($pageid - 1) * $this->serversperpage, $this->serversperpage);

        return view('admin.servers', compact(
            'servers',
            'buttons'
        ));
    }

    /**
     * Returns the admin view for images
     *
     * @param $pageid
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function allImages($pageid=null, Request $request){
        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmimages = images::count();
        $buttons = ceil($hmimages / $this->imagesperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $images = images::all()->reverse()
            ->splice(($pageid - 1) * $this->imagesperpage, $this->imagesperpage);

        //redirect to view
        return view('admin.images.dashboard', compact(
           'images',
           'buttons'
        ));
    }

    public function allPayloads($pageid=null, Request $request){
        if(
            $pageid!=null
            and !is_numeric($pageid)
        ){
            $user = $request->user();
            $user->admin = -1;
            $user->save();
            return redirect()->back()->with(
                'status', "You got banned, don't play with that 😳"
            );
        }

        $hmpayloads = user_payloads::count();
        $buttons = ceil($hmpayloads / $this->payloadsperpage);

        if(
            $pageid==null
            or $pageid<1
            or $pageid > $buttons
        ){
            $pageid = 1;
        } // setting default page

        $payloads = user_payloads::all()->reverse()
            ->splice(($pageid - 1) * $this->payloadsperpage, $this->payloadsperpage);

        return view('admin.payload.dashboard', compact(
            'payloads',
            'buttons'
        ));

    }

}
