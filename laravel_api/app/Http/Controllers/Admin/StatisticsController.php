<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StatisticsController extends Controller
{

    private function getDateRange(Request $request)
    {
        $period = $request->query('period', 'this_month');
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'yesterday':
                return [Carbon::yesterday(), Carbon::yesterday()->endOfDay()];
            case 'this_week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    public function getOverview(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $cacheKey = 'stats_overview_' . $request->query('period', 'this_month') . '_' . $startDate->format('Ymd');

        // Cache 60 phút
        $data = Cache::remember($cacheKey, 60, function () use ($startDate, $endDate) {
            // Dùng subQuery để tối ưu count
            $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

            // Tính tổng doanh thu (chỉ đơn thành công)
            $revenue = (clone $baseQuery)->where('order_status', 'completed')->sum('total_amount');

            // Đếm tổng đơn
            $orderCount = (clone $baseQuery)->count();

            // Đếm khách mới
            $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();

            $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

            return [
                'revenue' => (float) $revenue,
                'orderCount' => (int) $orderCount,
                'newCustomers' => (int) $newCustomers,
                'averageOrderValue' => (float) $avgOrderValue,
            ];
        });

        return response()->json($data);
    }

    public function getRevenueOverTime(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
    }

    public function getSalesByCategory(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        // Join bảng để lấy tên category từ product_id trong order_items
        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed')
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue') // Thêm doanh thu theo danh mục
            )
            ->groupBy('categories.name')
            ->get();

        return response()->json($data);
    }

    public function getOrderStatusDistribution(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get();

        return response()->json($data);
    }

    public function getTopSellingProducts(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $limit = $request->input('limit', 5);

        // BƯỚC 1: Aggregate số liệu từ bảng order_items
        // Sử dụng cột 'subtotal' có sẵn trong order_items thay vì nhân tay
        $stats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed')
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue') // Cập nhật: Dùng cột subtotal
            )
            ->groupBy('order_items.product_id')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        // BƯỚC 2: Lấy thông tin chi tiết sản phẩm hiện tại (Ảnh, Tên mới nhất)
        $productIds = $stats->pluck('product_id')->toArray();

        // Eager load 'mainImage' để lấy ảnh đại diện
        $products = Product::with('mainImage')->whereIn('id', $productIds)->get()->keyBy('id');

        // BƯỚC 3: Merge dữ liệu
        $result = $stats->map(function ($item) use ($products) {
            $product = $products->get($item->product_id);

            // Nếu sản phẩm đã bị xóa cứng khỏi DB, ta bỏ qua hoặc hiển thị thông tin fallback
            if (!$product) {
                // Tùy chọn: Có thể return null để ẩn, hoặc trả về thông tin cũ nếu muốn
                return null;
            }

            return [
                'id' => $product->id,
                'name' => $product->name, // Lấy tên hiện tại
                'image' => $product->main_image_url, // Lấy ảnh hiện tại qua Accessor của Product
                'price' => $product->price, // Giá hiện tại
                'total_sold' => (int) $item->total_sold,
                'total_revenue' => (float) $item->total_revenue,
            ];
        })->filter()->values(); // Loại bỏ các giá trị null và reset keys

        return response()->json($result);
    }

    public function getRecentActivities()
    {
        // 1. Đơn hàng gần đây
        $recentOrders = Order::with('user:id,full_name') // Chỉ lấy id và tên user để nhẹ payload
            ->select('id', 'user_id', 'total_amount', 'order_status', 'created_at', 'order_code')
            ->latest()
            ->take(5)
            ->get();

        // 2. Sản phẩm sắp hết hàng (Low Stock)
        // Logic: Tổng tồn kho = Tổng quantity của tất cả Variants thuộc Product đó
        $lowStockProducts = Product::query()
            ->select('products.id', 'products.name', 'products.price', 'products.status')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('SUM(COALESCE(product_variants.quantity, 0)) as total_stock')
            ->groupBy('products.id', 'products.name', 'products.price', 'products.status')
            ->having('total_stock', '<', 10) // Ngưỡng cảnh báo: dưới 10
            ->orderBy('total_stock', 'asc')
            ->take(5)
            ->get();

        // Kích hoạt accessor main_image_url cho danh sách này
        $lowStockProducts->each(function ($p) {
            $p->append('main_image_url');
        });

        return response()->json([
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    public function exportReport(Request $request)
    {
        // 1. Lấy dữ liệu (Tái sử dụng logic query cũ)
        [$startDate, $endDate] = $this->getDateRange($request);
        $periodMap = [
            'today' => 'Hôm nay',
            'yesterday' => 'Hôm qua',
            'this_week' => 'Tuần này',
            'this_month' => 'Tháng này'
        ];
        $periodText = $periodMap[$request->query('period', 'this_month')] ?? 'Tùy chọn';

        // Query Overview
        $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'completed') // KHỚP VỚI LOGIC DB
            ->sum('total_amount');
        $orderCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

        $overview = [
            'revenue' => $revenue,
            'orderCount' => $orderCount,
            'newCustomers' => $newCustomers,
            'averageOrderValue' => $avgOrderValue
        ];

        // Query Top Products (Copy logic từ hàm getTopSellingProducts)
        $stats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed')
            ->select(
                'order_items.product_id',
                'order_items.product_name', // Lấy tên snapshot lúc mua
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        $topProducts = $stats->map(function ($item) {
            return [
                'name' => $item->product_name,
                'total_sold' => $item->total_sold,
                'total_revenue' => $item->total_revenue
            ];
        });

        // Query Recent Orders
        $recentOrders = Order::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 2. Load View và render PDF
        $pdf = Pdf::loadView('statistics_pdf', [
            'period_text' => $periodText,
            'overview' => $overview,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders
        ]);

        // 3. Trả về file stream để download
        return $pdf->download('Bao_Cao_Kinh_Doanh_' . now()->format('dmY') . '.pdf');
    }
}
