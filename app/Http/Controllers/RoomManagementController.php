<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\RoomItem;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class RoomManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Always filter by authenticated user for data isolation
        $items = RoomItem::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Process items to add photo URLs
        $items->transform(function ($item) {
            if ($item->photo) {
                $item->photo_url = Storage::url($item->photo);
            }
            return $item;
        });
        return view('room-manage', compact('user', 'items'));
    }

    public function store(Request $request)
    {
        // Check if this is a full set or single item
        if ($request->device_category === 'Full Set') {
            return $this->storeFullSet($request);
        } else {
            return $this->storeSingleItem($request);
        }
    }

    /**
     * Handles storing and updating single items.
     */
    protected function storeSingleItem(Request $request, $oldPhotoPath = null, $oldStatus = null, $oldFullSetId = null, $oldIsFullSetItem = false, $oldSerialNumber = null, $oldBarcode = null)
    {
        $isUpdate = $request->has('_method') && $request->_method === 'PUT';
        // Use `required_without` to ensure at least one room title field is filled
        $rules = [
            'device_category' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1|max:100',
            'room_title' => 'required_without:custom_room_title|string|nullable',
            'custom_room_title' => 'required_without:room_title|string|nullable|max:255',
        ];
        // Updated validation to accept any image format
        if (!$isUpdate || $request->hasFile('photo')) {
            $rules['photo'] = 'nullable|image|max:2048';
        }

        $validatedData = $request->validate($rules);
        // Determine the final room title from the request
        $roomTitle = $request->filled('custom_room_title') ?
        $request->custom_room_title : $request->room_title;

        $photoPath = $oldPhotoPath;
        
        // Handle photo upload - only if not already handled in update method
        if ($request->hasFile('photo') && !$isUpdate) {
            if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                Storage::delete($oldPhotoPath);
            }
            $photoPath = $request->file('photo')->store('public/photos');
        }

        // Generate barcode based on room and device category
        // For updates, only regenerate if room title or category changed, otherwise keep existing barcode
        $barcode = $oldBarcode;
        if (!$isUpdate || !$oldBarcode) {
            $barcode = $this->generateBarcode($roomTitle, $validatedData['device_category']);
        } else {
            // Check if room title or category changed, then regenerate barcode
            $currentItem = RoomItem::find($request->route('item'));
            if ($currentItem && ($currentItem->room_title !== $roomTitle || $currentItem->device_category !== $validatedData['device_category'])) {
                $barcode = $this->generateBarcode($roomTitle, $validatedData['device_category']);
            }
        }
        
        // Generate serial number (use existing one for updates, generate new for creates)
        $serialNumber = $isUpdate ?
        $oldSerialNumber : $this->generateSerialNumber();

        $itemData = [
            'user_id' => auth()->id(),
            'room_title' => $roomTitle,
            'device_category' => $validatedData['device_category'],
            'device_type' => $this->getDeviceType($validatedData['device_category']),
            'brand' => $validatedData['brand'],
            'model' => $validatedData['model'],
            'serial_number' => $serialNumber,
            'barcode' => $barcode,
            'photo' => $photoPath,
            'description' => $validatedData['description'],
            'quantity' => $validatedData['quantity'],
            'status' => $oldStatus ?? $request->status,
            'full_set_id' => $oldFullSetId,
            'is_full_set_item' => $oldIsFullSetItem,
        ];
        
        if ($isUpdate) {
            RoomItem::where('id', $request->route('item'))->update($itemData);
        } else {
            RoomItem::create($itemData);
        }

        return redirect()->route('room-manage')->with('success', 'Item has been saved!');
    }

    /**
     * Handles storing and updating full sets.
     */
    protected function storeFullSet(Request $request, $fullSetId = null)
    {
        // Use `required_without` to ensure at least one room title field is filled
        $rules = [
            'device_category' => 'required|string',
            'fullset_brand' => 'nullable|string',
            'fullset_model' => 'nullable|string',
            'fullset_categories' => 'required|array',
            'quantity' => 'required|integer|min:1|max:50',
            'photo' => 'nullable|image|max:2048',
            'room_title' => 'required_without:custom_room_title|string|nullable',
            'custom_room_title' => 'required_without:room_title|string|nullable|max:255',
        ];
        $validatedData = $request->validate($rules);

        // Determine the final room title from the request
        $roomTitle = $request->filled('custom_room_title') ?
        $request->custom_room_title : $request->room_title;

        $isUpdate = $request->has('_method') && $request->_method === 'PUT';
        
        if (!$fullSetId) {
            $fullSetId = 'FS-' . Str::upper(Str::random(8));
        }

        // Handle photo upload - store once for the entire full set
        $photoPath = null;
        $oldPhotoPath = null;
        
        if ($isUpdate) {
            // For updates, get the existing photo from the full set
            $existingItem = RoomItem::where('full_set_id', $fullSetId)->first();
            if ($existingItem) {
                $oldPhotoPath = $existingItem->photo;
                $photoPath = $oldPhotoPath; // Keep existing photo by default
            }
        }
        
        if ($request->hasFile('photo')) {
            // Upload new photo
            $photoPath = $request->file('photo')->store('public/photos');
            
            // Delete old photo if it exists
            if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                Storage::delete($oldPhotoPath);
            }
        }

        if ($isUpdate) {
            // Delete existing items in the full set
            RoomItem::where('full_set_id', $fullSetId)->delete();
        }

        // Get the starting PC number for this room
        $quantity = $validatedData['quantity'];
        $useDeletedPc = $request->has('use_deleted_pc') && $request->use_deleted_pc == '1';
        
        // If using deleted PC#, we need to handle it differently
        $deletedPcNumbers = [];
        if ($useDeletedPc) {
            $deletedPcNumbers = $this->getDeletedPcNumbersInternal($roomTitle);
            // Sort deleted PC numbers in ascending order to restore sequence properly
            sort($deletedPcNumbers);
        }
        
        // Get the next available PC number (for continuing sequence after deleted PC#s)
        $nextAvailablePcNumber = intval($this->getNextPcNumber($roomTitle, false));
        
        // Create multiple full sets based on quantity
        // When using deleted PC numbers, they count towards the quantity
        $deletedPcIndex = 0; // Index for iterating through deleted PC numbers
        $sequenceIndex = 0; // Index for continuing sequence after deleted PC#s
        
        for ($setIndex = 0; $setIndex < $quantity; $setIndex++) {
            // If using deleted PC#s and we have deleted ones, use them first (in ascending order)
            if ($useDeletedPc && !empty($deletedPcNumbers) && $deletedPcIndex < count($deletedPcNumbers)) {
                // Use the deleted PC# at this index (counts as 1 in quantity)
                // This restores the sequence by filling gaps first
                $pcNumberInt = $deletedPcNumbers[$deletedPcIndex];
                $deletedPcIndex++;
            } else {
                // Continue from the next available PC number (skip deleted PC#s)
                $pcNumberInt = $nextAvailablePcNumber + $sequenceIndex;
                $sequenceIndex++;
            }

            // Retry loop to avoid barcode unique collisions by advancing PC number
            while (true) {
                $pcNumber = str_pad($pcNumberInt, 3, '0', STR_PAD_LEFT);

                try {
                    DB::transaction(function () use ($validatedData, $roomTitle, $pcNumber, $photoPath, $request, $fullSetId, $setIndex) {
                        foreach ($validatedData['fullset_categories'] as $index => $componentCategory) {
                            // Generate barcode for each component using the same PC number
                            $barcode = $this->generateBarcodeForFullSet($roomTitle, $componentCategory, $pcNumber);
                            // Generate unique serial number for each component
                            $serialNumber = $this->generateSerialNumber();
                            RoomItem::create([
                                'user_id' => auth()->id(),
                                'room_title' => $roomTitle,
                                'device_category' => $componentCategory, // Actual component category
                                'device_type' => $this->getDeviceType($componentCategory),
                                'brand' => $validatedData['fullset_brand'],
                                'model' => $validatedData['fullset_model'],
                                'serial_number' => $serialNumber,
                                'barcode' => $barcode,
                                'photo' => $photoPath,
                                'description' => $request->description,
                                'quantity' => 1,
                                'status' => $request->status,
                                'full_set_id' => $fullSetId . '-' . ($setIndex + 1),
                                'is_full_set_item' => true,
                            ]);
                        }
                    });

                    // Success for this set, break retry loop
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    // If unique barcode constraint hit, advance PC number and retry
                    $message = $e->getMessage();
                    if (strpos($message, 'room_items_barcode_unique') !== false || strpos($message, 'Duplicate entry') !== false) {
                        $pcNumberInt++;
                        continue;
                    }
                    throw $e;
                }
            }
        }

        return redirect()->route('room-manage')->with('success', 'Full set has been saved!');
    }

    /**
     * Handles the routing for item updates.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $item = RoomItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Handle photo upload for both single items and full set items
        $photoPath = $item->photo;
        $photoUpdated = false;
        
        if ($request->hasFile('photo')) {
            $photoUpdated = true;
            
            if ($item->is_full_set_item) {
                // For full set items, update photo for all items in the set
                $oldPhotoPath = $item->photo;
                
                // Store new photo
                $photoPath = $request->file('photo')->store('public/photos');
                
                // Update all items in the full set with the new photo
                RoomItem::where('full_set_id', $item->full_set_id)->update(['photo' => $photoPath]);
                
                // Delete old photo if it exists
                if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                    Storage::delete($oldPhotoPath);
                }
            } else {
                // For single items
                $oldPhotoPath = $item->photo;
                
                // Store new photo
                $photoPath = $request->file('photo')->store('public/photos');
                
                // Delete old photo if it exists
                if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                    Storage::delete($oldPhotoPath);
                }
            }
        }
        
        // Handle full set updates
        if ($item->is_full_set_item && $request->device_category === 'Full Set') {
            return $this->storeFullSet($request, $item->full_set_id);
        }

        // For single item updates that are part of a full set
        if ($item->is_full_set_item && $request->device_category !== 'Full Set') {
            return $this->storeSingleItem($request, $photoPath, $item->status, $item->full_set_id, $item->is_full_set_item, $item->serial_number, $item->barcode);
        }

        // General case for all single item updates
        return $this->storeSingleItem($request, $photoPath, $item->status, $item->full_set_id, $item->is_full_set_item, $item->serial_number, $item->barcode);
    }

    /**
     * Update photo for a single item or full set
     */
    public function updatePhoto(Request $request, $id)
    {
        $user = auth()->user();
        
        $request->validate([
            'photo' => 'required|image|max:2048'
        ]);

        $item = RoomItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Handle photo upload
        $photoPath = $request->file('photo')->store('public/photos');
        
        if ($item->is_full_set_item) {
            // For full set items, update photo for all items in the set (only user's items)
            $oldPhotoPath = $item->photo;
            
            // Update all items in the full set (only user's items)
            RoomItem::where('full_set_id', $item->full_set_id)
                ->where('user_id', $user->id)
                ->update(['photo' => $photoPath]);
            
            // Delete old photo if it exists
            if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                Storage::delete($oldPhotoPath);
            }
            
            $message = 'Photo updated for all items in the full set!';
        } else {
            // For single items
            $oldPhotoPath = $item->photo;
            
            // Update the single item
            $item->update(['photo' => $photoPath]);
            
            // Delete old photo if it exists
            if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                Storage::delete($oldPhotoPath);
            }
            
            $message = 'Photo updated successfully!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove photo from item(s)
     */
    public function removePhoto($id)
    {
        $user = auth()->user();
        $item = RoomItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        if ($item->is_full_set_item) {
            // For full set items, remove photo from all items in the set (only user's items)
            $photoPath = $item->photo;
            
            // Update all items in the full set (only user's items)
            RoomItem::where('full_set_id', $item->full_set_id)
                ->where('user_id', $user->id)
                ->update(['photo' => null]);
            
            // Delete the photo file
            if ($photoPath && Storage::exists($photoPath)) {
                Storage::delete($photoPath);
            }
            
            $message = 'Photo removed from all items in the full set!';
        } else {
            // For single items
            $photoPath = $item->photo;
            
            // Update the single item
            $item->update(['photo' => null]);
            
            // Delete the photo file
            if ($photoPath && Storage::exists($photoPath)) {
                Storage::delete($photoPath);
            }
            
            $message = 'Photo removed successfully!';
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $item = RoomItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        if ($item->is_full_set_item) {
            $fullSetItems = RoomItem::where('full_set_id', $item->full_set_id)
                ->where('user_id', $user->id)
                ->get();
            // Delete the shared photo only once
            if ($fullSetItems->isNotEmpty() && $fullSetItems->first()->photo && Storage::exists($fullSetItems->first()->photo)) {
                Storage::delete($fullSetItems->first()->photo);
            }

            // Delete all items in the full set (only user's items)
            RoomItem::where('full_set_id', $item->full_set_id)
                ->where('user_id', $user->id)
                ->delete();
        } else {
            if ($item->photo && Storage::exists($item->photo)) {
                Storage::delete($item->photo);
            }
            $item->delete();
        }

        return redirect()->route('room-manage')->with('success', 'Item(s) deleted successfully!');
    }

    /**
     * Bulk delete multiple items
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:room_items,id'
        ]);

        $itemIds = $request->input('item_ids');
        $deletedCount = 0;
        $processedFullSets = [];

        $user = auth()->user();
        
        // Use database transaction for better performance and consistency
        \DB::transaction(function () use ($itemIds, &$deletedCount, &$processedFullSets, $user) {
            foreach ($itemIds as $itemId) {
                $item = RoomItem::where('id', $itemId)
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$item) {
                    continue; // Skip if item doesn't belong to user
                }
                
                if ($item->is_full_set_item) {
                    $fullSetId = $item->full_set_id;
                    
                    // Skip if we've already processed this full set
                    if (in_array($fullSetId, $processedFullSets)) {
                        continue;
                    }
                    
                    $processedFullSets[] = $fullSetId;
                    $fullSetItems = RoomItem::where('full_set_id', $fullSetId)
                        ->where('user_id', $user->id)
                        ->get();
                    
                    // Delete the shared photo only once
                    if ($fullSetItems->isNotEmpty() && $fullSetItems->first()->photo && Storage::exists($fullSetItems->first()->photo)) {
                        Storage::delete($fullSetItems->first()->photo);
                    }

                    // Delete all items in the full set (only user's items)
                    $deletedCount += RoomItem::where('full_set_id', $fullSetId)
                        ->where('user_id', $user->id)
                        ->count();
                    RoomItem::where('full_set_id', $fullSetId)
                        ->where('user_id', $user->id)
                        ->delete();
                } else {
                    if ($item->photo && Storage::exists($item->photo)) {
                        Storage::delete($item->photo);
                    }
                    $item->delete();
                    $deletedCount++;
                }
            }
        });

        return redirect()->route('room-manage')->with('success', "Successfully deleted {$deletedCount} item(s)!");
    }

    public function getRoomsList()
    {
        $user = auth()->user();
        
        $predefinedRooms = [
            'Server',
            'ComLab 1',
            'ComLab 2',
            'ComLab 3',
            'ComLab 4',
            'ComLab 5'
        ];
        
        // Build query for custom rooms with user isolation (always filter by user)
        $customRooms = RoomItem::whereNotIn('room_title', $predefinedRooms)
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('room_title')
            ->toArray();
            
        return response()->json([
            'predefined' => $predefinedRooms,
            'custom' => $customRooms
        ]);
    }

    public function getFullSetComponents()
    {
        $components = [
            'System Unit' => 'PC',
            'Monitor' => 'Monitor',
            'Keyboard' => 'Keyboard',
            'Mouse' => 'Mouse',
            'Power Supply Unit' => 'PSU',
            'SSD' => 'SSD',
            'Motherboard' => 'MB',
            'Graphic Card' => 'GPU',
            'RAM' => 'RAM',
            'Speaker' => 'Speaker',
            'Webcam' => 'Webcam',
            'Headset' => 'Headset',
        ];
        return response()->json($components);
    }

    public function searchByBarcode($pattern)
    {
        $user = auth()->user();
        
        $cleanPattern = str_replace(' ', '', $pattern);
        $items = RoomItem::where(function($query) use ($pattern, $cleanPattern) {
            $query->where('barcode', 'LIKE', '%' . $pattern . '%')
                  ->orWhere('barcode', 'LIKE', '%' . $cleanPattern . '%');
        })
        ->where('user_id', $user->id) // Always filter by user
        ->orderBy('barcode')
        ->get();
        
        // Add photo URLs to search results
        $items->transform(function ($item) {
            if ($item->photo) {
                $item->photo_url = Storage::url($item->photo);
            }
            return $item;
        });
        return $items;
    }

    /**
     * Update a custom room's title.
     * This method is specifically for editing the room title of custom rooms,
     * updating all items associated with that room title.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCustomRoom(Request $request)
    {
        $user = auth()->user();
        
        $validatedData = $request->validate([
            'old_room_title' => 'required|string',
            'new_room_title' => [
                'required',
                'string',
                'max:255',
                // Ensure the new title doesn't conflict with predefined ones
                Rule::notIn([
                    'Server', 'ComLab 1', 'ComLab 2', 'ComLab 3', 'ComLab 4', 'ComLab 5'
                ]),
            ],
        ]);
        $oldRoomTitle = $validatedData['old_room_title'];
        $newRoomTitle = $validatedData['new_room_title'];

        // Find all items with the old room title (only user's items)
        $itemsToUpdate = RoomItem::where('room_title', $oldRoomTitle)
            ->where('user_id', $user->id)
            ->get();
        if ($itemsToUpdate->isEmpty()) {
            return redirect()->back()->with('error', 'No items found for the custom room: ' . $oldRoomTitle);
        }

        // Group items by full set to maintain PC numbering consistency
        $fullSets = [];
        $singleItems = [];
        
        foreach ($itemsToUpdate as $item) {
            if ($item->is_full_set_item) {
                $fullSets[$item->full_set_id][] = $item;
            } else {
                $singleItems[] = $item;
            }
        }

        // Update full sets first - each full set gets a new PC number
        foreach ($fullSets as $fullSetId => $items) {
            $pcNumber = $this->getNextPcNumber($newRoomTitle);
            foreach ($items as $item) {
                $newBarcode = $this->generateBarcodeForFullSet($newRoomTitle, $item->device_category, $pcNumber);
                $item->room_title = $newRoomTitle;
                $item->barcode = $newBarcode;
                $item->save();
            }
        }

        // Update single items
        foreach ($singleItems as $item) {
            $newBarcode = $this->generateBarcode($newRoomTitle, $item->device_category);
            $item->room_title = $newRoomTitle;
            $item->barcode = $newBarcode;
            $item->save();
        }

        return redirect()->route('room-manage')->with('success', 'Custom room title updated successfully!');
    }

    /**
     * Generate unique serial number with 7 characters (letters and numbers)
     */
    private function generateSerialNumber()
    {
        $user = auth()->user();
        $maxAttempts = 100;
        $attempts = 0;

        do {
            // Generate 7-character string with letters and numbers
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $serialNumber = '';

            for ($i = 0; $i < 7; $i++) {
                $serialNumber .= $characters[rand(0, strlen($characters) - 1)];
            }

            $attempts++;
            // Check if this serial number already exists (only within user's data)
            $exists = RoomItem::where('serial_number', $serialNumber)
                ->where('user_id', $user->id)
                ->exists();
            if (!$exists) {
                return $serialNumber;
            }

        } while ($attempts < $maxAttempts);
        // If we somehow can't generate a unique serial number after 100 attempts,
        // add timestamp to ensure uniqueness
        return Str::upper(Str::random(4)) . time() % 1000;
    }

    /**
     * Auto-assign device type based on device category
     */
    private function getDeviceType($deviceCategory)
    {
        $deviceCategory = strtolower($deviceCategory);
        // Define peripherals
        $peripherals = [
            'keyboard', 'mouse', 'monitor', 'printer', 'scanner', 'webcam',
            'microphone', 'external hard drive', 'usb flash drive', 'headphones',
            'modem', 'wi-fi adapter', 'speakers', 'flash drive', 'usb hub', 'nic',
            'headset', 'projector', 'router', 'switch', 'speaker'
        ];
        // Define computer units
        $computerUnits = [
            'system unit', 'central processing unit', 'cpu', 'graphics processing unit',
            'gpu', 'graphic card', 'video card', 'random access memory', 'ram',
            'storage devices', 'hard disk drives', 'hdds', 'usb flash drives',
            'external ssds', 'ssd', 'motherboard', 'power supply unit', 'psu'
        ];

        // Check if device category matches any peripheral
        if (in_array($deviceCategory, $peripherals)) {
            return 'Peripherals';
        }

        // Check if device category matches any computer unit
        if (in_array($deviceCategory, $computerUnits)) {
            return 'Computer Units';
        }

        // If no match found, return the original category
        return $deviceCategory;
    }

    /**
     * API endpoint to get deleted PC numbers for a room
     */
    public function getDeletedPcNumbers($roomTitle)
    {
        // Decode URL-encoded room title
        $roomTitle = urldecode($roomTitle);
        
        $deletedPcNumbers = $this->getDeletedPcNumbersInternal($roomTitle);
        return response()->json([
            'deleted_pc_numbers' => $deletedPcNumbers,
            'has_deleted' => !empty($deletedPcNumbers)
        ]);
    }

    /**
     * Get deleted PC numbers (gaps in sequence) for a room
     */
    private function getDeletedPcNumbersInternal($roomTitle)
    {
        $user = auth()->user();
        $roomCodes = [
            'Server' => 'SRV',
            'ComLab 1' => 'CL1',
            'ComLab 2' => 'CL2',
            'ComLab 3' => 'CL3',
            'ComLab 4' => 'CL4',
            'ComLab 5' => 'CL5',
            'ComLab 6' => 'CL6',
            'ComLab 7' => 'CL7',
            'ComLab 8' => 'CL8',
            'ComLab 9' => 'CL9',
            'ComLab 10' => 'CL10',
            'Computer Lab 1' => 'CL1',
            'Computer Lab 2' => 'CL2',
            'Computer Lab 3' => 'CL3',
            'Computer Lab 4' => 'CL4',
            'Computer Lab 5' => 'CL5',
            'Computer Lab 6' => 'CL6',
            'Computer Lab 7' => 'CL7',
            'Computer Lab 8' => 'CL8',
            'Computer Lab 9' => 'CL9',
            'Computer Lab 10' => 'CL10',
            'Lab 1' => 'L1',
            'Lab 2' => 'L2',
            'Lab 3' => 'L3',
            'Lab 4' => 'L4',
            'Lab 5' => 'L5',
            'Lab 6' => 'L6',
            'Lab 7' => 'L7',
            'Lab 8' => 'L8',
            'Lab 9' => 'L9',
            'Lab 10' => 'L10',
            'Office' => 'OFF',
            'Library' => 'LIB',
            'Classroom' => 'CLS',
            'Conference Room' => 'CFR',
            'Storage' => 'STG',
            'Maintenance' => 'MNT',
            'IT Room' => 'ITR',
            'Network Room' => 'NET',
            'Data Center' => 'DC',
        ];
        
        $roomCode = $roomCodes[$roomTitle] ??
            strtoupper(substr(str_replace([' ', '-', '_'], '', $roomTitle), 0, 3));
        
        // Get all existing PC numbers for this room
        $existingBarcodes = RoomItem::where('room_title', $roomTitle)
            ->where('user_id', $user->id)
            ->where('barcode', 'LIKE', $roomCode . '-%')
            ->pluck('barcode');
        
        $usedPcNumbers = [];
        $escapedRoomCode = preg_quote($roomCode, '/');
        foreach ($existingBarcodes as $barcode) {
            if (preg_match('/^' . $escapedRoomCode . '-[A-Z]+(\d+)$/i', $barcode, $matches)) {
                $num = intval($matches[1]);
                if (!in_array($num, $usedPcNumbers)) {
                    $usedPcNumbers[] = $num;
                }
            }
        }
        
        sort($usedPcNumbers);
        
        // Find gaps (deleted PC numbers)
        // Check from PC001 (1) up to the maximum PC number to find all gaps
        $deletedPcNumbers = [];
        if (!empty($usedPcNumbers)) {
            $maxPc = max($usedPcNumbers);
            
            // Check from 1 to maxPc to find all gaps (including gaps before the first PC)
            for ($i = 1; $i <= $maxPc; $i++) {
                if (!in_array($i, $usedPcNumbers)) {
                    $deletedPcNumbers[] = $i;
                }
            }
        }
        
        return $deletedPcNumbers;
    }

    /**
     * Get the next available PC number for a room
     */
    private function getNextPcNumber($roomTitle, $useDeletedPc = false)
    {
        // Get room code - comprehensive mapping for all room types
        $roomCodes = [
            'Server' => 'SRV',
            'ComLab 1' => 'CL1',
            'ComLab 2' => 'CL2',
            'ComLab 3' => 'CL3',
            'ComLab 4' => 'CL4',
            'ComLab 5' => 'CL5',
            'ComLab 6' => 'CL6',
            'ComLab 7' => 'CL7',
            'ComLab 8' => 'CL8',
            'ComLab 9' => 'CL9',
            'ComLab 10' => 'CL10',
            'Computer Lab 1' => 'CL1',
            'Computer Lab 2' => 'CL2',
            'Computer Lab 3' => 'CL3',
            'Computer Lab 4' => 'CL4',
            'Computer Lab 5' => 'CL5',
            'Computer Lab 6' => 'CL6',
            'Computer Lab 7' => 'CL7',
            'Computer Lab 8' => 'CL8',
            'Computer Lab 9' => 'CL9',
            'Computer Lab 10' => 'CL10',
            'Lab 1' => 'L1',
            'Lab 2' => 'L2',
            'Lab 3' => 'L3',
            'Lab 4' => 'L4',
            'Lab 5' => 'L5',
            'Lab 6' => 'L6',
            'Lab 7' => 'L7',
            'Lab 8' => 'L8',
            'Lab 9' => 'L9',
            'Lab 10' => 'L10',
            'Office' => 'OFF',
            'Library' => 'LIB',
            'Classroom' => 'CLS',
            'Conference Room' => 'CFR',
            'Storage' => 'STG',
            'Maintenance' => 'MNT',
            'IT Room' => 'ITR',
            'Network Room' => 'NET',
            'Data Center' => 'DC',
        ];
        
        $roomCode = $roomCodes[$roomTitle] ??
            strtoupper(substr(str_replace([' ', '-', '_'], '', $roomTitle), 0, 3));

        // Find the highest PC number by looking at existing barcodes with the room code
        // Only consider user's items for PC numbering
        $user = auth()->user();
        $existingBarcodes = RoomItem::where('room_title', $roomTitle)
            ->where('user_id', $user->id) // Only user's items
            ->where('barcode', 'LIKE', $roomCode . '-%')
            ->pluck('barcode');

        $maxPcNumber = 0;
        $escapedRoomCode = preg_quote($roomCode, '/');
        foreach ($existingBarcodes as $barcode) {
            // Extract trailing number from formats like: CL1-SU001, CL1-MS120, etc.
            // Pattern: ^ROOM-[A-Z]+(digits)$, case-insensitive
            if (preg_match('/^' . $escapedRoomCode . '-[A-Z]+(\d+)$/i', $barcode, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxPcNumber) {
                    $maxPcNumber = $num;
                }
            }
        }

        // If we should use deleted PC numbers, get the first deleted one
        if ($useDeletedPc) {
            $deletedPcNumbers = $this->getDeletedPcNumbersInternal($roomTitle);
            if (!empty($deletedPcNumbers)) {
                return str_pad($deletedPcNumbers[0], 3, '0', STR_PAD_LEFT);
            }
        }
        
        $nextPcNumber = $maxPcNumber + 1;

        return str_pad($nextPcNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate barcode for Full Set items with PC counting
     */
    private function generateBarcodeForFullSet($roomTitle, $deviceCategory, $pcNumber)
    {
        // Get room code - comprehensive mapping for all room types
        $roomCodes = [
            'Server' => 'SRV',
            'ComLab 1' => 'CL1',
            'ComLab 2' => 'CL2',
            'ComLab 3' => 'CL3',
            'ComLab 4' => 'CL4',
            'ComLab 5' => 'CL5',
            'ComLab 6' => 'CL6',
            'ComLab 7' => 'CL7',
            'ComLab 8' => 'CL8',
            'ComLab 9' => 'CL9',
            'ComLab 10' => 'CL10',
            'Computer Lab 1' => 'CL1',
            'Computer Lab 2' => 'CL2',
            'Computer Lab 3' => 'CL3',
            'Computer Lab 4' => 'CL4',
            'Computer Lab 5' => 'CL5',
            'Computer Lab 6' => 'CL6',
            'Computer Lab 7' => 'CL7',
            'Computer Lab 8' => 'CL8',
            'Computer Lab 9' => 'CL9',
            'Computer Lab 10' => 'CL10',
            'Lab 1' => 'L1',
            'Lab 2' => 'L2',
            'Lab 3' => 'L3',
            'Lab 4' => 'L4',
            'Lab 5' => 'L5',
            'Lab 6' => 'L6',
            'Lab 7' => 'L7',
            'Lab 8' => 'L8',
            'Lab 9' => 'L9',
            'Lab 10' => 'L10',
            'Office' => 'OFF',
            'Library' => 'LIB',
            'Classroom' => 'CLS',
            'Conference Room' => 'CFR',
            'Storage' => 'STG',
            'Maintenance' => 'MNT',
            'IT Room' => 'ITR',
            'Network Room' => 'NET',
            'Data Center' => 'DC',
        ];
        
        $roomCode = $roomCodes[$roomTitle] ??
            strtoupper(substr(str_replace([' ', '-', '_'], '', $roomTitle), 0, 3));

        // Device category code mapping for full set components
        $deviceCodes = [
            'System Unit' => 'SU',
            'Monitor' => 'M',
            'Keyboard' => 'K',
            'Mouse' => 'MS',
            'Power Supply Unit' => 'PSU',
            'SSD' => 'SSD',
            'Motherboard' => 'MB',
            'Graphic Card' => 'GPU',
            'RAM' => 'RAM',
            'Speaker' => 'SP',
            'Webcam' => 'WC',
            'Headset' => 'HS',
            'High-Definition Multimedia Interface' => 'HDMI',
            'High-Definition Multimedia Interface (HDMI)' => 'HDMI',
            'Video Graphics Array' => 'VGA',
            'Video Graphics Array (VGA)' => 'VGA',
        ];

        $deviceCode = $deviceCodes[$deviceCategory] ??
        strtoupper(substr(str_replace(' ', '', $deviceCategory), 0, 2));

        // Generate barcode in format: CL1-SU001 (PC001 = CL1-SU001)
        // The component number should match the PC number
        $formattedNumber = str_pad($pcNumber, 3, '0', STR_PAD_LEFT);
        
        return $roomCode . '-' . $deviceCode . $formattedNumber;
    }

    /**
     * Generate barcode based on room and device category (for single items)
     */
    private function generateBarcode($roomTitle, $deviceCategory)
    {
        // Room code mapping - comprehensive mapping for all room types
        $roomCodes = [
            'Server' => 'SRV',
            'ComLab 1' => 'CL1',
            'ComLab 2' => 'CL2',
            'ComLab 3' => 'CL3',
            'ComLab 4' => 'CL4',
            'ComLab 5' => 'CL5',
            'ComLab 6' => 'CL6',
            'ComLab 7' => 'CL7',
            'ComLab 8' => 'CL8',
            'ComLab 9' => 'CL9',
            'ComLab 10' => 'CL10',
            'Computer Lab 1' => 'CL1',
            'Computer Lab 2' => 'CL2',
            'Computer Lab 3' => 'CL3',
            'Computer Lab 4' => 'CL4',
            'Computer Lab 5' => 'CL5',
            'Computer Lab 6' => 'CL6',
            'Computer Lab 7' => 'CL7',
            'Computer Lab 8' => 'CL8',
            'Computer Lab 9' => 'CL9',
            'Computer Lab 10' => 'CL10',
            'Lab 1' => 'L1',
            'Lab 2' => 'L2',
            'Lab 3' => 'L3',
            'Lab 4' => 'L4',
            'Lab 5' => 'L5',
            'Lab 6' => 'L6',
            'Lab 7' => 'L7',
            'Lab 8' => 'L8',
            'Lab 9' => 'L9',
            'Lab 10' => 'L10',
            'Office' => 'OFF',
            'Library' => 'LIB',
            'Classroom' => 'CLS',
            'Conference Room' => 'CFR',
            'Storage' => 'STG',
            'Maintenance' => 'MNT',
            'IT Room' => 'ITR',
            'Network Room' => 'NET',
            'Data Center' => 'DC',
        ];
        
        // Device category code mapping
        $deviceCodes = [
            'Printer' => 'P',
            'Computer' => 'PC',
            'Monitor' => 'M',
            'Keyboard' => 'K',
            'Mouse' => 'MS',
            'Speaker' => 'SP',
            'Projector' => 'PJ',
            'Router' => 'R',
            'Switch' => 'SW',
            'Scanner' => 'SC',
            'System Unit' => 'SU',
            'Power Supply Unit' => 'PSU',
            'SSD' => 'SSD',
            'Motherboard' => 'MB',
            'Graphic Card' => 'GPU',
            'RAM' => 'RAM',
            'Webcam' => 'WC',
            'Headset' => 'HS',
            'High-Definition Multimedia Interface' => 'HDMI',
            'High-Definition Multimedia Interface (HDMI)' => 'HDMI',
            'Video Graphics Array' => 'VGA',
            'Video Graphics Array (VGA)' => 'VGA',
            
        ];
        
        // Get room code
        $roomCode = $roomCodes[$roomTitle] ?? 
            strtoupper(substr(str_replace([' ', '-', '_'], '', $roomTitle), 0, 3));

        // Get device code
        $deviceCode = $deviceCodes[$deviceCategory] ??
        strtoupper(substr(str_replace(' ', '', $deviceCategory), 0, 2));

        // Generate barcode in format: CL1-SU001
        $basePrefix = $roomCode . '-' . $deviceCode;

        // Find the highest existing number for this prefix in the same room
        // Always filter by user to prevent conflicts
        $user = auth()->user();
        $existingItemsQuery = RoomItem::where('barcode', 'LIKE', $basePrefix . '%')
            ->where('room_title', $roomTitle)
            ->where('is_full_set_item', false) // Exclude full set items from single item counting
            ->where('user_id', $user->id); // Always filter by user
        
        $existingItems = $existingItemsQuery->orderBy('barcode', 'desc')->first();
            
        $nextNumber = 1;
        if ($existingItems) {
            // Extract the number from the existing barcode (format: "CL1-SU001")
            if (preg_match('/-(\d+)$/', $existingItems->barcode, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
        }

        // Format the number with leading zeros (3 digits)
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        return $basePrefix . $formattedNumber;
    }

    /**
     * Get photo URL for display
     */
    public function getPhotoUrl($photoPath)
    {
        if (!$photoPath) {
            return null;
        }

        return Storage::url($photoPath);
    }

    /**
     * Display photo - separate endpoint for viewing photos
     */
    public function showPhoto($id)
    {
        $user = auth()->user();
        $item = RoomItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        if (!$item->photo || !Storage::exists($item->photo)) {
            abort(404, 'Photo not found');
        }

        $photoPath = Storage::path($item->photo);
        $mimeType = Storage::mimeType($item->photo);
        return response()->file($photoPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($photoPath) . '"'
        ]);
    }

    /**
     * Get next available serial number (for preview purposes)
     */
    public function getNextSerialNumber()
    {
        return response()->json([
            'serial_number' => $this->generateSerialNumber()
        ]);
    }

    /**
     * Delete all items in a room (Delete Room action)
     */
    public function destroyRoom(Request $request, $room)
    {
        $user = auth()->user();
        
        // Prefer the original room title from the request (non-slug)
        $request->validate([
            'room_title' => 'required|string'
        ]);

        $roomTitle = $request->input('room_title');

        // Collect all items in the room (only user's items)
        $items = RoomItem::where('room_title', $roomTitle)
            ->where('user_id', $user->id)
            ->get();
        if ($items->isEmpty()) {
            return redirect()->route('room-manage')->with('error', 'No items found for room: ' . $roomTitle);
        }

        // Track photos to delete once
        $photosToDelete = [];

        // If any items are part of full sets, group by full_set_id so we delete them together
        $fullSetIds = $items->where('is_full_set_item', true)
            ->pluck('full_set_id')
            ->unique()
            ->values();

        // Delete all full sets (all items sharing the same full_set_id, only user's items)
        foreach ($fullSetIds as $fullSetId) {
            $fullSetItems = RoomItem::where('full_set_id', $fullSetId)
                ->where('user_id', $user->id)
                ->get();
            if ($fullSetItems->isNotEmpty()) {
                $photo = $fullSetItems->first()->photo;
                if ($photo) { $photosToDelete[$photo] = true; }
                RoomItem::where('full_set_id', $fullSetId)
                    ->where('user_id', $user->id)
                    ->delete();
            }
        }

        // Delete remaining single items in the room (only user's items)
        $singleItems = RoomItem::where('room_title', $roomTitle)
            ->where('is_full_set_item', false)
            ->where('user_id', $user->id)
            ->get();

        foreach ($singleItems as $item) {
            if ($item->photo) { $photosToDelete[$item->photo] = true; }
            $item->delete();
        }

        // Delete photos from storage once
        foreach (array_keys($photosToDelete) as $photoPath) {
            if ($photoPath && Storage::exists($photoPath)) {
                Storage::delete($photoPath);
            }
        }

        return redirect()->route('room-manage')->with('success', 'Room "' . $roomTitle . '" and its items were deleted successfully!');
    }

    /**
     * Add a component to an existing PC
     */
    public function addComponent(Request $request, $room, $pc)
    {
        $user = auth()->user();
        
        // Validate the request
        $validatedData = $request->validate([
            'room_title' => 'required|string',
            'device_category' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|string|in:Usable,Unusable',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('public/photos');
        }

        // Generate barcode for the component using the existing PC number
        $barcode = $this->generateBarcodeForFullSet($validatedData['room_title'], $validatedData['device_category'], $pc);

        // Try to find an existing full set id for this room + PC number so the new
        // component is grouped with the same PC set (only user's items)
        $existingPcItem = RoomItem::where('room_title', $validatedData['room_title'])
            ->where('barcode', 'LIKE', '%-' . $pc)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();
        $fullSetId = $existingPcItem ? $existingPcItem->full_set_id : null;
        $isFullSetItem = $fullSetId ? true : false;

        // Create the component
        RoomItem::create([
            'user_id' => auth()->id(),
            'room_title' => $validatedData['room_title'],
            'device_category' => $validatedData['device_category'],
            'device_type' => $this->getDeviceType($validatedData['device_category']),
            'brand' => $validatedData['brand'],
            'model' => $validatedData['model'],
            'serial_number' => $this->generateSerialNumber(),
            'barcode' => $barcode,
            'photo' => $photoPath,
            'description' => $validatedData['description'],
            'quantity' => 1,
            'status' => $validatedData['status'],
            'is_full_set_item' => $isFullSetItem,
            'full_set_id' => $fullSetId,
        ]);

        return redirect()->route('room-manage')->with('success', 'Component added successfully!');
    }
}