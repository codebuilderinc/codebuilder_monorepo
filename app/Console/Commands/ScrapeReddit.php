<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use \CodeWizz\RedditAPI\RedditAPI;

use App\Notifications\PushJobs;

use App\Models\Job;
use Notification;
use App\Models\User;
use App\Notifications\HelloNotification;


class tcpBot extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:scrapeReddit';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Scrape Job Board Subreddits and store posts in database & send out push notifications.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $irc = new IRC();
    $colors = $irc->colors();

    $reddit = new RedditAPI("taodev91", "8fJUWB3exETiW78", "z6U6lida0GFEzacynDcllw", "a_7heyaSC04o9HoYK_5gTweI30tYmg", "https://www.reddit.com", "https://oauth.reddit.com", "STD");
    //fetch top Reddit posts
    $hot = $reddit->getHot("forhire+jobs4bitcoins", 100, false, false);
    foreach ($hot->data->children as $link) {
      $link = $link->data;
      $title = $link->title;
      $href = $link->permalink;
      $body = $link->selftext_html;
      $plain_body = $link->selftext;
      $author = $link->author;

      /* if link text doesnt contain word hiring then skip it */
      if (!strstr(strtolower($title), "hiring")) continue;
      $new = true; /* Got a new link */

      /* Check if link already exists in database */
      $exists = Job::where("href", "=", $href)->first();
      if ($exists) continue;

      /* CLI Output */
      echo $title . "\n";
      echo $href . "\n\n";

      /* Insert Link into Database */
      $job = new Job;
      $job->post_title = $title;
      $job->post_body = "$body";
      $job->post_author = $author;
      $job->href = $href;
      $job->flair_text = ""; //$flair_text;
      $job->post_body_plain = $plain_body;
      $job->status = "";
      $job->subreddit = $link->subreddit;
      $job->save();

      User::find(1)->notify(new HelloNotification);

      /* Send out notifications for web browser / android 
     Notification::send(User::all(), new PushJobs([
        "job_id" => $job->id,
        "title" => $job->post_title,
        "body" => $job->post_body_plain,
        "icon" => "https://codebuilder.us/images/mandala4_75.png"
      ]));

      echo "Sent Notification.";

      $cmd = "ASLkjda*s@ !yQgCRtXrhmRNbyRMiV:subtlefu.ge https://jobbit.codebuilder.us/job/message/" . $job->id;
      $fp = fsockopen("tcp://subtlefu.ge", 1337, $errno, $errstr, 30);
      if (!$cmd) return "no command given...";
      if (!$fp)  return "conn. refused";

      $response = "";
      fwrite($fp, $cmd);
      //while (!feof($fp)) {
      //        $response .= fgets($fp, 128);
      //}
      fclose($fp);
*/
      /* Send new messages to IRC Bot via Redis 
            $ircMsg = "[".$colors->purple."Job".$colors->nc."] ". $title." https://reddit.com".$href;
            $queue = Redis::get('TcpIRCQue');
            $queue = json_decode(($queue));
            if(is_array($queue)) $queue[] = $ircMsg;
                else $queue = [$ircMsg];
            Redis::set("TcpIRCQue", json_encode($queue));*/
    }


  }
}

/* IRC Notification Bot Connector Class */
class IRC
{
  public function colors()
  {  //this function will take color as text and then output the proper IRC color codes
    $this->nc = "0";
    $this->blue = "2";
    $this->green = "3";
    $this->lightred = "4";
    $this->red = "5";
    $this->purple = "6";
    $this->orange = "7";
    $this->yellow = "8";
    $this->lightgreen = "11";
    $this->lightblue = "12";
    $this->lightpurple = "13";
    $this->grey = "14";
    $this->lightgrey = "15";
    $this->darkwhite = "16";
    return $this;
  }
}
