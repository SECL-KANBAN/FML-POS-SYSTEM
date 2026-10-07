<x-app-layout>


    @php
        $chartLeft = 88;
        $chartRight = 742;
        $chartTop = 24;
        $chartBottom = 250;
        $chartMaxRevenue = max(1, (float) $salesByDay->max('revenue'));
        $chartPoints = $salesByDay->values()->map(function (array $day, int $index) use ($chartLeft, $chartRight, $chartTop, $chartBottom, $chartMaxRevenue): array {
            $x = $chartLeft + ($index * ($chartRight - $chartLeft) / 6);
            $y = $chartBottom - (($day['revenue'] / $chartMaxRevenue) * ($chartBottom - $chartTop));

            return [
                'x' => number_format($x, 2, '.', ''),
                'y' => number_format($y, 2, '.', ''),
                'day' => $day,
            ];
        });
        $chartLine = $chartPoints->map(fn (array $point): string => $point['x'].','.$point['y'])->implode(' ');
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Sales overview') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Analytics based on all completed transactions.') }}</p>
            </div>

            <section class="grid grid-cols-2 gap-4" aria-label="{{ __('Sales summary') }}">
                <article class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total revenue') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format((float) $summary->revenue, 2) }}</p>
                </article>
                <article class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Completed transactions') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((int) $summary->transaction_count) }}</p>
                </article>
                <article class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Average sale') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format((float) $summary->average_sale, 2) }}</p>
                </article>
                <article class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Units sold') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($unitsSold) }}</p>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <article class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800" aria-labelledby="daily-sales-heading">
                    <div class="mb-5">
                        <h3 id="daily-sales-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Daily revenue') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Last 7 days') }}</p>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <svg
                            viewBox="0 0 760 315"
                            class="h-auto w-full min-w-[540px]"
                            role="img"
                            aria-labelledby="daily-revenue-chart-title daily-revenue-chart-description"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <title id="daily-revenue-chart-title">{{ __('Daily revenue over the last 7 days') }}</title>
                            <desc id="daily-revenue-chart-description">{{ __('Line chart with dates on the horizontal axis and revenue in Philippine pesos on the vertical axis.') }}</desc>

                            @foreach (range(0, 4) as $tickIndex)
                                @php
                                    $tickValue = $chartMaxRevenue * (4 - $tickIndex) / 4;
                                    $tickY = $chartTop + ($tickIndex * ($chartBottom - $chartTop) / 4);
                                @endphp
                                <line x1="{{ $chartLeft }}" y1="{{ $tickY }}" x2="{{ $chartRight }}" y2="{{ $tickY }}" stroke="#37323d" stroke-width="1" />
                                <text x="{{ $chartLeft - 12 }}" y="{{ $tickY + 4 }}" fill="#a6a1ad" font-size="12" text-anchor="end">₱{{ number_format($tickValue, 2) }}</text>
                            @endforeach

                            <polyline
                                points="{{ $chartLine }}"
                                fill="none"
                                stroke="#a855f7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                            />

                            @foreach ($chartPoints as $point)
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="#d8b4fe" stroke="#111014" stroke-width="2">
                                    <title>{{ $point['day']['label'] }}, {{ $point['day']['date'] }}: ₱{{ number_format($point['day']['revenue'], 2) }} ({{ $point['day']['transaction_count'] }} {{ __('sales') }})</title>
                                </circle>
                                <text x="{{ $point['x'] }}" y="276" fill="#c8c2cf" font-size="12" text-anchor="middle">{{ $point['day']['label'] }}</text>
                                <text x="{{ $point['x'] }}" y="296" fill="#a6a1ad" font-size="11" text-anchor="middle">{{ $point['day']['date'] }}</text>
                            @endforeach
                        </svg>
                    </div>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800" aria-labelledby="payment-methods-heading">
                    <div class="mb-5">
                        <h3 id="payment-methods-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Payment methods') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Completed sales by payment method') }}</p>
                    </div>
                    @forelse ($paymentMethods as $paymentMethod)
                        <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-3 last:border-0 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $paymentMethod->payment_method === 'gcash' ? 'GCash' : ucfirst(str_replace('_', ' ', $paymentMethod->payment_method)) }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $paymentMethod->transaction_count }} {{ __('transactions') }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format((float) $paymentMethod->revenue, 2) }}</p>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No payment data yet.') }}</p>
                    @endforelse
                </article>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800" aria-labelledby="top-products-heading">
                <div class="border-b border-gray-200 p-5 dark:border-gray-700">
                    <h3 id="top-products-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Best-selling products') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ranked by units sold') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Product') }}</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('SKU') }}</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Units sold') }}</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($topProducts as $product)
                                <tr>
                                    <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $product->sku ?: '—' }}</td>
                                    <td class="px-5 py-3 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format((int) $product->units_sold) }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">₱{{ number_format((float) $product->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No product sales yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
