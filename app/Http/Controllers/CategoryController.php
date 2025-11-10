<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomItem;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display categories with related room items and unique room titles.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $items = RoomItem::all();
        $rooms = RoomItem::select('room_title')->distinct()->orderBy('room_title')->get();

        // RoomItem-based unique categories
        $roomItemCategories = RoomItem::select('device_category')
            ->whereNotNull('device_category')
            ->distinct()
            ->pluck('device_category');

        $itemCounts = RoomItem::selectRaw('device_category, COUNT(*) as total')
            ->groupBy('device_category')
            ->pluck('total', 'device_category');

        return view('categories', compact(
            'categories',
            'items',
            'rooms',
            'roomItemCategories',
            'itemCounts'
        ));
    }

    /**
     * Get items by identifier (room or category) with totals for rooms.
     */
    public function getItemsByIdentifier($identifier)
    {
        $type = request()->get('type', 'category'); // Default to category if not specified
        
        if ($type === 'room') {
            // Get items by room
            $items = RoomItem::where('room_title', $identifier)
                ->orderBy('device_category')
                ->orderBy('serial_number')
                ->get();
            
            // Calculate category totals for the room
            $categoryTotals = $items->groupBy('device_category')
                ->map(function ($group) {
                    return $group->count();
                })
                ->toArray();
            
            return response()->json([
                'items' => $items,
                'categoryTotals' => $categoryTotals,
                'type' => 'room'
            ]);
        } else {
            // Get items by category (existing functionality)
            $items = RoomItem::where('device_category', $identifier)
                ->orderBy('room_title')
                ->orderBy('serial_number')
                ->get();
            
            return response()->json([
                'items' => $items,
                'type' => 'category'
            ]);
        }
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category added successfully!');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    /**
     * Delete a category and optionally handle its associations.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Optional logic: If needed to detach from RoomItems
        // RoomItem::where('device_category', $category->name)->update(['device_category' => null]);

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}