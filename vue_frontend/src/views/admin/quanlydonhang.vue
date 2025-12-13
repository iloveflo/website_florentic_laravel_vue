<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div v-if="currentTab === 'orders'">
      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="md:col-span-2">
            <input v-model="filters.search" @keyup.enter="fetchOrders" type="text" placeholder="Tìm theo Mã đơn, Tên, SĐT..." class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
          </div>
          <select v-model="filters.status" @change="fetchOrders" class="border p-2 rounded">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ xác nhận</option>
            <option value="confirmed">Đã xác nhận</option>
            <option value="shipping">Đang giao</option>
            <option value="completed">Hoàn thành</option>
            <option value="cancelled">Đã hủy</option>
          </select>
          <select v-model="filters.payment_method" @change="fetchOrders" class="border p-2 rounded">
            <option value="">Phương thức thanh toán</option>
            <option value="cod">COD</option>
            <option value="vnpay">VNPay</option>
            <option value="momo">MoMo</option>
          </select>
          <button @click="fetchOrders" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Lọc dữ liệu</button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
            <tr>
              <th class="p-4">Mã đơn</th>
              <th class="p-4">Khách hàng</th>
              <th class="p-4">Ngày đặt</th>
              <th class="p-4">Tổng tiền</th>
              <th class="p-4">Trạng thái</th>
              <th class="p-4 text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="order in orders.data" :key="order.id" class="border-b hover:bg-gray-50">
              <td class="p-4 font-bold text-blue-600">#{{ order.order_code }}</td>
              <td class="p-4">
                <div class="font-medium">{{ order.full_name }}</div>
                <div class="text-gray-500 text-xs">{{ order.phone }}</div>
              </td>
              <td class="p-4">{{ formatDate(order.created_at) }}</td>
              <td class="p-4 font-bold">{{ formatCurrency(order.total_amount) }}</td>
              <td class="p-4">
                <span :class="statusColor(order.order_status)" class="px-2 py-1 rounded-full text-xs font-semibold">
                  {{ statusText(order.order_status) }}
                </span>
              </td>
              <td class="p-4 text-center">
                <button @click="openDetail(order)" class="text-blue-600 hover:text-blue-800 mr-2" title="Xem nhanh">
                  <i class="fas fa-eye"></i> Xem chi tiết
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="p-4 border-t flex justify-between items-center">
          <!-- Phân trang -->
          <div class="flex items-center gap-1">
            <!-- Nút Previous -->
            <button 
              class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" 
              :disabled="!orders.prev_page_url" 
              @click="changePage(orders.current_page - 1)">
              <
            </button>
            
            <!-- Nút trang đầu -->
            <button 
              v-if="orders.current_page > 3"
              class="px-3 py-1 border rounded hover:bg-gray-100"
              @click="changePage(1)">
              1
            </button>
            <span v-if="orders.current_page > 3" class="px-2">...</span>
            
            <!-- Các trang xung quanh trang hiện tại -->
            <button 
              v-for="page in visiblePages" 
              :key="page"
              class="px-3 py-1 border rounded hover:bg-gray-100"
              :class="page === orders.current_page ? 'bg-blue-500 text-white font-bold' : ''"
              @click="changePage(page)">
              {{ page }}
            </button>
            
            <!-- Nút trang cuối -->
            <span v-if="orders.current_page < orders.last_page - 2" class="px-2">...</span>
            <button 
              v-if="orders.current_page < orders.last_page - 2"
              class="px-3 py-1 border rounded hover:bg-gray-100"
              @click="changePage(orders.last_page)">
              {{ orders.last_page }}
            </button>
            
            <!-- Nút Next -->
            <button 
              class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" 
              :disabled="!orders.next_page_url" 
              @click="changePage(orders.current_page + 1)">
              >
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="currentTab === 'abandoned'">
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
          <h3 class="font-bold text-lg">Danh sách khách rớt đơn</h3>
          <button class="text-sm bg-green-600 text-white px-3 py-1 rounded">Gửi Email Marketing All</button>
        </div>
        <table class="w-full text-left">
          <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
             <tr>
               <th class="p-4">Session/User</th>
               <th class="p-4">Số lượng SP</th>
               <th class="p-4">Giá trị ước tính</th>
               <th class="p-4">Cập nhật cuối</th>
               <th class="p-4 text-center">Hành động</th>
             </tr>
          </thead>
          <tbody>
            <tr v-for="cart in abandonedCarts.data" :key="cart.session_id" class="border-b hover:bg-gray-50">
              <td class="p-4">
                 <div v-if="cart.user">{{ cart.user.full_name }}</div>
                 <div v-else class="text-gray-500 italic">Khách vãng lai (Guest)</div>
                 <div class="text-xs text-gray-400">{{ cart.session_id.substring(0,10) }}...</div>
              </td>
              <td class="p-4">{{ cart.item_count }} sản phẩm</td>
              <td class="p-4 font-bold text-red-500">{{ formatCurrency(cart.total_value) }}</td>
              <td class="p-4">{{ formatDate(cart.last_activity) }}</td>
              <td class="p-4 text-center">
                 <button class="bg-indigo-500 text-white px-3 py-1 rounded text-xs hover:bg-indigo-600">Gửi Mail nhắc</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal && selectedOrder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto flex flex-col">
        
        <div class="flex justify-between items-center p-4 border-b bg-gray-50">
          <h3 class="text-xl font-bold">Chi tiết đơn hàng #{{ selectedOrder.order_code }}</h3>
          <button @click="closeModal" class="text-gray-500 hover:text-red-500 text-2xl">&times;</button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <div class="md:col-span-2 space-y-4">
            <h4 class="font-semibold text-gray-700 border-b pb-2">Sản phẩm</h4>
            <div v-for="item in selectedOrder.order_items || []" :key="item.id" class="flex items-center gap-4 border p-2 rounded-lg">
              <img :src="item.product_image_url || '/placeholder.png'" 
                  class="w-16 h-16 object-cover rounded border">
              <div class="flex-1">
                <div class="font-bold text-sm">{{ item.product_name }}</div>
                <div class="text-xs text-gray-500">Phân loại: {{ item.size || 'N/A' }} / {{ item.color || 'N/A' }}</div>
                <div class="text-xs text-gray-500">SKU: {{ item.sku || 'N/A' }}</div>
              </div>
              <div class="text-right">
                <div class="font-bold">{{ formatCurrency(item.price) }}</div>
                <div class="text-sm text-gray-500">x {{ item.quantity }}</div>
              </div>
            </div>

            <div class="bg-gray-50 p-4 rounded text-right space-y-2 text-sm">
               <div class="flex justify-between"><span>Tạm tính:</span> <span>{{ formatCurrency(selectedOrder.subtotal) }}</span></div>
               <div class="flex justify-between"><span>Phí vận chuyển:</span> <span>{{ formatCurrency(selectedOrder.shipping_fee) }}</span></div>
               <div class="flex justify-between text-green-600"><span>Giảm giá:</span> <span>- {{ formatCurrency(selectedOrder.discount_amount) }}</span></div>
               <div class="flex justify-between font-bold text-lg border-t pt-2"><span>Tổng cộng:</span> <span class="text-red-600">{{ formatCurrency(selectedOrder.total_amount) }}</span></div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="bg-blue-50 p-4 rounded border border-blue-100">
               <label class="block text-sm font-bold mb-2">Cập nhật trạng thái</label>
               <select v-model="selectedOrder.order_status" class="w-full border p-2 rounded mb-2">
                 <option value="pending">Chờ xác nhận</option>
                 <option value="confirmed">Đã xác nhận (Đóng gói)</option>
                 <option value="shipping">Đang giao hàng</option>
                 <option value="completed">Hoàn thành</option>
                 <option value="cancelled">Hủy đơn</option>
               </select>
               <button @click="updateStatus" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700 transition">Lưu trạng thái</button>
            </div>

            <div>
              <h4 class="font-semibold text-gray-700 border-b pb-2 mb-2">Khách hàng</h4>
              <p class="font-bold">{{ selectedOrder.full_name }}</p>
              <p class="text-sm"><i class="fas fa-phone mr-1"></i> <a :href="'tel:'+selectedOrder.phone" class="text-blue-600">{{ selectedOrder.phone }}</a></p>
              <p class="text-sm"><i class="fas fa-envelope mr-1"></i> {{ selectedOrder.email }}</p>
              <p class="text-sm mt-2 font-semibold">Địa chỉ giao hàng:</p>
              <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ selectedOrder.address }}</p>
            </div>

            <div>
               <div class="text-sm text-yellow-700 bg-yellow-50 p-2 rounded mb-2" v-if="selectedOrder.note">
                  <strong>Khách note:</strong> {{ selectedOrder.note }}
               </div>
               <div class="flex gap-2">
                 <button @click="printInvoice" class="flex-1 border border-gray-300 py-1 rounded hover:bg-gray-100"><i class="fas fa-print"></i> In Hóa đơn</button>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted,computed, reactive } from 'vue';
import axios from 'axios';

// State Data
const currentTab = ref('orders');
const orders = ref({});
const abandonedCarts = ref({});
const showModal = ref(false);
const selectedOrder = ref(null);

// Filters
const filters = reactive({
  search: '',
  status: '',
  payment_method: '',
  date_from: '',
  date_to: ''
});

// 1. Fetch Danh sách đơn hàng
// Sửa lại hàm fetchOrders để xử lý trường hợp tham số là Event
const fetchOrders = async (url) => {
  const token = localStorage.getItem('token'); // token login
  try {
    // Gửi request kèm theo các bộ lọc (filters)
    const response = await axios.get('/admin/orders', {
      params: filters,
      headers: {
        Authorization: token ? `Bearer ${token}` : undefined,
      },
    });
    orders.value = response.data;
  } catch (error) {
    console.error("Lỗi tải đơn hàng:", error.response?.status, error.response?.data);
  }
};

// 2. Fetch Giỏ hàng bỏ quên
const fetchAbandonedCarts = async () => {
    try {
        const response = await axios.get('/abandoned-carts');
        abandonedCarts.value = response.data;
    } catch (error) {
        console.error("Lỗi:", error);
    }
}

// 3. Xử lý Modal & Chi tiết
const openDetail = async (order) => {
  try {
    const response = await axios.get(`/admin/orders/${order.order_code}`);
    selectedOrder.value = response.data;
    showModal.value = true;
  } catch (error) {
    console.error("Lỗi tải chi tiết đơn hàng:", error);
    console.error("Response:", error.response?.data);
    alert("Không thể tải chi tiết đơn hàng!");
  }
};


const closeModal = () => {
  showModal.value = false;
  selectedOrder.value = null;
};

// 4. Cập nhật trạng thái
const updateStatus = async () => {
  if (!selectedOrder.value) return;
  
  try {
    // SỬA: Dùng order_code thay vì id
    // URL sẽ thành: /api/admin/orders/ORD-2023-001/status
    const url = `/admin/${selectedOrder.value.order_code}/status`;

    await axios.post(url, {
      order_status: selectedOrder.value.order_status,
      _method: 'PUT'
    });

    alert('Cập nhật trạng thái thành công!');
    fetchOrders(); 
    closeModal();  
  } catch (error) {
    console.error(error);
    alert('Lỗi cập nhật!');
  }
};

// Hàm xử lý in hóa đơn
const printInvoice = () => {
  if (!selectedOrder.value) return;
  
  // 1. Tạo nội dung HTML cho hóa đơn
  const order = selectedOrder.value;
  const printWindow = window.open('', '_blank');
  
  // Tính toán ngày tháng
  const date = new Date(order.created_at);
  const day = date.getDate();
  const month = date.getMonth() + 1;
  const year = date.getFullYear();

  // HTML Template Hóa đơn
  const invoiceHTML = `
    <html>
      <head>
        <title>Hóa đơn #${order.order_code}</title>
        <script src="https://cdn.tailwindcss.com"><\/script>
        <style>
            @media print {
                @page { margin: 0; size: A4; }
                body { margin: 1.6cm; -webkit-print-color-adjust: exact; }
            }
            body { font-family: 'Times New Roman', serif; }
        </style>
      </head>
      <body class="bg-white text-black text-sm">
        
        <div class="flex justify-between items-start border-b pb-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold uppercase tracking-wide mb-1">FLORENTIC</h1>
                <p class="text-gray-600">Thời trang & Phong cách</p>
                <p class="mt-2">Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM</p>
                <p>Hotline: 0909.123.456 - Email: support@florentic.com</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800">HÓA ĐƠN</h2>
                <p class="font-bold text-red-600 mt-1">#${order.order_code}</p>
                <p class="text-gray-500 italic">Ngày ${day} tháng ${month} năm ${year}</p>
            </div>
        </div>

        <div class="mb-8 bg-gray-50 p-4 rounded border border-gray-200">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="font-bold text-gray-700 uppercase text-xs mb-1">Khách hàng:</p>
                    <p class="text-lg font-bold">${order.full_name}</p>
                    <p>${order.phone}</p>
                    <p>${order.email}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-700 uppercase text-xs mb-1">Địa chỉ giao hàng:</p>
                    <p class="max-w-xs ml-auto">${order.address}</p>
                    <p class="mt-2 text-xs text-gray-500">Phương thức: ${order.payment_method.toUpperCase()}</p>
                </div>
            </div>
        </div>

        <table class="w-full mb-8 border-collapse">
            <thead>
                <tr class="bg-gray-800 text-white uppercase text-xs tracking-wider">
                    <th class="py-3 px-2 text-left">STT</th>
                    <th class="py-3 px-2 text-left">Sản phẩm</th>
                    <th class="py-3 px-2 text-center">Phân loại</th>
                    <th class="py-3 px-2 text-center">SL</th>
                    <th class="py-3 px-2 text-right">Đơn giá</th>
                    <th class="py-3 px-2 text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
            ${(order.order_items || []).map((item, index) => `
                <tr class="border-b border-gray-200">
                    <td class="py-3 px-2 text-center">${index + 1}</td>
                    <td class="py-3 px-2 font-semibold">
                        ${item.product_name}
                        <div class="text-xs text-gray-500 italic">${item.product?.sku || ''}</div>
                    </td>
                    <td class="py-3 px-2 text-center text-xs text-gray-600">${item.size} / ${item.color}</td>
                    <td class="py-3 px-2 text-center font-bold">${item.quantity}</td>
                    <td class="py-3 px-2 text-right">${formatCurrency(item.price)}</td>
                    <td class="py-3 px-2 text-right font-bold">${formatCurrency(item.price * item.quantity)}</td>
                </tr>
            `).join('')}
            </tbody>
        </table>

        <div class="flex justify-end mb-10">
            <div class="w-1/2">
                <div class="flex justify-between py-1 px-4 border-b border-gray-100">
                    <span class="text-gray-600">Tạm tính:</span>
                    <span class="font-bold">${formatCurrency(order.subtotal)}</span>
                </div>
                <div class="flex justify-between py-1 px-4 border-b border-gray-100">
                    <span class="text-gray-600">Phí vận chuyển:</span>
                    <span>${formatCurrency(order.shipping_fee)}</span>
                </div>
                ${order.discount_amount > 0 ? `
                <div class="flex justify-between py-1 px-4 border-b border-gray-100 text-green-600">
                    <span>Giảm giá (Voucher):</span>
                    <span>- ${formatCurrency(order.discount_amount)}</span>
                </div>
                ` : ''}
                <div class="flex justify-between py-3 px-4 bg-gray-800 text-white mt-2 rounded">
                    <span class="font-bold uppercase">Tổng cộng:</span>
                    <span class="font-bold text-lg">${formatCurrency(order.total_amount)}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 text-center mt-12">
            <div>
                <p class="font-bold text-xs uppercase text-gray-500">Người lập phiếu</p>
                <p class="italic text-gray-400 mt-16">(Ký, họ tên)</p>
            </div>
            <div>
                <p class="font-bold text-xs uppercase text-gray-500">Người nhận hàng</p>
                <p class="text-xs italic text-gray-400">Kiểm tra kỹ hàng hóa trước khi nhận</p>
                <p class="italic text-gray-400 mt-12">(Ký, họ tên)</p>
            </div>
        </div>
        
        <div class="text-center mt-12 text-xs text-gray-400 italic">
            Cảm ơn quý khách đã mua sắm tại Florentic!
        </div>

      </body>
    </html>
  `;

  // 2. Ghi nội dung vào cửa sổ mới và gọi lệnh in
  printWindow.document.write(invoiceHTML);
  printWindow.document.close();
  
  // Đợi hình ảnh/CSS tải xong rồi mới in
  setTimeout(() => {
      printWindow.focus();
      printWindow.print();
      // printWindow.close(); // Bỏ comment nếu muốn tự đóng sau khi in
  }, 500);
};

// 5. Utilities Formatter
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
  if(!dateString) return '';
  return new Date(dateString).toLocaleString('vi-VN');
};

const statusColor = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    shipping: 'bg-purple-100 text-purple-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return map[status] || 'bg-gray-100';
};

const statusText = (status) => {
  const map = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    shipping: 'Đang giao',
    completed: 'Hoàn thành',
    cancelled: 'Hủy bỏ',
  };
  return map[status] || status;
};

// Trong <script setup>
const visiblePages = computed(() => {
  if (!orders.value.last_page) return [];
  
  const current = orders.value.current_page;
  const last = orders.value.last_page;
  const pages = [];
  
  // Hiển thị 2 trang trước và 2 trang sau trang hiện tại
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const changePage = async (page) => {
  if (!page || page < 1 || page > orders.value.last_page) return;
  
  try {
    const params = {
      page: page,
      status: filters.status, 
      payment_method: filters.payment_method, 
      search: filters.search,
      date_from: filters.date_from, // Nên thêm nếu có lọc ngày
      date_to: filters.date_to      // Nên thêm nếu có lọc ngày
    };
    
    // Axios tự động serialize object params, không cần dùng URLSearchParams thủ công
    const response = await axios.get('/admin/orders', { params });
    orders.value = response.data;
    
    // Scroll lên đầu trang
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (error) {
    console.error("Lỗi phân trang:", error);
  }
};

// Init
onMounted(() => {
  fetchOrders();

});
</script>

<style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:root {
  --black: #000000;
  --white: #ffffff;
  --gray-50: #fafafa;
  --gray-100: #f5f5f5;
  --gray-200: #e5e5e5;
  --gray-300: #d4d4d4;
  --gray-400: #090909;
  --gray-600: #525252;
  --gray-900: #171717;
}

body {
  font-family: 'Helvetica Neue', Arial, sans-serif;
  background: var(--white);
  color: var(--black);
  line-height: 1.5;
}

.p-6 {
  padding: 40px 20px;
}

.bg-gray-50 {
  background: var(--white);
}

.min-h-screen {
  min-height: 100vh;
}

/* Filter Box */
.bg-white.p-4.rounded-lg.shadow.mb-6 {
  background: var(--white);
  border: 2px solid var(--black);
  padding: 30px;
  margin-bottom: 40px;
}

.grid.grid-cols-1.md\:grid-cols-5.gap-4 {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 120px;
  gap: 16px;
}

/* Input & Select */
input[type="text"],
select {
  font-family: inherit;
  font-size: 14px;
  border: 2px solid var(--black);
  background: var(--white);
  padding: 12px 16px;
  width: 100%;
  transition: all 0.2s;
}

input[type="text"]:focus,
select:focus {
  outline: none;
  box-shadow: inset 0 0 0 2px var(--black);
}

input[type="text"]::placeholder {
  color: var(--gray-400);
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
}

select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='black' stroke-width='2'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 36px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
}

/* Buttons */
button {
  font-family: inherit;
  cursor: pointer;
  border: 2px solid var(--black);
  padding: 12px 16px;
  transition: all 0.15s;
}

.bg-blue-600 {
  background: var(--black);
  color: var(--white);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 12px;
}

.bg-blue-600:hover {
  background: var(--white);
  color: var(--black);
}

button:active {
  transform: scale(0.98);
}

/* Table Container */
.bg-white.rounded-lg.shadow.overflow-hidden {
  background: var(--white);
  border: 2px solid var(--black);
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

/* Table Header */
thead {
  background: var(--black);
  color: var(--white);
}

thead th {
  padding: 16px 20px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Table Body */
tbody tr {
  border-bottom: 1px solid var(--gray-200);
  transition: background 0.15s;
}

tbody tr:hover {
  background: var(--gray-50);
}

tbody td {
  padding: 20px;
  font-size: 14px;
}

/* Order Code */
.font-bold.text-blue-600 {
  font-weight: 700;
  font-family: 'Courier New', monospace;
  letter-spacing: -0.5px;
  color: var(--black);
}

/* Customer Info */
.font-medium {
  font-weight: 600;
  margin-bottom: 4px;
}

.text-gray-500.text-xs {
  font-size: 12px;
  color: var(--gray-600);
}

/* Price */
td.font-bold {
  font-weight: 700;
  font-family: 'Courier New', monospace;
}

/* Status Badge */
.px-2.py-1.rounded-full {
  display: inline-block;
  padding: 6px 14px;
  border: 2px solid var(--black);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: var(--white);
  border-radius: 0;
}

.bg-yellow-100 { background: var(--white); }
.bg-blue-100 { background: var(--gray-100); }
.bg-purple-100 { background: var(--gray-200); }
.bg-green-100 { background: var(--black); color: var(--white); }
.bg-red-100 { background: var(--white); text-decoration: line-through; }

/* Action Button */
.text-blue-600.hover\:text-blue-800 {
  padding: 8px 16px;
  font-size: 12px;
  border: 2px solid var(--black);
  background: var(--white);
  color: var(--black);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.15s;
}

.text-blue-600.hover\:text-blue-800:hover {
  background: var(--black);
  color: var(--white);
}

/* Pagination Container */
.p-4.border-t.flex.justify-between.items-center {
  padding: 24px;
  border-top: 2px solid var(--black);
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Pagination Wrapper - Đảm bảo nằm ngang */
.flex.items-center.gap-1 {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 4px;
}

/* Pagination Buttons */
.px-3.py-1.border.rounded,
.px-3.py-1.border.rounded.hover\:bg-gray-100 {
  width: 40px;
  height: 40px;
  border: 2px solid var(--black);
  background: var(--white);
  color: var(--black);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
  transition: all 0.15s;
  border-radius: 0;
  padding: 0;
  flex-shrink: 0;
}

.px-3.py-1.border.rounded:hover:not(:disabled),
.px-3.py-1.border.rounded.hover\:bg-gray-100:hover:not(:disabled) {
  background: var(--black);
  color: var(--white);
}

/* Active page */
.bg-blue-500,
.bg-blue-500.text-white.font-bold {
  background: var(--black) !important;
  color: var(--white) !important;
}

/* Disabled state */
button:disabled,
.disabled\:opacity-50:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* Dots */
span.px-2 {
  padding: 0 8px;
  color: var(--gray-400);
  font-weight: 700;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* Modal */
.fixed.inset-0 {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.bg-white.rounded-lg.shadow-xl {
  background: var(--white);
  border: 4px solid var(--black);
  border-radius: 0;
  width: 100%;
  max-width: 1200px;
  max-height: 90vh;
  overflow-y: auto;
}

/* Modal Header */
.flex.justify-between.items-center.p-4.border-b {
  padding: 24px 32px;
  border-bottom: 2px solid var(--black);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--black);
}

.text-xl.font-bold {
  font-size: 20px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--white);
}

.text-gray-500.hover\:text-red-500 {
  width: 40px;
  height: 40px;
  border: 2px solid var(--white);
  background: transparent;
  color: var(--white);
  font-size: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.text-gray-500.hover\:text-red-500:hover {
  background: var(--white);
  color: var(--black);
}

/* Modal Body */
.p-6.grid.grid-cols-1.md\:grid-cols-3 {
  padding: 32px;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
}

/* Section Title */
.font-semibold.text-gray-700.border-b.pb-2 {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 2px solid var(--black);
  padding-bottom: 12px;
  margin-bottom: 20px;
  color: var(--black);
}

/* Product Item */
.flex.items-center.gap-4.border.p-2 {
  display: flex;
  gap: 16px;
  border: 2px solid var(--black);
  padding: 16px;
  margin-bottom: 16px;
  border-radius: 0;
}

.w-16.h-16 {
  width: 80px;
  height: 80px;
  border: 2px solid var(--black);
  object-fit: cover;
  border-radius: 0;
}

.font-bold.text-sm {
  font-weight: 700;
  margin-bottom: 4px;
  font-size: 14px;
}

.text-xs.text-gray-500 {
  font-size: 11px;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Price Summary */
.bg-gray-50.p-4.rounded {
  background: var(--gray-50);
  border: 2px solid var(--black);
  padding: 20px;
  margin-top: 20px;
  border-radius: 0;
}

.flex.justify-between {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 13px;
}

.font-bold.text-lg.border-t.pt-2 {
  font-size: 18px;
  font-weight: 700;
  border-top: 2px solid var(--black);
  padding-top: 16px;
  margin-top: 16px;
}

/* Status Update Box */
.bg-blue-50.p-4.rounded.border {
  background: var(--gray-50);
  border: 2px solid var(--black);
  padding: 20px;
  margin-bottom: 24px;
  border-radius: 0;
}

.block.text-sm.font-bold.mb-2 {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 12px;
  display: block;
}

/* Customer Info */
.space-y-6 > div {
  border: 2px solid var(--black);
  padding: 20px;
  margin-bottom: 24px;
}

.space-y-6 p {
  margin-bottom: 8px;
  font-size: 13px;
}

.font-bold {
  font-weight: 700;
}

/* Note Box */
.text-yellow-700.bg-yellow-50 {
  background: var(--gray-50);
  border: 2px solid var(--black);
  border-left-width: 8px;
  padding: 16px;
  margin-bottom: 16px;
  font-size: 13px;
  border-radius: 0;
  color: var(--black);
}

/* Print Button */
.border.border-gray-300.py-1 {
  width: 100%;
  padding: 14px;
  border: 2px solid var(--black);
  background: var(--white);
  color: var(--black);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 12px;
}

.border.border-gray-300.py-1:hover {
  background: var(--black);
  color: var(--white);
}

@media (max-width: 768px) {
  .grid.grid-cols-1.md\:grid-cols-5.gap-4 {
    grid-template-columns: 1fr;
  }

  .p-6.grid.grid-cols-1.md\:grid-cols-3 {
    grid-template-columns: 1fr;
  }

  table {
    font-size: 12px;
  }

  thead th, tbody td {
    padding: 12px 8px;
  }
}
  </style>