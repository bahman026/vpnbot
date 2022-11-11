<?php

namespace App\Http\Controllers;

use App\Helper\Telegram;
use App\Lib\Test;
use App\Repositories\AccountingRepository;
use Faker\Extension\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Monolog\Handler\TelegramBotHandler;

class BotController extends Controller
{
    //


    public function index(Request $request)
    {
        $request->validate([
            'update_id' => 'required|int',
        ]);

        Log::info(json_encode($request->all()));
        $result = (Telegram::getCommand($request));
        if ($result == "/start" || $result == "/help") {
            $text = "🔺برای اطلاع از وضعیت سرویس خود لطفا نام اتصال و یا id اتصال خود را ارسال کنید

🔹برای پشتیبانی لطفا به آیدی های زیر پیام دهید.
@vpnxzn @vpn_fm_admin
آیدی کانال :
 @vpn2vray";

            Log::debug(Telegram::sendImage($request, fopen(Storage::path('image/image.png'), 'r'), $text));
            return 0;
        }

        $result = Telegram::getColumn($request);
        if (!$result) {
            $text = "🔺اطلاعاتی یافت نشد!
🔹برای پشتیبانی لطفا به آیدی های زیر پیام دهید.
@vpnxzn @vpn_fm_admin

آیدی کانال :
 @vpn2vray";

            Telegram::sendMessage($request, $text);
            return 0;
        }

        Telegram::sendMessage($request, $result);
        return 0;
    }
}
