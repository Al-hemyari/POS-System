<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class OrderController extends Controller
{

    public function index(Request $request): View
    {
        $orders = Order::with('customer', 'order_details')->search(trim($request->search_query))->latest()->paginate(10);

        $totalCost = OrderDetail::sum('total_cost');
        $totalSold = OrderDetail::sum('total');
        $totalProfile = $totalSold - $totalCost;

        $todayQuery = OrderDetail::whereDay('created_at', Carbon::today());
        $totalOrdersToday =  Order::whereDay('created_at', Carbon::today())->count();
        $totalCostToday = $todayQuery->sum('total_cost');
        $totalSoldToday = $todayQuery->sum('total');
        $totalProfileToday = $totalSoldToday - $totalCostToday;


        $thisMonthQuery = OrderDetail::whereMonth('created_at', Carbon::now()->month);
        $totalOrdersThisMonth =  Order::whereMonth('created_at', Carbon::now()->month)->count();
        $totalCostThisMonth = $thisMonthQuery->sum('total_cost');
        $totalSoldThisMonth = $thisMonthQuery->sum('total');
        $totalProfileThisMonth = $totalSoldThisMonth - $totalCostThisMonth;

        $thisYearQuery = OrderDetail::whereYear('created_at', date('Y'));
        $totalOrdersThisYear =  Order::whereYear('created_at', date('Y'))->count();
        $totalCostThisYear = $thisYearQuery->sum('total_cost');
        $totalSoldThisYear = $thisYearQuery->sum('total');
        $totalProfileThisYear = $totalSoldThisYear - $totalCostThisYear;

        return view('orders.index', [
            'orders' => $orders,

            'totalOrders' => $orders->total(),
            'totalCost' => currency_format($totalCost),
            'totalSold' => currency_format($totalSold),
            'totalProfile' => currency_format($totalProfile),

            'totalOrdersToday' => $totalOrdersToday,
            'totalCostToday' => currency_format($totalCostToday),
            'totalSoldToday' => currency_format($totalSoldToday),
            'totalProfileToday' => currency_format($totalProfileToday),


            'totalOrdersThisMonth' => $totalOrdersThisMonth,
            'totalCostThisMonth' => currency_format($totalCostThisMonth),
            'totalSoldThisMonth' => currency_format($totalSoldThisMonth),
            'totalProfileThisMonth' => currency_format($totalProfileThisMonth),

            'totalOrdersThisYear' => $totalOrdersThisYear,
            'totalCostThisYear' => currency_format($totalCostThisYear),
            'totalSoldThisYear' => currency_format($totalSoldThisYear),
            'totalProfileThisYear' => currency_format($totalProfileThisYear),
        ]);
    }


    public function show(string $id): View
    {
        $order = Order::with('customer', 'order_details', 'order_details.product')->findOrFail($id);

        return view('orders.show', [
            'order' => $order
        ]);
    }

    public function receipt(string $id): View
    {
        $order = Order::with('customer', 'order_details', 'order_details.product')->findOrFail($id);

        return view('receipt.show', [
            'order' => $order
        ]);
    }




    public function edit(string $id): View
    {
        $order = Order::with('customer', 'order_details', 'order_details.product')->findOrFail($id);

        return view('orders.edit', [
            'order' => $order
        ]);
    }

    public function print(string $id): View
    {
        $order = Order::with('customer', 'order_details')->findOrFail($id);

        return view('orders.print', [
            'order' => $order
        ]);
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();
        return Redirect::back()->with("success", "Order has been deleted.");
    }

    public function showAnalytics(): View
    {

        $totalPerMonth = OrderDetail::select(
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
            DB::raw('SUM(total) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends(['display_total']);
            });



        $totalOrdersPerMonth = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
            DB::raw('count(*) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends([]);
            });

        $totalProfitMonth = OrderDetail::select(
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
            DB::raw('SUM(total) - SUM(total_cost) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends([]);
            });
        $totalCostMonth = OrderDetail::select(
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
            DB::raw('SUM(total_cost) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends([]);
            });
        return view('orders.analytics.show', [
            'totalPerMonth' => $totalPerMonth,
            'totalOrdersPerMonth' => $totalOrdersPerMonth,
            'totalProfitMonth' => $totalProfitMonth,
            'totalCostMonth' => $totalCostMonth,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'subtotal' => ['required', 'numeric'],
            'delivery_charge' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'tender_amount' => ['required', 'numeric'],
            'change' => ['required', 'numeric'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'remarks' => ['nullable', 'string', 'max:3000'],
        ]);

        $cart = (object) $request->cart;
        $order->subtotal = $request->subtotal;
        $order->delivery_charge = $request->delivery_charge;
        $order->discount = $request->discount;
        $order->total = $request->total;
        $order->tender_amount = $request->tender_amount;
        $order->change = $request->change;
        $order->tax_rate = $request->tax_rate;
        $order->remarks = $request->remarks;

        if ($request->has('customer')) {
            $customer = (object) $request->customer;
            $order->customer_id = $customer->id;
        } else {
            $order->customer_id = null;
        }

        $order->save();
        $order->order_details()->delete();

        foreach ($cart as $cartItem) {
            $cartItem = (object)$cartItem;
            $product = Product::where('id', $cartItem->id)->first(); // check item if valid
            if ($product) {
                $quantity = $cartItem->quantity > 0 ? $cartItem->quantity : 0; //prevent vegetive numbers
                $order_detail = new OrderDetail();
                $order_detail->quantity =  $quantity;
                $order_detail->price = $product->price;
                $order_detail->cost = $product->cost;
                $order_detail->total = $quantity * $product->price;
                $order_detail->total_cost = $quantity * $product->cost;
                $order_detail->product()->associate($product);
                $order_detail->order()->associate($order);
                $order_detail->save();
            }
        }
        return $this->jsonResponse();
    }



    public function store(Request $request)
    {

        $request->validate([
            'subtotal' => ['required', 'numeric'],
            'delivery_charge' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'tender_amount' => ['required', 'numeric'],
            'change' => ['required', 'numeric'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'remarks' => ['nullable', 'string', 'max:3000'],
        ]);



        $cart = (object) $request->cart;


        $order = new Order();
        $order->number = date("ynjhsi");

        $order->subtotal = $request->subtotal;
        $order->delivery_charge = $request->delivery_charge;
        $order->discount = $request->discount;
        $order->total = $request->total;
        $order->tender_amount = $request->tender_amount;
        $order->change = $request->change;
        $order->tax_rate = $request->tax_rate;
        $order->remarks = $request->remarks;

        if ($request->has('customer')) {
            $customer = (object) $request->customer;
            $order->customer_id = $customer->id;
        }
        if ($request->has('table')) {
            $table = (object) $request->table;
            $order->table_name = $table->name;
            $order->table_status = "pending";
        }
        $order->user_id = $request->user()->id;

        $order->save();

        foreach ($cart as $cartItem) {
            $cartItem = (object)$cartItem;
            $product = Product::where('id', $cartItem->id)->first(); // check item if valid
            if ($product) {
                $quantity = $cartItem->quantity > 0 ? $cartItem->quantity : 0; //prevent vegetive numbers
                $order_detail = new OrderDetail();
                $order_detail->quantity =  $quantity;
                $order_detail->price = $product->price;
                $order_detail->cost = $product->cost;
                $order_detail->total = $quantity * $product->price;
                $order_detail->total_cost = $quantity * $product->cost;
                $order_detail->product()->associate($product);
                $order_detail->order()->associate($order);
                $order_detail->save();
            }
        }



        return $this->jsonResponse([
            'order_number' => $order->number,
            'date_view' => $order->date_view,
            'time_view' => $order->time_view,
            'qr_code' => route('qr-code.show', $order->id)
        ]);
    }



    public function filter(Request $request): View
    {
        $from = is_null($request->from) ? now()->toDateString() : $request->from;
        $to = is_null($request->to) ? now()->toDateString() : $request->to;

        $customerName = $request->name;
        if (!is_null($customerName)) {
            $customer = Customer::search($customerName)->first();
            $orders = $customer->orders()->latest()->whereBetween('created_at', [$from, $to])->paginate(10);
            $totalCost =  $customer->order_details->whereBetween('created_at', [$from, $to])->sum('total_cost');
            $totalSold =  $customer->order_details->whereBetween('created_at', [$from, $to])->sum('total');


            $totalPerMonth = $customer->orders()->select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])
                ->get()->each(function ($order) {
                    $order->setAppends([]);
                });

            $totalOrdersPerMonth = $customer->orders()->select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
                DB::raw('count(*) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')
                ->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()->each(function ($order) {
                    $order->setAppends([]);
                });
        } else {
            $orders = Order::latest()->whereBetween('created_at', [$from, $to])->paginate(10);
            $totalCost =  OrderDetail::whereBetween('created_at', [$from, $to])->sum('total_cost');
            $totalSold =  OrderDetail::whereBetween('created_at', [$from, $to])->sum('total');

            $totalPerMonth = Order::select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')
                ->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()->each(function ($order) {
                    $order->setAppends(['display_total']);
                });

            $totalOrdersPerMonth = Order::select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as date'),
                DB::raw('count(*) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')
                ->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()->each(function ($order) {
                    $order->setAppends([]);
                });
        }
        $totalProfile = $totalSold - $totalCost;

        return view('orders.filter', [
            'orders' => $orders,
            'totalOrders' => $orders->total(),
            'totalCost' => currency_format($totalCost),
            'totalSold' => currency_format($totalSold),
            'totalProfile' => currency_format($totalProfile),

            'totalPerMonth' => $totalPerMonth,
            'totalOrdersPerMonth' => $totalOrdersPerMonth,
        ]);
    }
}
