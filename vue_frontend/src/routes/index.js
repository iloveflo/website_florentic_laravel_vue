import { createRouter, createWebHistory } from 'vue-router'

// Layout chính
import MainLayout from '../views/index.vue'

// Pages
import Home from '../views/home.vue'
import Login from '../views/login.vue'
import Register from '../views/register.vue'
import ResetPassword from '../views/resetpassword.vue'
import ForgotPassword from '../views/forgotpassword.vue'
import About from '../views/about.vue'
import Contact from '../views/contact.vue'
import Search from '../views/search.vue'

// User
import Cart from '../views/user/cart.vue'
import Profile from '../views/user/profile.vue'
import Orders from '../views/user/orders.vue'
import PaymentResult from '../views/PaymentResult.vue'
import Review from '../views/user/review.vue'
import OrderDetail from '../views/user/orderdetail.vue'

// Products
import Products from '../views/products/index.vue'
import ProductDetails from '../views/products/productdetails.vue'
import ProductCategory from '../views/products/productcategory.vue'


// Help
import shipping from '../views/help/shipping.vue'
import returns from '../views/help/returns.vue'
import size_guide from '../views/help/size_guide.vue'

// Admin
import Admin from '../views/admin.vue'
import QuanLyNguoiDung from '../views/admin/quanlynguoidung.vue'
import QuanLySanPham from '../views/admin/quanlysanpham.vue'
import QuanLyDonHang from '../views/admin/quanlydonhang.vue'
import ThongKeBaoCao from '../views/admin/thongkebaocao.vue'
import QuanLyKhuyenMai from '../views/admin/quanlykhuyenmai.vue'

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: Home,
      },
      {
        path: 'about',
        name: 'about',
        component: About,
      },
      {
        path: 'contact',
        name: 'contact',
        component: Contact,
      },
      {
        path: 'cart',
        name: 'cart',
        component: Cart,
      },
      {
        path: 'login',
        name: 'login',
        component: Login,
      },
      {
        path: 'register',
        name: 'register',
        component: Register,
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
      },
      {
        path: 'reset-password',
        name: 'reset-password',
        component: ResetPassword,
      },
      {
        path: 'search',
        name: 'search',
        component: Search,
      },
      
      // User
      {
        path: 'user/profile',
        name: 'profile',
        component: Profile,
      },
      {
        path: 'user/orders',
        name: 'orders',
        component: Orders,
      },
      {
        path: 'user/cart',
        name: 'cart',
        component: Cart,
      },

      // Payment Result
      {
        path: '/payment/result',
        name: 'PaymentResult',
        component: PaymentResult
      },
      // Products
      {
        path: 'products',
        name: 'products',
        component: Products,
      },

      // Product Category
      {
        path: 'products/category/:slug',
        name: 'product-category',
        component: ProductCategory,
      },

      // Product Details
      {
        path: '/product/:slug',
        name: 'product-details',
        component: ProductDetails,
      },

      // Order Detail
      {
        path : '/user/order/:order_code',
        name : 'order-detail',
        component : OrderDetail,
      },

      // Review
      {
          path: '/reviews/:order_code', 
          name: 'review',
          component: Review,
      },

      // Help
      { path: 'help/shipping', name: 'shipping', component: shipping, meta: { title: 'Chính sách vận chuyển' } },
      { path: 'help/returns', name: 'returns', component: returns, meta: { title: 'Đổi trả & Hoàn tiền' } },
      { path: 'help/size-guide', name: 'size-guide', component: size_guide, meta: { title: 'Hướng dẫn chọn size' } },
    ]
  },
  
  // Admin (layout riêng, không dùng Header/Footer chính)
  {
    path: '/admin',
    name: 'admin',
    component: Admin,
    children: [
      { path: 'quan-ly-nguoi-dung', component: QuanLyNguoiDung },
      { path: 'quan-ly-san-pham', component: QuanLySanPham },
      { path: 'quan-ly-don-hang', component: QuanLyDonHang },
      { path: 'thong-ke-bao-cao', component: ThongKeBaoCao },
      { path: 'quan-ly-khuyen-mai', component: QuanLyKhuyenMai },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

export default router