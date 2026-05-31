<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class AdminController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $pendingApproval = Order::where('status', 'pending_approval')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $revenue = Order::where('status', 'paid')->sum('total_amount');

        return view('admin.dashboard', compact('totalOrders', 'pendingApproval', 'deliveredOrders', 'revenue'));
    }
}
