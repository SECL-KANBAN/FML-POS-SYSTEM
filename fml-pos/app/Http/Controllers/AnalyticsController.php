<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6);

        $summary = Transaction::query()
            ->selectRaw('COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as revenue, COALESCE(AVG(total), 0) as average_sale')
            ->first();

        $unitsSold = (int) TransactionItem::query()->sum('quantity');

        $dailySales = Transaction::query()
            ->selectRaw('DATE(completed_at) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as revenue')
            ->whereBetween('completed_at', [$weekStart->startOfDay(), $today->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $salesByDay = collect(range(0, 6))->map(function (int $daysAgo) use ($today, $dailySales): array {
            $date = $today->copy()->subDays(6 - $daysAgo);
            $sale = $dailySales->get($date->toDateString());

            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'revenue' => (float) ($sale->revenue ?? 0),
                'transaction_count' => (int) ($sale->transaction_count ?? 0),
            ];
        });

        $paymentMethods = Transaction::query()
            ->select('payment_method')
            ->selectRaw('COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        $topProducts = TransactionItem::query()
            ->select('name', 'sku')
            ->selectRaw('SUM(quantity) as units_sold, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('name', 'sku')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        return view('analytics.index', [
            'summary' => $summary,
            'unitsSold' => $unitsSold,
            'salesByDay' => $salesByDay,
            'paymentMethods' => $paymentMethods,
            'topProducts' => $topProducts,
        ]);
    }
}
