<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;
use RedditAPI;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->middleware('auth');
        $jobs = Job::where("status", "!=", "rejected")->where("status", "!=", "messaged")->orderBy("id", "DESC")->limit(100)->get();
        return view('home', ["jobs" => $jobs]);
    }

    /**
     * Show jobs that have been messaged.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function messagedJobs()
    {
        $this->middleware('auth');
        $jobs = Job::where("status", "=", "messaged")->orderBy("id", "DESC")->limit(100)->get();
        return view('home', ["jobs" => $jobs]);
    }

    /**
     * Show jobs that have been rejected.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function rejectedJobs()
    {
        $this->middleware('auth');
        $jobs = Job::where("status", "=", "rejected")->where("status", "!=", "messaged")->orderBy("id", "DESC")->limit(100)->get();
        return view('home', ["jobs" => $jobs]);
    }

    /**
     * Show the job message form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function msgJob($id)
    {
        $job = Job::where("id", "=", $id)->orderBy("id", "DESC")->first();
        return view('jobs.message', ["job" => $job]);
    }

    /**
     * Process msg job form
     *
     * @return JSON
     */
    public function postMsgJob($id, Request $Request)
    {
        $this->middleware('auth');
        $job = Job::where("id", "=", $id)->orderBy("id", "DESC")->first();
        $reddit = new RedditAPI("taodev91", "8fJUWB3exETiW78", "z6U6lida0GFEzacynDcllw", "a_7heyaSC04o9HoYK_5gTweI30tYmg", "https://www.reddit.com", "https://oauth.reddit.com", "STD");
        $msg = $reddit->composeMessage($job->post_author, $Request->input("subject"), $Request->input("message"));
        $job->status = "messaged";
        $job->save();
        print_r($msg);
        die();
        return redirect('/jobs');
    }

    /**
     * Process reject job
     *
     * @return JSON
     */
    public function postRejectJob($id)
    {
        $this->middleware('auth');
        $job = Job::where("id", "=", $id)->orderBy("id", "DESC")->first();
        $job->status = "rejected";
        $job->save();
        //return response()->json(["success", "true"]);
        return redirect('/jobs');
    }

    /**
     * Process restore job
     *
     * @return JSON
     */
    public function postRestoreJob($id)
    {
        $this->middleware('auth');
        $job = Job::where("id", "=", $id)->orderBy("id", "DESC")->first();
        $job->status = "";
        $job->save();
        //return response()->json(["success", "true"]);
        return redirect('/jobs/rejected');
    }


}
