<?php

namespace App\Console\Commands;

use App\Http\Controllers\XiaoshuoController;
use Illuminate\Console\Command;

class Xiaoshuo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xiaoshuo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $message = "Enter the collection site ? \n";

        $message .= "\t 1： Collect www.biqudu.com \n";

        $message .= "\t 1： Collect www.biqudu.com \n";

        $message .= "\t 1： Collect www.biqudu.com \n";
        
        $message .= "\t 1： Collect www.biqudu.com \n";
        

        $type = $this->ask($message);

        //Get comic data.
        if($type=="1")
        {
            XiaoshuoController::getBookData('biqudu_com');
        }
    }
}
