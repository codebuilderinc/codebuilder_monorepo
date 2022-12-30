<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Phergie\Irc\Connection;
use BitcoinVietnam\BitcoinAverage;
use Illuminate\Support\Facades\Redis;

class runIRCBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:runIRCBot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to start phappr IRC bot.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function connection() {
        return $this->connection = new Connection(array(
                    // Required settings
                    'serverHostname' => 'codebuilder.org',
                    'username' => 'Phappr',
                    'realname' => 'Tyler Durden',
                    'nickname' => 'Phappr',
                    // Optional settings
                    'hostname' => 'phappr@phappr.net',
                     'serverport' => 6667,
                    // 'password' => 'password goes here if needed',
                     //'options' => array(
                      //   'transport' => 'ssl',
                         //'force-ipv4' => true,
                     //)
                ));
    }

    public function config() {

        $this->connection();
        $config = array(
            // Plugins to include for all connections
            'plugins' => array(
                // new \Vendor\Plugin\PluginName(array(
                // /* configuration goes here */
                // )),
            ),
            'connections' => array($this->connection)
        );
        return $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $config = $this->config();
        $connection = $this->connection;
        $bot = new \Phergie\Irc\Bot\React\Bot;
        $bot->setConfig($config);

        /* Begin client logic for bot handling of events */
        $client = new \Phergie\Irc\Client\React\Client();
        $client->on('irc.received', function($message, $write, $connection, $logger) {
            if ($message['command'] == 'DEBUG') return false;
            if ($message['command'] == 'NOTICE') return false;
            $GLOBALS["write"] = $write;
            $write->ircJoin("#root");

            //die(print_r($message));
            //$write->ircPrivmsg($message["params"]["receivers"], " USD");


            if ($message['command'] == 'PRIVMSG') {
                if(strstr("!b", $message["params"]["text"])) {
                    $usd = $this->convertUSD();
                    $write->ircPrivmsg($message["params"]["receivers"], '$'.$usd." USD");
                } 
                $nick = $message['nick'];
            }
            //$channel = $message['params']['channels'];
            //$nick = $message['nick'];
            //$write->ircPrivmsg($channel, 'Welcome ' . $nick . '!');
        });


        /* Periodic timer to check redis for new messages that bot should echo / relay */
        $client->addPeriodicTimer(5, function() {
            echo "hi bitch \n\n\n";
            global $write;

            $queue = Redis::get("TcpIRCQue");
            $queue = json_decode(($queue));
            if(is_array($queue)) {
                foreach($queue as $message) $write->ircPrivmsg("#root", $message);
            } 
            //reset message queue to null after sending them all
            Redis::set('TcpIRCQue', null);
        });

        $client->run($connection);

        /* Start up the bot */
        $bot->run();


    }

    public function convertUSD() {
        $client = new BitcoinAverage\Client($apiKey = "YjgwZmJmNmEwMTc0NDllY2FiY2ZlZmZkMjNiYTNhMzA", $apiSecret = "NmQ2MTI3MmM3MTIzNDZlOTlmZjA2YTFlZGRhZDRlM2ExYWRkNDJjZGQzN2E0NDEwODc0ZjQ4YjJmNWU3OTBhMg");
        $conversion = $client->getTickerPerSymbol("BTCUSD", "global");
        return $conversion->getLast();
    }
}
