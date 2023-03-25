<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController;
use App\Models\Queue;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class QueueController extends BaseController
{
    public function getQueueList(Request $request)
    {
        try {
            $queues = Queue::where('status', 'WAITING')
                            ->whereDate('created_at', Carbon::today())
                            ->limit(10)
                            ->get(['id', 'tel_no', 'queue_no']);
            // id, queue, status
            $response = [
                'status' => 'success',
                'data' => $queues
            ];

            return response()->json($response);
            
        } catch (Exception $e) {
            Log::error($e);

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response);
        }
    }

    public function queueComplete(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $queue = Queue::where('id', $request->id);
            $no = $queue->first()->queue_no;
            $queue->update(['status' => 'DONE']);

            // send whatsapp to next queue

            $response = [
                'status' => 'success',
                'message' => "No. giliran $no selesai!"
            ];

            DB::commit();
            return response()->json($response);

        } catch (Exception $e) { 
            DB::rollBack();
            Log::error($e);

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response);
        }
    }

    public function getQueue(Request $request)
    {
        $request->validate([
            'tel_no' => 'required|digits_between:10,11'
        ]);

        DB::beginTransaction();
        try {
            $queue = Queue::orderBy('id', 'desc')
                            ->whereDate('created_at', Carbon::today())
                            ->where('status', 'next')
                            ->first();
            
            // generate queue no
            if ($queue) {
                $new_queue = sprintf("%03d", $queue->queue_no + 1);
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
                'queue no' => $new_queue
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
