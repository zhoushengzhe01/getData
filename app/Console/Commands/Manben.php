<?php

namespace App\Console\Commands;

use App\Http\Controllers\ManbenController;
use Illuminate\Console\Command;

class Manben extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manben';

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
            ManbenController::getBookData();
        }

        //Get comment data.
        if($type=="comment")
        {
            ManbenController::getCommentData();
        }

        if($type=='update')
        {
            ManbenController::getUpdateData();
        }

        //Updating manga data

        //User system acquisition.
        if($type=="user")
        {
            ManbenController::getUserData();
        }


    }
}
