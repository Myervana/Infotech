<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomItem;
use App\Models\Borrow;
use App\Models\BorrowExtension;
use App\Models\MaintenanceNote;
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
            ->with(['latestBorrow.extensions' => function($query) {
                $query->orderBy('extended_at', 'desc');
            }])
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
            ->get()
            ->groupBy(function($borrow) {
                // Group by borrower name, date, and location
                return $borrow->borrower_name . '|' . $borrow->borrow_date->format('Y-m-d H:i') . '|' . ($borrow->latitude ?? '') . '|' . ($borrow->longitude ?? '');
            })
            ->map(function($group) {
                // Return the first item with a count of all items
                $first = $group->first();
                $first->items_count = $group->count();
                $first->all_items = $group->map(function($item) {
                    return [
                        'id' => $item->id,
                        'serial_number' => $item->roomItem->serial_number ?? 'N/A',
                        'device_category' => $item->roomItem->device_category ?? 'N/A',
                        'room_title' => $item->roomItem->room_title ?? 'N/A',
                    ];
                })->values();
                return $first;
            })
            ->values();

        // Prepare borrower groups with JSON-ready data
        $borrowerGroupsData = [];
        foreach($items as $item) {
            if ($item->latestBorrow && $item->latestBorrow->status === 'Borrowed') {
                $borrowerName = $item->latestBorrow->borrower_name;
                
                if (!isset($borrowerGroupsData[$borrowerName])) {
                    $borrowerGroupsData[$borrowerName] = [];
                }
                
                $borrowerGroupsData[$borrowerName][] = [
                    'id' => $item->id,
                    'device_category' => $item->device_category ?? '',
                    'serial_number' => $item->serial_number ?? '',
                    'room_title' => $item->room_title ?? '',
                    'description' => $item->description ?? 'N/A',
                    'latest_borrow' => [
                        'id' => $item->latestBorrow->id,
                        'status' => $item->latestBorrow->status,
                    ]
                ];
            }
        }

        // Get overdue items for notifications
        $overdueItems = Borrow::with('roomItem')
            ->whereHas('roomItem', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->overdue()
            ->get()
            ->groupBy('borrower_name');

        return view('borrow', [
            'items' => $items,
            'availableItems' => $availableItems,
            'groupedAvailableItems' => $groupedAvailableItems,
            'individualAvailableItems' => $individualAvailableItems,
            'activities' => $activities,
            'borrowerGroupsData' => $borrowerGroupsData,
            'overdueItems' => $overdueItems,
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
            'reason' => 'required|string|max:1000',
            'borrow_duration' => 'required|in:1_day,2_days,3_days,4_days,1_week',
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
            // First, try to use the PC number from the request (most reliable - from frontend selection)
            $pcNumber = null;
            if ($request->has('selected_pc_number') && !empty($request->selected_pc_number)) {
                $pcNumber = intval($request->selected_pc_number);
            }
            
            // If not provided, extract PC number from the selected item - use a helper function for consistency
            if ($pcNumber === null || $pcNumber === 0) {
                $extractPcNumber = function($item) {
                    // Priority 1: Extract from barcode (most reliable)
                    if (!empty($item->barcode) && preg_match('/(\d{3})$/', $item->barcode, $matches)) {
                        return intval($matches[1]);
                    }
                    // Priority 2: Extract from serial number
                    if (!empty($item->serial_number) && preg_match('/(\d{3})$/', $item->serial_number, $matches)) {
                        return intval($matches[1]);
                    }
                    // Priority 3: Extract from full_set_id
                    if (!empty($item->full_set_id) && preg_match('/(\d{3})$/', $item->full_set_id, $matches)) {
                        return intval($matches[1]);
                    }
                    return null;
                };
                
                $pcNumber = $extractPcNumber($item);
            }
            
            // Also verify room title matches
            $selectedRoomTitle = $request->has('selected_room_title') ? $request->selected_room_title : $item->room_title;
            
            if ($pcNumber !== null && $pcNumber > 0) {
                // Get all available items in the same room
                $allAvailableItems = RoomItem::where('user_id', $user->id)
                    ->where('room_title', $selectedRoomTitle) // CRITICAL: Only same room (use selected room)
                    ->where('status', 'Usable')
                    ->whereDoesntHave('latestBorrow', function ($query) {
                        $query->where('status', 'Borrowed');
                    })
                    ->get();
                
                // Extract PC number helper function
                $extractPcNumber = function($item) {
                    // Priority 1: Extract from barcode (most reliable)
                    if (!empty($item->barcode) && preg_match('/(\d{3})$/', $item->barcode, $matches)) {
                        return intval($matches[1]);
                    }
                    // Priority 2: Extract from serial number
                    if (!empty($item->serial_number) && preg_match('/(\d{3})$/', $item->serial_number, $matches)) {
                        return intval($matches[1]);
                    }
                    // Priority 3: Extract from full_set_id
                    if (!empty($item->full_set_id) && preg_match('/(\d{3})$/', $item->full_set_id, $matches)) {
                        return intval($matches[1]);
                    }
                    return null;
                };
                
                // Filter to get all items with the EXACT same PC number in the same room
                $fullSetItems = $allAvailableItems->filter(function($filterItem) use ($pcNumber, $selectedRoomTitle, $extractPcNumber) {
                    // Must be in the same room
                    if ($filterItem->room_title !== $selectedRoomTitle) {
                        return false;
                    }
                    
                    // Extract PC number from the filter item using the same logic
                    $itemPcNumber = $extractPcNumber($filterItem);
                    
                    // Must match the exact PC number (strict comparison)
                    if ($itemPcNumber === null || $itemPcNumber !== $pcNumber) {
                        return false;
                    }
                    
                    return true;
                });
                
                if ($fullSetItems->count() > 0) {
                    $itemsToBorrow = $fullSetItems;
                } else {
                    // If no items found with PC number matching, don't borrow full set
                    // Just borrow the single item
                    $itemsToBorrow = collect([$item]);
                }
            } else {
                // If PC number cannot be extracted, don't attempt full set borrowing
                // Just borrow the single item
                $itemsToBorrow = collect([$item]);
            }
        }

        // Calculate due date based on borrow date and duration
        $borrowDate = Carbon::parse($request->borrow_date);
        $daysToAdd = 0;
        switch($request->borrow_duration) {
            case '1_day':
                $daysToAdd = 1;
                break;
            case '2_days':
                $daysToAdd = 2;
                break;
            case '3_days':
                $daysToAdd = 3;
                break;
            case '4_days':
                $daysToAdd = 4;
                break;
            case '1_week':
                $daysToAdd = 7;
                break;
        }
        $dueDate = $borrowDate->copy()->addDays($daysToAdd);

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
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'reason' => $request->reason,
                'borrow_duration' => $request->borrow_duration,
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

    public function returnBulk(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'items' => 'required|array',
            'items.*.status' => 'required|in:Usable,Unusable',
            'items.*.room_item_id' => 'required|exists:room_items,id',
            'items.*.reason' => 'nullable|string|max:1000',
        ]);

        $returnedCount = 0;
        $unusableCount = 0;

        foreach ($request->items as $borrowId => $itemData) {
            // Find borrow record and verify user has access
            $borrow = Borrow::with('roomItem')
                ->where('id', $borrowId)
                ->whereHas('roomItem', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('status', 'Borrowed')
                ->first();

            if (!$borrow) {
                continue;
            }

            $item = $borrow->roomItem;
            
            // Update borrow record
            $borrow->status = 'Returned';
            $borrow->return_date = now();
            $borrow->save();

            // Update item status
            $item->status = $itemData['status'];
            $item->save();

            // If item is marked as Unusable, create/update maintenance note
            if ($itemData['status'] === 'Unusable' && !empty($itemData['reason'])) {
                MaintenanceNote::updateOrCreate(
                    ['room_item_id' => $item->id],
                    [
                        'note' => 'Returned as Unusable',
                        'reason' => $itemData['reason'],
                        'fullset_id' => $item->full_set_id,
                    ]
                );
                $unusableCount++;
            }

            $returnedCount++;
        }

        $message = "Successfully returned {$returnedCount} item(s)";
        if ($unusableCount > 0) {
            $message .= ". {$unusableCount} item(s) marked as Unusable and added to maintenance tracking.";
        }

        return redirect('/borrow')->with('success', $message);
    }

    public function extend(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'borrow_id' => 'required|exists:borrows,id',
            'extend_duration' => 'required|in:1_day,2_days,3_days,4_days,1_week',
            'extend_reason' => 'required|string|max:1000',
        ]);

        // Find borrow record and verify user has access
        $borrow = Borrow::with('roomItem')
            ->where('id', $request->borrow_id)
            ->whereHas('roomItem', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'Borrowed')
            ->firstOrFail();

        // Calculate days to add
        $daysToAdd = match($request->extend_duration) {
            '1_day' => 1,
            '2_days' => 2,
            '3_days' => 3,
            '4_days' => 4,
            '1_week' => 7,
            default => 0,
        };

        // Get current due date or use borrow date as base
        $currentDueDate = $borrow->due_date ?? $borrow->borrow_date;
        $previousDueDate = $borrow->due_date;
        
        // Calculate new due date
        $newDueDate = Carbon::parse($currentDueDate)->addDays($daysToAdd);

        // Create extension record
        $extension = BorrowExtension::create([
            'borrow_id' => $borrow->id,
            'extension_duration' => $request->extend_duration,
            'days_added' => $daysToAdd,
            'reason' => $request->extend_reason,
            'previous_due_date' => $previousDueDate,
            'new_due_date' => $newDueDate,
            'extended_at' => now(),
        ]);

        // Update borrow record
        $borrow->due_date = $newDueDate;
        $borrow->borrow_duration = $request->extend_duration;
        
        // Append extension reason to existing reason if any
        $extensionNote = "\n\n[Extended on " . now()->format('Y-m-d H:i') . "] Reason: " . $request->extend_reason;
        $borrow->reason = ($borrow->reason ?? '') . $extensionNote;
        
        $borrow->save();

        $item = $borrow->roomItem;
        $extensionCount = $borrow->extensions()->count();
        $message = "Borrow period extended successfully! New due date: " . $newDueDate->format('M d, Y (g:i A)');
        if ($extensionCount > 1) {
            $message .= " (Total extensions: {$extensionCount})";
        }

        return redirect('/borrow')->with('success', $message);
    }
}
