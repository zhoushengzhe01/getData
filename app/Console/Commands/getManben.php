<?php

namespace App\Console\Commands;

use App\Http\Controllers\ManbenController;
use Illuminate\Console\Command;

class getManben extends Command
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

        $type = $this->ask("input type ? \n \t data : Get manben data \n \t user : Get user data ");

        if($type=='data')
        {
            ManbenController::getData();
        }
        
        if($type=='user')
        {
            ManbenController::getUser();
        }

        //echo $userId = $this->argument('type');
        //获取漫画
        //

    }
}
