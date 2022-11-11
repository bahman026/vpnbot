<?php

namespace App\Http\Controllers;

use App\Helper\Telegram;
use App\Lib\Test;
use App\Repositories\AccountingRepository;
use Faker\Extension\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\TelegramBotHandler;
class BotController extends Controller
{
    //


    public function index(Request $request){
        $request->validate([
            'update_id' => 'required|int',
        ]);
        Log::debug(Telegram::getCommand($request));
    }
}
