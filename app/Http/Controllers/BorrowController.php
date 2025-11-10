<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomItem;
use App\Models\Borrow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BorrowController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Always filter by authenticated user for data isolation
        $items = RoomItem::where('user_id', $user->id)
            ->with('latestBorrow')
            ->orderBy('room_title')
            ->orderBy('full_set_id')
            ->orderBy('serial_number')
            ->get();
            
        // Get available items
        $availableItems = RoomItem::where('user_id', $user->id)
            ->where('status', 'Usable')
            ->whereDoesntHave('latestBorrow', function ($query) {
                $query->where('status', 'Borrowed');
            })
            ->get();
        
        // Group available items by room_title first, then by PC# similar to room-manage.blade.php
        $groupedAvailableItems = []; // Structure: [room_title][pcNumber] = [items]
        $individualAvailableItems = []; // Structure: [room_title] = [items]
        
        foreach($availableItems as $item) {
            $pcNumber = null;
            
            // Try to extract PC number from various sources
            if (preg_match('/(\d{3})$/', $item->barcode ?? '', $matches)) {
                $pcNumber = intval($matches[1]);
            } elseif (preg_match('/(\d{3})$/', $item->serial_number ?? '', $matches)) {
                $pcNumber = intval($matches[1]);
            } elseif ($item->full_set_id && preg_match('/(\d{3})$/', $item->full_set_id, $matches)) {
                $pcNumber = intval($matches[1]);
            } elseif (preg_match('/(\d{3})/', ($item->barcode ?? '') . ' ' . ($item->serial_number ?? ''), $matches)) {
                $pcNumber = intval($matches[1]);
            }
            
            $roomTitle = $item->room_title;
            
            if ($pcNumber !== null) {
                if (!isset($groupedAvailableItems[$roomTitle])) {
                    $groupedAvailableItems[$roomTitle] = [];
                }
                if (!isset($groupedAvailableItems[$roomTitle][$pcNumber])) {
                    $groupedAvailableItems[$roomTitle][$pcNumber] = [];
                }
                $groupedAvailableItems[$roomTitle][$pcNumber][] = $item;
            } else {
                if (!isset($individualAvailableItems[$roomTitle])) {
                    $individualAvailableItems[$roomTitle] = [];
                }
                $individualAvailableItems[$roomTitle][] = $item;
            }
        }
        
        // Sort PC numbers within each room
        foreach($groupedAvailableItems as $roomTitle => $pcGroups) {
            ksort($groupedAvailableItems[$roomTitle], SORT_NUMERIC);
        }
        
        // Sort rooms - extract numbers and sort numerically (ComLab 1, ComLab 2, etc.)
        uksort($groupedAvailableItems, function($a, $b) {
            // Extract numbers from room titles
            preg_match('/(\d+)/', $a, $matchesA);
            preg_match('/(\d+)/', $b, $matchesB);
            
            $numA = isset($matchesA[1]) ? intval($matchesA[1]) : 9999;
            $numB = isset($matchesB[1]) ? intval($matchesB[1]) : 9999;
            
            if ($numA !== $numB) {
                return $numA <=> $numB;
            }
            
            // If numbers are equal, sort alphabetically
            return strcmp($a, $b);
        });
        
        // Sort individual items rooms the same way
        uksort($individualAvailableItems, function($a, $b) {
            preg_match('/(\d+)/', $a, $matchesA);
            preg_match('/(\d+)/', $b, $matchesB);
            
            $numA = isset($matchesA[1]) ? intval($matchesA[1]) : 9999;
            $numB = isset($matchesB[1]) ? intval($matchesB[1]) : 9999;
            
            if ($numA !== $numB) {
                return $numA <=> $numB;
            }
            
            return strcmp($a, $b);
        });
            
        $activities = Borrow::with('roomItem')
            ->whereHas('roomItem', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereMonth('borrow_date', now()->month)
            ->whereYear('borrow_date', now()->year)
            ->orderByDesc('borrow_date')
            ->get();

        return view('borrow', [
            'items' => $items,
            'availableItems' => $availableItems,
            'groupedAvailableItems' => $groupedAvailableItems,
            'individualAvailableItems' => $individualAvailableItems,
            'activities' => $activities,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_item_id' => 'required|exists:room_items,id',
            'borrower_name' => 'required|string|max:255',
            'borrower_photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,jfif,webp,bmp,svg|max:5120', // Accept multiple image formats, max 5MB
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|in:BSIT,BSHM,BSBA,BSED,BEED',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'borrow_date' => 'required|date',
            'borrow_full_set' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        
        // Always verify the item belongs to the user
        $item = RoomItem::where('id', $request->room_item_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('borrower_photo')) {
            $photo = $request->file('borrower_photo');
            
            // Get original extension and normalize it (handle case-insensitive)
            $originalExtension = strtolower($photo->getClientOriginalExtension());
            
            // Normalize common extensions
            $extensionMap = [
                'jpeg' => 'jpg',
                'jfif' => 'jpg', // JFIF is essentially JPEG
            ];
            
            $normalizedExtension = $extensionMap[$originalExtension] ?? $originalExtension;
            
            // Generate unique filename
            $photoName = 'borrower_' . time() . '_' . Str::random(10) . '.' . $normalizedExtension;
            $photoPath = $photo->storeAs('borrower_photos', $photoName, 'public');
        }

        // Determine if borrowing full set
        $borrowFullSet = $request->has('borrow_full_set') && $request->borrow_full_set;
        $itemsToBorrow = collect([$item]);
        
        if ($borrowFullSet) {
            // Extract PC number from the selected item
            $pcNumber = null;
            if (preg_match('/(\d{3})$/', $item->barcode ?? '', $matches)) {
                $pcNumber = intval($matches[1]);
            } elseif (preg_match('/(\d{3})$/', $item->serial_number ?? '', $matches)) {
                $pcNumber = intval($matches[1]);
            } elseif ($item->full_set_id && preg_match('/(\d{3})$/', $item->full_set_id, $matches)) {
                $pcNumber = intval($matches[1]);
            } elseif (preg_match('/(\d{3})/', ($item->barcode ?? '') . ' ' . ($item->serial_number ?? ''), $matches)) {
                $pcNumber = intval($matches[1]);
            }
            
            if ($pcNumber !== null) {
                // Get all available items in the same room first
                $allAvailableItems = RoomItem::where('user_id', $user->id)
                    ->where('room_title', $item->room_title) // CRITICAL: Only same room
                    ->where('status', 'Usable')
                    ->whereDoesntHave('latestBorrow', function ($query) {
                        $query->where('status', 'Borrowed');
                    })
                    ->get();
                
                // Filter to get all items with the EXACT same PC number in the same room
                $fullSetItems = $allAvailableItems->filter(function($filterItem) use ($pcNumber, $item) {
                    // Must be in the same room
                    if ($filterItem->room_title !== $item->room_title) {
                        return false;
                    }
                    
                    $itemPcNumber = null;
                    
                    // Try to extract PC number from various sources (same logic as in index method)
                    if (preg_match('/(\d{3})$/', $filterItem->barcode ?? '', $matches)) {
                        $itemPcNumber = intval($matches[1]);
                    } elseif (preg_match('/(\d{3})$/', $filterItem->serial_number ?? '', $matches)) {
                        $itemPcNumber = intval($matches[1]);
                    } elseif ($filterItem->full_set_id && preg_match('/(\d{3})$/', $filterItem->full_set_id, $matches)) {
                        $itemPcNumber = intval($matches[1]);
                    } elseif (preg_match('/(\d{3})/', ($filterItem->barcode ?? '') . ' ' . ($filterItem->serial_number ?? ''), $matches)) {
                        $itemPcNumber = intval($matches[1]);
                    }
                    
                    // Must match the exact PC number
                    return $itemPcNumber === $pcNumber;
                });
                
                if ($fullSetItems->count() > 0) {
                    $itemsToBorrow = $fullSetItems;
                }
            } elseif ($item->is_full_item && $item->full_set_id) {
                // Fallback to full_set_id matching if PC number extraction fails
                // But still ensure same room
                $fullSetItems = RoomItem::where('user_id', $user->id)
                    ->where('room_title', $item->room_title) // CRITICAL: Only same room
                    ->where('full_set_id', $item->full_set_id)
                    ->where('status', 'Usable')
                    ->whereDoesntHave('latestBorrow', function ($query) {
                        $query->where('status', 'Borrowed');
                    })
                    ->get();
                
                if ($fullSetItems->count() > 0) {
                    $itemsToBorrow = $fullSetItems;
                }
            }
        }

        // Create borrow records for all items
        foreach ($itemsToBorrow as $borrowItem) {
            Borrow::create([
                'room_item_id' => $borrowItem->id,
                'borrower_name' => $request->borrower_name,
                'borrower_photo' => $photoPath,
                'position' => $request->position,
                'department' => $request->department,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'borrow_date' => $request->borrow_date,
                'status' => 'Borrowed',
            ]);

            // Update item status to "Borrowed"
            $borrowItem->status = 'Borrowed';
            $borrowItem->save();
        }

        $message = $borrowFullSet && $itemsToBorrow->count() > 1 
            ? "Full set ({$itemsToBorrow->count()} items) successfully borrowed!" 
            : 'Item successfully borrowed!';

        return redirect('/borrow')->with('success', $message);
    }

    public function returnItem($id)
    {
        $user = auth()->user();
        
        // Always find borrow record and verify user has access to the item
        $borrow = Borrow::with('roomItem')
            ->where('id', $id)
            ->whereHas('roomItem', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();
        
        $item = $borrow->roomItem;
        $returnFullSet = $item->is_full_item && $item->full_set_id;
        
        // If returning full set, return all items in the set
        if ($returnFullSet) {
            $fullSetBorrows = Borrow::with('roomItem')
                ->whereHas('roomItem', function($query) use ($user, $item) {
                    $query->where('user_id', $user->id)
                          ->where('full_set_id', $item->full_set_id);
                })
                ->where('status', 'Borrowed')
                ->where('borrower_name', $borrow->borrower_name)
                ->whereDate('borrow_date', $borrow->borrow_date->format('Y-m-d'))
                ->get();
            
            foreach ($fullSetBorrows as $fullSetBorrow) {
                $fullSetBorrow->status = 'Returned';
                $fullSetBorrow->return_date = now();
                $fullSetBorrow->save();
                
                $fullSetItem = $fullSetBorrow->roomItem;
                $fullSetItem->status = 'Usable';
                $fullSetItem->save();
            }
            
            $message = "Full set ({$fullSetBorrows->count()} items) successfully returned!";
        } else {
            $borrow->status = 'Returned';
            $borrow->return_date = now();
            $borrow->save();

            // Restore item status to "Usable"
            $item->status = 'Usable';
            $item->save();
            
            $message = 'Item successfully returned!';
        }

        return redirect('/borrow')->with('success', $message);
    }
}
