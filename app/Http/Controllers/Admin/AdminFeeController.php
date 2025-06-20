<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DriverDailyReport;
use App\Models\DriverMonthlyReport;
use App\Models\RestaurantDailyReport;
use App\Models\RestaurantMonthlyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminFeeController extends Controller
{
    public function getAdminDailyEarnings($year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day)->toDateString();

        $total = DriverDailyReport::where('date', $date)->sum('admin_earnings');

        return response()->json([
            'status' => true,
            'date' => $date,
            'total_admin_earnings' => $total,
        ]);
    }

    public function getAdminMonthlyEarnings($year, $month)
    {
        $monthFormatted = sprintf('%04d-%02d', $year, $month);

        $total = DriverMonthlyReport::where('month_date', $monthFormatted)->sum('admin_earnings');

        return response()->json([
            'status' => true,
            'month' => $monthFormatted,
            'total_admin_earnings' => $total,
        ]);
    }

    public function getAdminDailyEarningsFromRestaurants($year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day)->toDateString();

        $total = RestaurantDailyReport::where('date', $date)->sum('system_earnings');

        return response()->json([
            'status' => true,
            'date' => $date,
            'total_admin_earnings_from_restaurants' => $total,
        ]);
    }

    public function getAdminMonthlyEarningsFromRestaurants($year, $month)
    {
        $monthDate = Carbon::createFromDate($year, $month, 1)->format('Y-m');

        $total = RestaurantMonthlyReport::where('month_date', $monthDate)->sum('system_earnings');

        return response()->json([
            'status' => true,
            'month' => $monthDate,
            'total_admin_earnings_from_restaurants' => $total,
        ]);
    }
}
