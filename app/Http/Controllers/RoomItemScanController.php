<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomItem;

class RoomItemScanController extends Controller
{
    /**
     * Display the barcode scan form page
     */
    public function index()
    {
        return view('scan-barcode');
    }

    /**
     * Process barcode scan submission and show matching item(s)
     */
    public function search(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $user = auth()->user();
        $barcode = $request->input('barcode');

        // Find all matching room items by barcode with user isolation
        $itemsQuery = RoomItem::where('barcode', $barcode);
        
        // Apply user-based filtering for new users
        if ($user->is_new_user) {
            $itemsQuery->where('user_id', $user->id);
        }
        
        $items = $itemsQuery->get();

        if ($items->isEmpty()) {
            return view('scan-barcode', [
                'notFound' => true,
                'barcode' => $barcode
            ]);
        }

        return view('scan-barcode', [
            'items' => $items,
            'barcode' => $barcode,
            'scanned' => true
        ]);
    }

    /**
     * API endpoint for camera barcode scanning
     */
    public function apiSearch(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $user = auth()->user();
        $barcode = $request->input('barcode');

        // Find all matching room items by barcode with user isolation
        $itemsQuery = RoomItem::where('barcode', $barcode);
        
        // Apply user-based filtering for new users
        if ($user->is_new_user) {
            $itemsQuery->where('user_id', $user->id);
        }
        
        $items = $itemsQuery->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No item found for this barcode',
                'barcode' => $barcode
            ]);
        }

        // Transform items data for JSON response
        $itemsData = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'room_title' => $item->room_title,
                'device_category' => $item->device_category,
                'device_type' => $item->device_type ?? 'Unspecified',
                'brand' => $item->brand ?? 'N/A',
                'model' => $item->model ?? 'N/A',
                'serial_number' => $item->serial_number,
                'description' => $item->description,
                'status' => $item->status,
                'barcode' => $item->barcode,
                'has_photo' => !empty($item->photo)
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $itemsData,
            'barcode' => $barcode
        ]);
    }
}