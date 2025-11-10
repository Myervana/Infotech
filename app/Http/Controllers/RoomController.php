<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'room_title' => 'required|string|max:255',
        ]);

        try {
            // Insert the new room
            DB::table('room_items')->insert([
                'room_title' => $request->room_title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add room: ' . $e->getMessage()
            ], 500);
        }
    }
}
