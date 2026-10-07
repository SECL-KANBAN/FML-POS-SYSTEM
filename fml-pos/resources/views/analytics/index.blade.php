<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Analytics') }}
        </h2>
    </x-slot>

    @php
        $maxDailyRevenue = max(1, (float) $salesByDay->max('revenue'));
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
                    <div class="space-y-4">
                        @foreach ($salesByDay as $day)
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                    <span class="w-20 shrink-0 text-gray-600 dark:text-gray-300">{{ $day['label'] }}, {{ $day['date'] }}</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $day['transaction_count'] }} {{ __('sales') }}
                                    </span>
                                    <span class="shrink-0 font-medium text-gray-900 dark:text-gray-100">₱{{ number_format($day['revenue'], 2) }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div
                                        class="h-full rounded-full bg-indigo-600"
                                        style="width: {{ min(100, ($day['revenue'] / $maxDailyRevenue) * 100) }}%"
                                        role="progressbar"
                                        aria-label="{{ __('Revenue for') }} {{ $day['date'] }}"
                                        aria-valuenow="{{ $day['revenue'] }}"
                                        aria-valuemin="0"
                                        aria-valuemax="{{ $maxDailyRevenue }}"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
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
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $paymentMethod->payment_method)) }}</p>
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
