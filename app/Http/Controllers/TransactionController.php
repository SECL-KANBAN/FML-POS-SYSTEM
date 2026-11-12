<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::query()
            ->with('items')
            ->latest('completed_at')
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }
}
