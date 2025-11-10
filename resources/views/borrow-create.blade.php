<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrow Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }

        form {
            background: #fff;
            padding: 20px;
            max-width: 600px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        select, input[type="text"], input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }

        button {
            margin-top: 20px;
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        option.disabled {
            color: red;
            font-style: italic;
        }
    </style>
</head>
<body>

<h2>Borrow Item</h2>

<a href="/borrow" style="text-decoration: none; background: #ccc; padding: 8px 12px; border-radius: 5px; color: black;">← Back</a>

@if ($errors->any())
    <div style="color: red; margin-top: 15px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>⚠ {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/borrow/store">
    @csrf

    <label for="room_item_id">Select Item to Borrow:</label>
    <select name="room_item_id" id="room_item_id" required>
        <option value="">-- Choose an item --</option>
        @foreach ($items as $item)
            <option value="{{ $item->id }}"
                @if($item->status === 'Unusable' || $item->status === 'Borrowed') disabled class="disabled" @endif>
                {{ $item->room_title }} | {{ $item->device_category }} | {{ $item->serial_number }}
                @if($item->status === 'Unusable')
                    ❌ (Unusable)
                @elseif($item->status === 'Borrowed')
                    🔒 (Borrowed)
                @endif
            </option>
        @endforeach
    </select>

    <label for="borrower_name">Borrower Name:</label>
    <input type="text" name="borrower_name" id="borrower_name" required>

    <label for="borrow_date">Borrow Date:</label>
    <input type="date" name="borrow_date" id="borrow_date" required>

    <button type="submit"><i class="fas fa-save"></i> Borrow</button>
</form>

</body>
</html>
