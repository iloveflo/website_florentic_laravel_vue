<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo Cáo Kinh Doanh Florentic</title>
    <style>
        /* Cấu hình Font chữ hỗ trợ tiếng Việt */
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 11px; 
            color: #333; 
            line-height: 1.5;
        }

        /* HEADER BRANDING */
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000; /* Đường kẻ đậm chất thời trang */
            padding-bottom: 15px;
        }
        .brand-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px; /* Dãn chữ tạo cảm giác sang trọng */
            margin: 0;
            color: #000;
        }
        .brand-sub {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .report-title {
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* INFO SECTION */
        .meta-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .meta-table td {
            border: none;
            padding: 2px;
        }
        .meta-label {
            font-weight: bold;
            color: #000;
            width: 120px;
        }

        /* KPI CARDS (Dùng Table layout cho PDF an toàn hơn float) */
        .kpi-wrapper {
            width: 100%;
            margin-bottom: 30px;
        }
        .kpi-cell {
            width: 25%;
            padding: 0 5px;
        }
        .kpi-box {
            background-color: #f5f5f5; /* Xám nhẹ */
            border: 1px solid #e0e0e0;
            padding: 15px 10px;
            text-align: center;
        }
        .kpi-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        /* DATA TABLES */
        h3 {
            font-size: 13px;
            text-transform: uppercase;
            border-left: 3px solid #000;
            padding-left: 10px;
            margin-bottom: 15px;
            color: #000;
        }
        
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        table.data-table th { 
            border-bottom: 1px solid #000; 
            border-top: 1px solid #000; 
            padding: 10px 5px; 
            text-align: left; 
            font-size: 10px;
            text-transform: uppercase;
            background-color: #fff; /* Nền trắng sạch sẽ */
            color: #000;
        }
        table.data-table td { 
            border-bottom: 1px solid #eee; 
            padding: 10px 5px; 
            color: #444;
        }
        /* Zebra stripe nhẹ */
        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* STATUS BADGES - Style tối giản */
        .status-text {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-success { color: #10b981; }
        .text-warning { color: #d97706; }
        .text-danger { color: #ef4444; }
        .text-info { color: #3b82f6; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="brand-header">
        <h1 class="brand-name">Florentic</h1>
        <div class="brand-sub">Professional Fashion Store</div>
        <div class="report-title">Báo Cáo Hiệu Suất Kinh Doanh</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Thời gian:</td>
            <td>{{ $period_text }}</td>
            <td class="meta-label text-right">Ngày xuất:</td>
            <td class="text-right">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Người lập:</td>
            <td>{{ auth()->user()->full_name ?? 'Quản trị viên' }}</td>
            <td class="meta-label text-right">Bộ phận:</td>
            <td class="text-right">Quản lý</td>
        </tr>
    </table>

    

[Image of business report visualization]


    <table class="kpi-wrapper">
        <tr>
            <td class="kpi-cell">
                <div class="kpi-box">
                    <div class="kpi-title">Tổng Doanh Thu</div>
                    <div class="kpi-value">{{ number_format($overview['revenue']) }} ₫</div>
                </div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-box">
                    <div class="kpi-title">Đơn Hàng</div>
                    <div class="kpi-value">{{ number_format($overview['orderCount']) }}</div>
                </div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-box">
                    <div class="kpi-title">Khách Hàng Mới</div>
                    <div class="kpi-value">{{ number_format($overview['newCustomers']) }}</div>
                </div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-box">
                    <div class="kpi-title">AOV (Trung bình)</div>
                    <div class="kpi-value">{{ number_format($overview['averageOrderValue']) }} ₫</div>
                </div>
            </td>
        </tr>
    </table>

    <h3>Top Sản Phẩm Bán Chạy</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%;">Tên Sản Phẩm</th>
                <th class="text-right">Số Lượng</th>
                <th class="text-right">Doanh Thu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $product)
            <tr>
                <td>{{ $product['name'] }}</td>
                <td class="text-right">{{ $product['total_sold'] }}</td>
                <td class="text-right">{{ number_format($product['total_revenue']) }} ₫</td>
            </tr>
            @endforeach
            @if(count($topProducts) == 0)
                <tr><td colspan="3" class="text-center">Chưa có dữ liệu</td></tr>
            @endif
        </tbody>
    </table>

    <h3>Giao Dịch Gần Đây</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã Đơn</th>
                <th>Khách Hàng</th>
                <th>Ngày Tạo</th>
                <th>Trạng Thái</th>
                <th class="text-right">Tổng Tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
            @php
                // Dịch trạng thái và gán màu
                $statusMap = [
                    'pending' => ['text' => 'Chờ xử lý', 'class' => 'text-warning'],
                    'processing' => ['text' => 'Đang xử lý', 'class' => 'text-info'],
                    'shipping' => ['text' => 'Đang giao', 'class' => 'text-info'],
                    'completed' => ['text' => 'Hoàn thành', 'class' => 'text-success'],
                    'cancelled' => ['text' => 'Đã hủy', 'class' => 'text-danger'],
                    'returned' => ['text' => 'Hoàn trả', 'class' => 'text-danger'],
                ];
                $st = $statusMap[$order->order_status] ?? ['text' => $order->order_status, 'class' => ''];
            @endphp
            <tr>
                <td><strong>{{ $order->order_code ?? '#'.$order->id }}</strong></td>
                <td>{{ $order->user->full_name ?? 'Khách lẻ' }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>
                    <span class="status-text {{ $st['class'] }}">
                        {{ $st['text'] }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($order->total_amount) }} ₫</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Florentic Store - Website: www.florentic.com - Hotline: 1900 xxxx <br>
        Báo cáo này được tạo tự động bởi hệ thống quản trị.
    </div>
</body>
</html>