<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800" aria-labelledby="transactions-heading">
                <div class="border-b border-gray-200 p-4 sm:p-6 dark:border-gray-700">
                    <h3 id="transactions-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Transactions') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Review completed sales and payment details.') }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Transaction') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Date') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Items') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Total') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Payment Method') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Paid / Change') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6 dark:text-gray-400">
                                    {{ __('Status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900 sm:px-6 dark:text-gray-100">
                                        {{ $transaction->receipt_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                        {{ $transaction->completed_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                        <ul class="space-y-1">
                                            @foreach ($transaction->items as $item)
                                                <li>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</span>
                                                    @if ($item->sku)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $item->sku }})</span>
                                                    @endif
                                                    <span class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $item->quantity }} × ${{ number_format((float) $item->price, 2) }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900 sm:px-6 dark:text-gray-100">
                                        ${{ number_format((float) $transaction->total, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                        {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                        <div>${{ number_format((float) $transaction->amount_paid, 2) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('Change') }}: ${{ number_format((float) $transaction->change, 2) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm sm:px-6">
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            {{ __('Completed') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No transactions yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->hasPages())
                    <div class="border-t border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-700">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
