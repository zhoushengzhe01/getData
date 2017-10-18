<?php

namespace App\Console\Commands;

use App\Http\Controllers\YaoqiController;
use Illuminate\Console\Command;

class Yaoqi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yaoqi';

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
        $message = "Please enter the data you want ? \n\n";

        $message .= "\t book: Get comic data. \n\n";

        $message .= "\t comment: Get comment data. \n\n";

        $message .= "\t update: Updating manga data. \n\n";
        
        $message .= "\t user: User system acquisition. \n\n";
        

        $type = $this->ask($message);

        //Get comic data.
        if($type=="book")
        {
            YaoqiController::getBookData();
        }

        //Get comment data.
        if($type=="comment")
        {
            YaoqiController::getCommentData();
        }

        if($type=='update')
        {
            YaoqiController::getUpdateData();
        }

        //Updating manga data

        //User system acquisition.
        if($type=="user")
        {
            YaoqiController::getUserData();
        }


    }
}
