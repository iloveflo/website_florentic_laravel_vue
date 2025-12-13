<template>
  <div class="stats-dashboard">
    <div class="dashboard-header">
      <div class="header-content">
        <h1>Thống kê & Báo cáo</h1>
        <p>Tổng quan tình hình kinh doanh của cửa hàng</p>
      </div>
      <div class="header-actions">
        <div class="date-filter">
          <label>Thời gian:</label>
          <select v-model="period" @change="fetchData">
            <option value="today">Hôm nay</option>
            <option value="yesterday">Hôm qua</option>
            <option value="this_week">Tuần này</option>
            <option value="this_month">Tháng này</option>
          </select>
        </div>
        <button class="btn-export" @click="exportReport" :disabled="loading">
          <i class="fas fa-download"></i> Xuất Báo Cáo
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <div v-else class="dashboard-content">
      <div class="kpi-grid">
        <div class="kpi-card revenue">
          <div class="kpi-icon"><i class="fas fa-coins"></i></div>
          <div class="kpi-info">
            <h3>Doanh Thu</h3>
            <p class="kpi-value">{{ formatCurrency(stats.overview.revenue) }}</p>
            <span class="kpi-sub">Tổng doanh thu (đã giao)</span>
          </div>
        </div>
        <div class="kpi-card orders">
          <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
          <div class="kpi-info">
            <h3>Đơn Hàng</h3>
            <p class="kpi-value">{{ stats.overview.orderCount }}</p>
            <span class="kpi-sub">Đơn hàng mới</span>
          </div>
        </div>
        <div class="kpi-card customers">
          <div class="kpi-icon"><i class="fas fa-users"></i></div>
          <div class="kpi-info">
            <h3>Khách Hàng</h3>
            <p class="kpi-value">{{ stats.overview.newCustomers }}</p>
            <span class="kpi-sub">Khách hàng mới</span>
          </div>
        </div>
        <div class="kpi-card avg-order">
          <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
          <div class="kpi-info">
            <h3>TB Đơn Hàng</h3>
            <p class="kpi-value">{{ formatCurrency(stats.overview.averageOrderValue) }}</p>
            <span class="kpi-sub">Giá trị trung bình</span>
          </div>
        </div>
      </div>

      <div class="charts-grid-top">
        <div class="chart-card main-chart">
          <h3>Biểu Đồ Doanh Thu</h3>
          <div class="chart-wrapper">
            <Line v-if="chartData.revenue" :data="chartData.revenue" :options="chartOptions.revenue" />
          </div>
        </div>
        <div class="chart-card side-chart">
          <h3>Trạng Thái Đơn Hàng</h3>
          <div class="chart-wrapper">
            <Doughnut v-if="chartData.status" :data="chartData.status" :options="chartOptions.status" />
          </div>
        </div>
      </div>

      <div class="charts-grid-bottom">
        <div class="chart-card">
          <h3>Doanh Thu Theo Danh Mục</h3>
           <div class="chart-wrapper">
            <Bar v-if="chartData.category" :data="chartData.category" :options="chartOptions.category" />
          </div>
        </div>
      </div>

      <div class="data-tables-grid">
        <div class="table-card">
          <div class="card-header">
            <h3>Sản Phẩm Bán Chạy</h3>
          </div>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Sản phẩm</th>
                  <th>Giá hiện tại</th>
                  <th>Đã bán</th>
                  <th>Tổng doanh thu</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in stats.topProducts" :key="product.id">
                  <td class="product-cell">
                    <img :src="product.image || '/images/placeholder.png'" alt="img" class="product-thumb">
                    <div class="product-name-col">
                        <span>{{ product.name }}</span>
                    </div>
                  </td>
                  <td>{{ formatCurrency(product.price) }}</td>
                  <td>{{ product.total_sold }}</td>
                  <td>{{ formatCurrency(product.total_revenue) }}</td>
                </tr>
                <tr v-if="stats.topProducts.length === 0">
                  <td colspan="4" class="text-center">Chưa có dữ liệu</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="table-card">
          <div class="card-header">
            <h3>Cảnh Báo & Hoạt Động</h3>
          </div>
          <div class="activity-list">
             <div v-if="stats.lowStock.length > 0" class="alert-section">
               <h4><i class="fas fa-exclamation-triangle text-warning"></i> Sắp hết hàng (< 10)</h4>
               <ul>
                 <li v-for="item in stats.lowStock" :key="'stock-'+item.id">
                   <span>{{ item.name }}</span>
                   <span class="badge red">Còn: {{ item.total_stock }}</span>
                 </li>
               </ul>
             </div>
             <div v-else class="alert-section">
                <p class="text-muted"><i class="fas fa-check-circle text-success"></i> Kho hàng ổn định</p>
             </div>
             
             <div class="recent-orders-section">
               <h4><i class="fas fa-clock text-info"></i> Đơn hàng mới nhất</h4>
               <ul>
                 <li v-for="order in stats.recentOrders" :key="'order-'+order.id" class="order-item">
                    <div class="order-info">
                      <strong>#{{ order.order_code || order.id }}</strong> - {{ order.user ? order.user.full_name : 'Khách vãng lai' }}
                    </div>
                    <div class="order-meta">
                      {{ formatCurrency(order.total_amount) }}
                      <span :class="['status-badge', order.order_status]">{{ translateStatus(order.order_status) }}</span>
                    </div>
                 </li>
               </ul>
             </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line, Bar, Doughnut } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

const loading = ref(true);
const period = ref('this_month');

const stats = reactive({
  overview: {
    revenue: 0,
    orderCount: 0,
    newCustomers: 0,
    averageOrderValue: 0
  },
  topProducts: [],
  recentOrders: [],
  lowStock: []
});

const chartData = reactive({
  revenue: null,
  status: null,
  category: null
});

const chartOptions = {
  revenue: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      title: { display: true, text: 'Doanh thu theo thời gian' }
    },
    scales: {
        y: { beginAtZero: true }
    }
  },
  status: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' }
    }
  },
  category: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
       legend: { display: false }
    }
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

// Hàm dịch trạng thái sang tiếng Việt
const translateStatus = (status) => {
    const map = {
        'pending': 'Chờ xử lý',
        'processing': 'Đang xử lý',
        'shipping': 'Đang giao',
        'delivered': 'Đã giao',
        'cancelled': 'Đã hủy',
        'returned': 'Hoàn trả'
    };
    return map[status] || status;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const params = { period: period.value };
    
    const [overviewRes, revenueRes, statusRes, categoryRes, topRes, recentRes] = await Promise.all([
      axios.get('/admin/statistics/overview', { params }), // Lưu ý: Thêm prefix /api nếu cấu hình Laravel mặc định
      axios.get('/admin/statistics/revenue-over-time', { params }),
      axios.get('/admin/statistics/order-status-distribution', { params }),
      axios.get('/admin/statistics/sales-by-category', { params }),
      axios.get('/admin/statistics/top-selling-products', { params }),
      axios.get('/admin/statistics/recent-activities') 
    ]);

    stats.overview = overviewRes.data;
    stats.topProducts = topRes.data;
    stats.recentOrders = recentRes.data.recentOrders;
    stats.lowStock = recentRes.data.lowStockProducts;

    // 1. Revenue Chart
    chartData.revenue = {
      labels: revenueRes.data.map(item => item.date), // Controller trả về 'date'
      datasets: [{
        label: 'Doanh thu',
        backgroundColor: '#10b981',
        borderColor: '#10b981',
        data: revenueRes.data.map(item => item.total), // Controller trả về 'total'
        tension: 0.3
      }]
    };

    // 2. Status Chart
    const statusColors = {
        'pending': '#fbbf24', 
        'processing': '#3b82f6', 
        'shipping': '#8b5cf6', 
        'delivered': '#10b981', 
        'cancelled': '#ef4444',
        'returned': '#6b7280'
    };
    
    chartData.status = {
      // SỬA: item.order_status
      labels: statusRes.data.map(item => translateStatus(item.order_status)), 
      datasets: [{
        backgroundColor: statusRes.data.map(item => statusColors[item.order_status] || '#ccc'),
        // SỬA: item.order_status
        data: statusRes.data.map(item => item.count)
      }]
    };

    // 3. Category Chart
    chartData.category = {
      labels: categoryRes.data.map(item => item.name),
      datasets: [{
        label: 'Số lượng bán',
        backgroundColor: '#6366f1',
        data: categoryRes.data.map(item => item.total_quantity)
      }]
    };

  } catch (error) {
    console.error("Lỗi tải dữ liệu:", error);
  } finally {
    loading.value = false;
  }
};

const exportReport = async () => {
  loading.value = true;
  try {
    const params = { period: period.value };
    
    // Gọi API với responseType là 'blob' (quan trọng để tải file)
    const response = await axios.get('/admin/statistics/export', { 
        params,
        responseType: 'blob' 
    });

    // Tạo URL ảo cho file blob
    const url = window.URL.createObjectURL(new Blob([response.data]));
    
    // Tạo thẻ a ảo để kích hoạt download
    const link = document.createElement('a');
    link.href = url;
    
    // Đặt tên file (lấy từ header server hoặc đặt cứng)
    const fileName = `Bao_Cao_${period.value}_${new Date().toISOString().slice(0,10)}.pdf`;
    link.setAttribute('download', fileName);
    
    document.body.appendChild(link);
    link.click();
    
    // Dọn dẹp
    link.remove();
    window.URL.revokeObjectURL(url);
    
    console.log("Xuất báo cáo thành công!");

  } catch (error) {
    console.error("Lỗi xuất báo cáo:", error);
    alert("Có lỗi xảy ra khi xuất báo cáo. Vui lòng thử lại.");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.stats-dashboard {
  padding: 24px;
  background-color: #f3f4f6; /* Light gray bg */
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.dashboard-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.dashboard-header p {
  color: #6b7280;
  margin-top: 4px;
}

.header-actions {
  display: flex;
  gap: 16px;
  align-items: center;
}

.date-filter select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  background: white;
  margin-left: 8px;
  font-size: 14px;
  cursor: pointer;
}

.btn-export {
  background: #111;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-export:hover {
  background: #333;
}

/* KPI Cards */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.kpi-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: transform 0.2s;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.kpi-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.kpi-card.revenue .kpi-icon { background: #ecfdf5; color: #10b981; }
.kpi-card.orders .kpi-icon { background: #eff6ff; color: #3b82f6; }
.kpi-card.customers .kpi-icon { background: #f5f3ff; color: #8b5cf6; }
.kpi-card.avg-order .kpi-icon { background: #fff7ed; color: #f97316; }

.kpi-info h3 { font-size: 14px; color: #6b7280; font-weight: 500; margin: 0; }
.kpi-value { font-size: 24px; font-weight: 700; color: #111; margin: 4px 0; }
.kpi-sub { font-size: 12px; color: #9ca3af; }

/* Charts */
.charts-grid-top {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.charts-grid-bottom {
  margin-bottom: 32px;
}

.chart-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.chart-card h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #374151;
}

.chart-wrapper {
  position: relative;
  height: 300px;
  width: 100%;
}

/* Data Tables */
.data-tables-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.table-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 12px 0;
    font-size: 13px;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
}

td {
    padding: 16px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #374151;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.product-thumb {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
}

.activity-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.activity-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.alert-section { margin-bottom: 24px; }
.alert-section h4 { color: #d97706; margin-bottom: 12px; font-size: 14px; }
.recent-orders-section h4 { color: #2563eb; margin-bottom: 12px; font-size: 14px; }

.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}
.badge.red { background: #fee2e2; color: #ef4444; }

.status-badge {
    padding: 2px 8px;
    border-radius: 99px;
    font-size: 11px;
    text-transform: capitalize;
}
.status-badge.delivered { background: #d1fae5; color: #059669; }
.status-badge.pending { background: #fef3c7; color: #d97706; }
.status-badge.cancelled { background: #fee2e2; color: #dc2626; }
.status-badge.processing { background: #dbeafe; color: #2563eb; }

/* Responsive */
@media (max-width: 1024px) {
  .charts-grid-top, .data-tables-grid {
    grid-template-columns: 1fr;
  }
}

.loading-state {
    text-align: center;
    padding: 100px 0;
}
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>