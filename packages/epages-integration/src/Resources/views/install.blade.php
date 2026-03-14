<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect ePages Shop</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #333;
        }
        p {
            color: #666;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        input[type="url"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        input[type="url"]:focus {
            outline: none;
            border-color: #0066cc;
        }
        .hint {
            font-size: 13px;
            color: #888;
            margin-top: 6px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #0052a3;
        }
        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .success {
            background: #efe;
            border: 1px solid #cfc;
            color: #060;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Connect your ePages Shop</h1>
        <p>Enter your ePages shop URL to connect and authorize this application.</p>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        @if(session('message'))
            <div class="success">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{ route('epages.authorize') }}" method="GET">
            <div class="form-group">
                <label for="shop_url">Shop URL</label>
                <input
                    type="url"
                    id="shop_url"
                    name="shop_url"
                    placeholder="https://yourshop.epages.com"
                    value="{{ old('shop_url') }}"
                    required
                >
                <p class="hint">Enter the full URL of your ePages shop</p>
            </div>

            <button type="submit">Connect Shop</button>
        </form>
    </div>
</body>
</html>
