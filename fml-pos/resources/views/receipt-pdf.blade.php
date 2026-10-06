<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Receipt {{ $data['receipt_number'] }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
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
            color: #777;
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
                        ${{ number_format($item['price'], 2) }}
                    </td>

                    <td class="right">
                        ${{ number_format($item['total'], 2) }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <div class="total">
        Total:
        ${{ number_format($data['total'], 2) }}
    </div>


    <div class="footer">
        Thank you for your purchase!
    </div>

</body>
</html>