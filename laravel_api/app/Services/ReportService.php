<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * ===================================================================
 * REPORT SERVICE - DỊCH VỤ CHÍNH XỬ LÝ BÁO CÁO DOANH THU FLORENTIC
 * ===================================================================
 * 
 * - Tất cả logic báo cáo nằm ở đây → Controller cực mỏng
 * - Validate đầu vào ngay từ service (defense in depth)
 * - Comment tiếng Việt chi tiết từng dòng
 * - Dễ viết unit test (không phụ thuộc request)
 */
class ReportService
{
    /**
     * Validate khoảng thời gian
     */
    private function validateDateRange(?Carbon $start, ?Carbon $end): void
    {
        if ($start && $end && $start->greaterThan($end)) {
            throw new InvalidArgumentException('Ngày bắt đầu không được lớn hơn ngày kết thúc');
        }

        if ($start && $start->greaterThan(now())) {
            throw new InvalidArgumentException('Ngày bắt đầu không được lớn hơn hôm nay');
        }

        if ($end && $end->greaterThan(now())) {
            throw new InvalidArgumentException('Ngày kết thúc không được lớn hơn hôm nay');
        }
    }

    /**
     * 1. Dashboard Stats - Tổng quan doanh thu
     */
    public function getDashboardStats(?Carbon $start = null, ?Carbon $end = null): array
    {
        $this->validateDateRange($start, $end);

        $start = $start?->startOfDay() ?? now()->subDays(30)->startOfDay();
        $end   = $end?->endOfDay() ?? now()->endOfDay();

        $completedOrders = Order::where('order_status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        $revenue     = $completedOrders->sum('total_amount');
        $ordersCount = $completedOrders->count();
        $itemsSold   = OrderItem::whereHas('order', fn($q) => $q
            ->where('order_status', 'completed')
            ->whereBetween('created_at', [$start, $end])
        )->sum('quantity');

        $avgOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0;

        // Tính tăng trưởng so với kỳ trước
        $daysDiff  = $end->diffInDays($start);
        $prevStart = $start->clone()->subDays($daysDiff + 1);
        $prevEnd   = $start->clone()->subSecond();

        $prevRevenue = Order::where('order_status', 'completed')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total_amount');

        $growth = $prevRevenue > 0
            ? (($revenue - $prevRevenue) / $prevRevenue) * 100
            : ($revenue > 0 ? 100 : 0);

        return [
            'revenue'         => round($revenue, 2),
            'orders'          => $ordersCount,
            'items_sold'      => (int) $itemsSold,
            'avg_order_value' => round($avgOrderValue, 2),
            'revenue_growth'  => round($growth, 1) . '%',
            'growth_positive' => $growth >= 0,
            'period'          => $start->format('d/m/Y') . ' → ' . $end->format('d/m/Y'),
        ];
    }

    /**
     * 2. Xu hướng doanh thu theo kỳ
     */
    public function getSalesTrend(string $period = 'day', int $days = 90): array
    {
        if (!in_array($period, ['day', 'week', 'month'])) {
            throw new InvalidArgumentException('Kỳ báo cáo chỉ hỗ trợ: day, week, month');
        }

        if ($days < 1 || $days > 365) {
            throw new InvalidArgumentException('Số ngày phải từ 1 đến 365');
        }

        $start = now()->subDays($days);
        $format = $period === 'month' ? '%Y-%m' : ($period === 'week' ? '%Y-%u' : '%Y-%m-%d');

        $raw = DB::table('orders')
            ->where('order_status', 'completed')
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, ?) as date_key, SUM(total_amount) as revenue", [$format])
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->pluck('revenue', 'date_key');

        $labels = [];
        $data   = [];
        $current = $start->clone()->startOf($period === 'month' ? 'month' : ($period === 'week' ? 'week' : 'day'));

        while ($current <= now()) {
            $key = $current->format($period === 'month' ? 'Y-m' : ($period === 'week' ? 'Y-W' : 'Y-m-d'));
            $labels[] = $current->translatedFormat(
                $period === 'month' ? 'M Y' : 
                ($period === 'week' ? '\Tuần W, Y' : 'd/m')
            );
            $data[] = $raw[$key] ?? 0;

            $current = match($period) {
                'month' => $current->addMonthNoOverflow(),
                'week'  => $current->addWeek(),
                default => $current->addDay(),
            };
        }

        return compact('labels', 'data');
    }

    /**
     * 3. Top Products - Sản phẩm bán chạy nhất
     */
    public function getTopProducts(int $limit = 10): array
    {
        return OrderItem::selectRaw('product_id, product_name, SUM(quantity) as units_sold, SUM(subtotal) as revenue')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product_name,
                    'units_sold' => (int) $item->units_sold,
                    'revenue' => round($item->revenue, 2),
                ];
            })
            ->toArray();
    }

    /**
     * 4. Sales by Category - Doanh thu theo danh mục
     */
    public function getSalesByCategory(): array
    {
        $totalRevenue = Order::where('order_status', 'completed')->sum('total_amount');

        if ($totalRevenue == 0) {
            return [];
        }

        return Category::selectRaw('categories.id, categories.name, SUM(order_items.subtotal) as revenue')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($category) use ($totalRevenue) {
                $percentage = ($category->revenue / $totalRevenue) * 100;
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'revenue' => round($category->revenue, 2),
                    'percentage' => round($percentage, 2),
                ];
            })
            ->toArray();
    }
}