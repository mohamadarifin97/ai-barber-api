<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Queue;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends BaseController
{
    public function getQueue(Request $request)
    {
        try {
            $queues = Queue::where('status', 'WAITING')
                            ->whereDate('created_at', Carbon::today())
                            ->limit(10)
                            ->get(['id', 'no']);
            
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

    public function nextQueue(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $queue = Queue::where('id', $request->id);
            $no = $queue->first()->no;
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
}
