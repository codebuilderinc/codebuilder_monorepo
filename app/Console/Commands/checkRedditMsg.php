<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \CodeWizz\RedditAPI\RedditAPI;
use App\Notifications\NewJobMessage;
use Notification;
use App\User;
use App\Message;

class checkRedditMsg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkRedditMsg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Reddit for new messages and send notifications.';

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
        $reddit = new RedditAPI("taodev91", "8fJUWB3exETiW78", "z6U6lida0GFEzacynDcllw", "a_7heyaSC04o9HoYK_5gTweI30tYmg", "https://www.reddit.com", "https://oauth.reddit.com", "STD");
        $messages = $reddit->getUnread();

        print_r($messages);
        /* If new messages then send out web push notification for phone alert */
        if(count($messages->data->children)) {
            foreach($messages->data->children as $message) {
                $msg = $message->data;
                print_r($msg);
                $exists = Message::where("reddit_id", "=", $msg->id)->first();
                if(!$exists) {

                    /* Insert Link into Database */
                    $message = new Message;
                    $message->reddit_id = $msg->id;
                    $message->body_html = $msg->body_html;
                    $message->body = $msg->body;
                    $message->author = $msg->author;
                    $message->save();

                    $cmd = "ASLkjda*s@ !yQgCRtXrhmRNbyRMiV:subtlefu.ge <b>Reddit Message from: ".$msg->author." (https://www.reddit.com/message/messages/".$msg->id.")</b><br><table width='100%' border='0'><tr><td><img width='25' height='25' src='https://subtlefu.ge/_matrix/media/r0/download/subtlefu.ge/pxhTywhmnTENTwwXxNKXEAPs'></td><td><pre>".$msg->body."</pre></td></tr></table>";
                    $fp = fsockopen("tcp://subtlefu.ge", 1337, $errno, $errstr, 30);
                    if(!$cmd) return "no command given...";
                    if(!$fp)  return "conn. refused";
                            
                    $response = "";
                    fwrite($fp, $cmd);
                    //while (!feof($fp)) {
                    //        $response .= fgets($fp, 128);
                    //}
                    fclose($fp);

                    /* Send out notifications for web browser / android */
                    Notification::send(User::all(),new NewJobMessage([
                        "title"=>"New Reddit Message!",
                        "body" => "Check Reddit for message.",
                        "icon" => "https://codebuilder.us/images/mandala4_75.png"
                    ]));
                }

            }


        }

    }
}
