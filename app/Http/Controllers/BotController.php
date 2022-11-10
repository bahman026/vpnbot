<?php

namespace App\Http\Controllers;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    //

    public function index(Request $request){
       Log::debug($request->getContent().PHP_EOL);
    }
}
