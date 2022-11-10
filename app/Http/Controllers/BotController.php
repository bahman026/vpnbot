<?php

namespace App\Http\Controllers;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    //

    public function index(Request $request){
       Log::debug(json_encode($request).PHP_EOL);
    }
}
