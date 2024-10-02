<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NatPunchingController extends Controller
{

    const HEARTBEAT_INTERVAL = 10; // Time (in seconds) before a client is considered inactive

    /**
     * Register a client with IP and port details.
     * Store the client info in a temporary cache.
     */
    public function register(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'client_id' => 'required|string',
            'public_ip' => 'required|ip',
            'public_port' => 'required|integer',
        ]);

        // Store client info in cache and set the initial heartbeat timestamp
        Cache::put($request->client_id, [
            'public_ip' => $request->public_ip,
            'public_port' => $request->public_port,
            'last_heartbeat' => now()->timestamp,
        ], now()->addMinutes(10)); // Cache for 10 minutes

        return response()->json([
            'message' => 'Client registered successfully',
            'client_id' => $request->client_id
        ]);
    }

    /**
     * Receive a heartbeat from the client to keep it alive.
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
        ]);

        $client = Cache::get($request->client_id);

        if ($client) {
            // Update the last heartbeat timestamp
            $client['last_heartbeat'] = now()->timestamp;
            Cache::put($request->client_id, $client, now()->addMinutes(10));

            return response()->json(['message' => 'Heartbeat received']);
        }

        return response()->json(['error' => 'Client not found'], 404);
    }

    /**
     * Get peer information (public IP and port) for the other client.
     */
    public function getPeerInfo($client_id)
    {
        $clientInfo = Cache::get($client_id);

        if (!$clientInfo) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        return response()->json($clientInfo);
    }

    /**
     * Periodically check for clients that have not sent a heartbeat in the last HEARTBEAT_INTERVAL seconds.
     */
    public function checkInactiveClients()
    {
        $clients = Cache::getMultiple(['A', 'B']); // Assuming A and B for simplicity

        foreach ($clients as $client_id => $client) {
            if ($client && (now()->timestamp - $client['last_heartbeat']) > self::HEARTBEAT_INTERVAL) {
                // Remove the client if they are inactive
                Cache::forget($client_id);
                echo "Client $client_id removed due to inactivity\n";
            }
        }
    }

}
