<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reporting dashboard with analytics and filters
     */
    public function index(Request $request)
    {
        // 1. Gather Date Filters (default: last 30 days)
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : Carbon::now()->endOfDay();

        // 2. Query basic order analytics
        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        $filteredOrders = $ordersQuery->get();

        // Calculate KPIs based on date-range
        $totalOrdersCount = $filteredOrders->count();
        $pendingApprovalsCount = $filteredOrders->where('status', 'pending_approval')->count();
        $paidOrders = $filteredOrders->where('status', 'paid');
        $totalRevenue = $paidOrders->sum('total_amount');
        
        $paidOrdersCount = $paidOrders->count();
        $averageOrderValue = $paidOrdersCount > 0 ? $totalRevenue / $paidOrdersCount : 0.00;

        // 3. Compile daily revenue array for last 30 days (regardless of filters, to show a consistent trend graph)
        $salesTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $revenueForDay = Order::where('status', 'paid')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total_amount');

            $salesTrend[] = [
                'date' => $date->format('M d'),
                'revenue' => floatval($revenueForDay)
            ];
        }

        // 4. Retrieve Top Selling Products (by total quantity ordered)
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.quantity * COALESCE(order_items.unit_price, products.price)) as total_sales'))
            ->where('orders.status', '=', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        // 5. Retrieve Sales by Category
        $topCategories = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('categories.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.quantity * COALESCE(order_items.unit_price, products.price)) as total_sales'))
            ->where('orders.status', '=', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact(
            'totalOrdersCount',
            'pendingApprovalsCount',
            'totalRevenue',
            'averageOrderValue',
            'salesTrend',
            'topProducts',
            'topCategories',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export the filtered orders list to CSV
     */
    public function export(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : Carbon::now()->endOfDay();

        $query = Order::with('items.product')->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $filename = "sales_report_" . Carbon::now()->format('Y_m_d_His') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Order ID', 'Customer Name', 'Phone', 'Email', 'Status', 'Items (Name, Qty x Price)', 'Total Amount ($)', 'Created At', 'Last Updated'];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $itemsString = $order->items->map(function($item) {
                    $productName = $item->product ? str_replace(',', '', $item->product->name) : 'Unknown Product';
                    $price = number_format($item->unit_price ?? ($item->product ? $item->product->price : 0), 2);
                    return "{$productName} ({$item->quantity} x \${$price})";
                })->implode(' | ');

                $row = [
                    $order->id,
                    $order->customer_name ?? 'N/A',
                    $order->customer_phone,
                    $order->customer_email ?? 'N/A',
                    strtoupper($order->status),
                    $itemsString,
                    number_format($order->total_amount, 2),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->updated_at->format('Y-m-d H:i:s')
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
