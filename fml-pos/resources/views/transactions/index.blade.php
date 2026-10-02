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
                                    {{ __('Status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No transactions yet.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
