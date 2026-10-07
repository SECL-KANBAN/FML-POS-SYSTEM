<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Receipt {{ $data['receipt_number'] }}</title>

    <style>
        body {
            font-family: Courier, monospace;    
            font-size: 12px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin-bottom: 5px;
        }

        table {
            font-family: Courier, monospace;
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
        }

        th {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;

        }
        .peso {
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>

<body>

    <div class="header">

        <h1>POS SYSTEM</h1>

        <div>Official Receipt</div>

        <div>
            Receipt #: {{ $data['receipt_number'] }}
        </div>

        <div>
            {{ $data['date'] }}
        </div>

    </div>


    <table>

        <thead>
            <tr>
                <th>Product</th>
                <th class="center">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($data['items'] as $item)

                <tr>

                    <td>
                        {{ $item['name'] }}<br>
                        <small>{{ $item['sku'] }}</small>
                    </td>

                    <td class="center">
                        {{ $item['quantity'] }}
                    </td>

                    <td class="right">
                        <span class="peso">₱</span>{{ number_format($item['price'], 2) }}
                    </td>

                    <td class="right">
                        <span class="peso">₱</span>{{ number_format($item['total'], 2) }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <div class="total">
    Total:
    <span class="peso">₱</span>{{ number_format($data['total'], 2) }}
        </div>

        <div style="margin-top: 15px; text-align: right; font-size: 13px;">

            <div>
                Payment Method:
                <strong>
                    {{ ucfirst(str_replace('_', ' ', $data['payment_method'])) }}
                </strong>
            </div>

            <div style="margin-top: 5px;">
                Amount Paid:
                <span class="peso">₱</span>{{ number_format($data['amount_paid'], 2) }}
            </div>

            <div style="margin-top: 5px; font-weight: bold;">
                Change:
                <span class="peso">₱</span>{{ number_format($data['change'], 2) }}
            </div>

        </div>


    <div class="footer">
        Thank you for your purchase!
    </div>

</body>
</html>