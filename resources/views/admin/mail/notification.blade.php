<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Orders Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #eee;
            background-color: #fafafa;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        p.subtitle {
            text-align: center;
            font-size: 16px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Orders Notification</h1>
        <p class="subtitle">{{ $orders->count() }} new order{{ $orders->count() > 1 ? 's' : '' }} received</p>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Package Name</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->shipping_name }}</td>
                        <td>{{ $order->shipping_email ?? 'N/A' }}</td>
                        <td>{{ $order->shipping_phone ?? 'N/A' }}</td>
                        <td>{{ $order->package->name ?? 'N/A' }}</td>
                        <td>${{ number_format($order->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you</p>
        </div>
    </div>
</body>
</html>
