<?php

namespace App\Console\Commands;

use App\Http\Controllers\TenxunController;
use Illuminate\Console\Command;

class Tenxun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenxun';

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

        $message .= "\t email: Get valid email. \n\n";

        $message .= "\t qq: Get valid qq. \n\n";
        

        $type = $this->ask($message);

        //Get comic data.
        if($type=="email")
        {
            TenxunController::getEmailData();
        }
        
        //Get comment data.
        if($type=="qq")
        {
            TenxunController::getQQData();
        }

    }
}
