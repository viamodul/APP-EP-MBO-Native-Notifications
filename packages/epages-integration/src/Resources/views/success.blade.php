<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Connected</title>
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
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon svg {
            width: 32px;
            height: 32px;
            fill: white;
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
        .shop-info {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }
        .shop-info dt {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .shop-info dd {
            color: #333;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .shop-info dd:last-child {
            margin-bottom: 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0052a3;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #e5e5e5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>

        <h1>Shop Connected!</h1>
        <p>{{ session('message', 'Your ePages shop has been successfully connected.') }}</p>

        @if(session('shop'))
            <dl class="shop-info">
                <dt>Shop ID</dt>
                <dd>{{ session('shop')->shop_id }}</dd>
                <dt>Shop URL</dt>
                <dd>{{ session('shop')->shop_url }}</dd>
            </dl>
        @endif

        <a href="{{ url('/') }}" class="btn">Go to Dashboard</a>
    </div>
</body>
</html>
