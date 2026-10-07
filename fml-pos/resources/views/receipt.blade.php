<x-app-layout>


    <div class="py-8">

        {{-- Receipt Preview --}}
        <div class="flex justify-center px-4">

            <div
                id="receipt"
                class="receipt-paper w-[80mm] bg-white text-black shadow-lg rounded-lg"
                style="padding: 20px;"
            >

                {{-- HEADER --}}
                <div class="text-center">

                    <h1 class="text-base font-bold">
                        POS SYSTEM
                    </h1>

                    <p class="text-[10px]">
                        OFFICIAL RECEIPT
                    </p>

                    <div class="my-2 border-b border-dashed border-black"></div>

                    <p class="text-[9px]">
                        Receipt #: {{ $data['receipt_number'] }}
                    </p>

                    <p class="text-[9px]">
                        {{ $data['date'] }}
                    </p>

                </div>


                {{-- ITEMS --}}
                <div class="mt-3">

                    @foreach ($data['items'] as $item)

                        <div class="mb-2">

                            {{-- Product Name + Total --}}
                            <div class="flex justify-between gap-2 text-[10px] font-bold">

                                <span class="max-w-[45mm]">
                                    {{ $item['name'] }}
                                </span>

                                <span>
                                    ₱{{ number_format($item['total'], 2) }}
                                </span>

                            </div>


                            {{-- SKU --}}
                            <div class="text-[8px] text-gray-600">
                                {{ $item['sku'] }}
                            </div>


                            {{-- Quantity + Price --}}
                            <div class="flex justify-between text-[9px]">

                                <span>
                                    {{ $item['quantity'] }} x
                                    ₱{{ number_format($item['price'], 2) }}
                                </span>

                                <span>
                                    ₱{{ number_format($item['total'], 2) }}
                                </span>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- DIVIDER --}}
                <div class="border-t border-dashed border-black"></div>


                {{-- TOTAL --}}
                <div class="mt-2 flex justify-between text-xs font-bold">
                    <span>
                        TOTAL
                    </span>
                    <span>
                        ₱{{ number_format($data['total'], 2) }}
                    </span>
                </div>

                {{-- PAYMENT DETAILS --}}
                <div class="mt-3 space-y-1 text-[10px]">

                    <div class="flex justify-between">
                        <span>Payment Method</span>
                        <span class="font-semibold">
                            {{ ucfirst(str_replace('_', ' ', $data['payment_method'])) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span>Amount Paid</span>
                        <span>
                            ₱{{ number_format($data['amount_paid'], 2) }}
                        </span>
                    </div>

                    <div class="flex justify-between font-bold">
                        <span>Change</span>
                        <span>
                            ₱{{ number_format($data['change'], 2) }}
                        </span>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="mt-4 border-t border-dashed border-black pt-3 text-center">

                    <p class="text-[9px] font-semibold">
                        THANK YOU FOR YOUR PURCHASE!
                    </p>

                    <p class="mt-1 text-[8px]">
                        Please come again.
                    </p>

                </div>

            </div>

        </div>


{{-- BUTTONS --}}
<div class="mx-auto mt-6 flex w-[80mm] max-w-full justify-center gap-2 px-4">

    {{-- PDF Button --}}
    <a
        href="{{ route('checkout.receipt.pdf', $data['receipt_number']) }}"
        target="_blank"
        class="rounded-md bg-indigo-600 px-5 py-2 text-center text-[11px] font-semibold text-white shadow-sm transition hover:bg-indigo-500"
    >
        PDF Receipt
    </a>


    {{-- Print Button --}}
    <button
        type="button"
        onclick="printReceipt()"
        class="rounded-md bg-gray-700 px-5 py-2 text-[11px] font-semibold text-white shadow-sm transition hover:bg-gray-600"
    >
        Print Receipt
    </button>

</div>


{{-- BACK BUTTON --}}
<div class="mt-3 text-center">

    <a
        href="{{ route('dashboard') }}"
        class="text-[11px] text-indigo-600 hover:underline dark:text-indigo-400"
    >
        ← Back to Dashboard
    </a>

</div>

    </div>


    {{-- PRINT STYLE --}}
    <style>

        @media print {

            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            body * {
                visibility: hidden;
            }

            #receipt,
            #receipt * {
                visibility: visible;
            }

            #receipt {
                position: absolute;
                left: 0;
                top: 0;

                width: 80mm !important;

                margin: 0 !important;

                padding: 20px !important;

                box-shadow: none !important;
            }

            .receipt-paper {
                font-family: Arial, Helvetica, sans-serif;
            }

        }

    </style>


    {{-- PRINT SCRIPT --}}
    <script>

        function printReceipt() {
            window.print();
        }

    </script>

</x-app-layout>