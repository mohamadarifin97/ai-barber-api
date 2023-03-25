<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Queue;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class QueueController extends BaseController
{
    public function getQueueList(Request $request)
    {
        try {
            $queues = Queue::whereIn('status', ['upcoming', 'current', 'next'])
                            ->whereDate('created_at', Carbon::today())
                            ->limit(10)
                            ->get(['id', 'tel_no', 'queue_no', 'status'])
                            ->map(function ($queue) {
                                return [
                                    'id' => $queue->id,
                                    'phone no' => $queue->tel_no,
                                    'queue' => $queue->queue_no,
                                    'status' => $queue->status
                                ];
                            })
                            ->groupBy('status')
                            ->toArray();

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

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required'
        ]);

        DB::beginTransaction();
        try{
            $queue = Queue::where('id', $request->id);
            $no = $queue->first()->queue_no;
            $queue->update(['status' => $request->status]);

            // send whatsapp to next queue

            $response = [
                'status' => 'success',
                'message' => "Kemaskini status no. giliran $no berjaya!"
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
                'queue' => $new_queue
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
