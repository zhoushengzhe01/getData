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
    protected $signature = 'get:manben';

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
        ManbenController::get();
        //new Crawler('<html></html>');
    }
}
