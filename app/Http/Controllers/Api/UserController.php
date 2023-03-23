<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use App\Notifications\TelegramNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class UserController extends Controller
{
    public function getQueue(Request $request)
    {
        $request->validate([
            'tel_no' => 'required|digits_between:10,11'
        ]);

        DB::beginTransaction();
        try {
            $queue = Queue::orderBy('id', 'desc')
                            ->whereDate('created_at', Carbon::today())
                            ->where('status', 'WAITING')
                            ->first();
            
            // generate queue no
            if ($queue) {
                $new_queue = sprintf("%03d", $queue->no + 1);
            } else {
                $new_queue = '001';
            }

            // create queue
            Queue::create([
                'tel_no' => $request->tel_no,
                'queue_no' => $new_queue,
                'status' => 'WAITING'
            ]);

            // send whatsapp
            $sid = env("TWILIO_SID");
            $token  = env("TWILIO_AUTH_TOKEN");

            $sender = env('TWILIO_WHATSAPP_NUMBER');
            $recipient = '+60103600383';
            $message = "Your turn is $new_queue";

            $twilio = new Client($sid, $token);
            $message = $twilio->messages
            ->create("whatsapp:$recipient", // to
                array(
                    "from" => "whatsapp:$sender",
                    "body" => $message
                )
            );

            // // send telegram
            // $user = User::find(4);
            // $user->notify(new TelegramNotification(['text' => "Welcome to the application"]));

            $response = [
                'status' => 'success',
                'data' => $new_queue
            ];

            DB::commit();
            return response()->json($response);

        } catch (Exception $e) {
            Log::error($e);
            DB::rollBack();

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response);
        }
    }
}
