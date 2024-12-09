<?php

namespace App\Http\Controllers;

use App\Models\Campaign;  
use Illuminate\Http\Request;
use DB;

class BudgetController extends Controller
{

     // Ensure the user is authorized before performing certain actions
    public function __construct()
    {
        $this->middleware('auth:api'); // Add authentication middleware for API routes
    }

    // API to get sum of total_budget and daily_budget
    public function getBudgetSums()
    {
        $totalBudgetSum = Campaign::sum('total_budget');
        $dailyBudgetSum = Campaign::sum('daily_budget');
        
        return response()->json([
            'total_budget_sum' => $totalBudgetSum,
            'daily_budget_sum' => $dailyBudgetSum
        ]);
    }



    // API to get records by month (for graph)
    public function getRecordsByMonth(Request $request)
    {
        // Validate the month input
        $request->validate([
            'month' => 'required|date_format:Y-m',  // e.g., 2024-01
        ]);

        $month = $request->input('month');
        
        // Query records for the given month
        $records = Campaign::whereMonth('created_at', '=', date('m', strtotime($month)))
                            ->whereYear('created_at', '=', date('Y', strtotime($month)))
                            ->get(['total_budget', 'daily_budget', 'created_at']);
        
        return response()->json($records);

    }




}
