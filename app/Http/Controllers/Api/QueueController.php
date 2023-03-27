<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Queue;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
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

            // $data = [
            //     'current' => array_key_exists('current', $queues) ? $queues['current'][0] : [],
            //     'next' => array_key_exists('next', $queues) ? $queues['next'][0] : null,
            //     'upcoming' => array_key_exists('upcoming', $queues) ? $queues['upcoming'] : null,
            // ];

            // id, queue, status
            $response = [
                'status' => 'success',
                'current' => array_key_exists('current', $queues) ? $queues['current'][0] : (object) null,
                'next' => array_key_exists('next', $queues) ? $queues['next'][0] : (object) null,
                'upcoming' => array_key_exists('upcoming', $queues) ? $queues['upcoming'] : (object) null,
            ];

            return response()->json($response, 200);
            
        } catch (Exception $e) {
            Log::error($e);

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response, 500);
        }
    }

    public function queueComplete(Request $request)
    {
        DB::beginTransaction();
        try{
            $queue = Queue::where('id', $request->id);
            $no = $queue->first()->queue_no;
            $queue->update(['status' => 'done']);

            // send whatsapp to next queue

            $response = [
                'status' => 'success',
                'message' => "No. giliran $no selesai!"
            ];

            DB::commit();
            return response()->json($response, 200);

        } catch (Exception $e) { 
            DB::rollBack();
            Log::error($e);

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response, 500);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required|in:done,current,next,upcoming'
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
            return response()->json($response, 200);

        } catch (Exception $e) { 
            DB::rollBack();
            Log::error($e);

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response, 500);
        }
    }

    public function getQueue(Request $request)
    {
        $request->validate([
            'tel_no' => 'required|digits_between:10,11'
        ]);

        DB::beginTransaction();
        try {
            $next_queue = Queue::whereDate('created_at', Carbon::today())
                                ->where('status', 'next')
                                ->first();

            $current_queue = Queue::whereDate('created_at', Carbon::today())
                                    ->where('status', 'current')
                                    ->first();
                            
            // generate queue no
            if ($next_queue) {
                $upcoming_queue = Queue::orderBy('id', 'desc')
                                        ->whereDate('created_at', Carbon::today())
                                        ->where('status', 'upcoming')
                                        ->first();

                if ($upcoming_queue) {
                    $new_queue = sprintf("%03d", $upcoming_queue->queue_no + 1);
                } else {
                    $new_queue = sprintf("%03d", $next_queue->queue_no + 1);
                }

                $status = 'upcoming';
            } else {
                $all_queue = Queue::orderBy('id', 'desc')
                                    ->whereDate('created_at', Carbon::today())
                                    ->first();

                $new_queue = $all_queue ? sprintf("%03d", $all_queue->queue_no + 1) : '001';
                $status = 'next';
            }

            // create queue
            Queue::create([
                'tel_no' => $request->tel_no,
                'queue_no' => $new_queue,
                'status' => $status
            ]);

            // send whatsapp
            $sid = env("TWILIO_SID");
            $token  = env("TWILIO_AUTH_TOKEN");

            $sender = env('TWILIO_WHATSAPP_NUMBER');
            $recipient = '+60103600383';
            $message = "Selamat datang! No. giliran anda adalah *$new_queue*. Sila tunggu sebentar.";

            $message = 'Your appointment is coming up on July 21 at 3PM';

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
                'queue' => $new_queue,
                'current' => $current_queue ? $current_queue->queue_no : null
            ];

            DB::commit();
            return response()->json($response, 200);

        } catch (Exception $e) {
            Log::error($e);
            DB::rollBack();

            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            return response()->json($response, 500);
        }
    }

    public function storeStatus(Request $request)
    {
        info('here');
    }
}
