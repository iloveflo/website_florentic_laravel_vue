<template>
  <div class="chat-widget-container">
    <button v-if="!isOpen" @click="toggleChat" class="chat-toggle-btn">
      <div class="advisor-avatar">
        <div class="advisor-body">
          <div class="advisor-head">
            <div class="headphone-left"></div>
            <div class="face">
              <div class="eyes">
                <div class="eye"></div>
                <div class="eye"></div>
              </div>
              <div class="mouth"></div>
            </div>
            <div class="headphone-right"></div>
          </div>
          <div class="advisor-torso"></div>
          <div class="advisor-arms">
            <div class="arm-left"></div>
            <div class="arm-right"></div>
          </div>
        </div>
      </div>
      <span class="tooltip">Hỗ trợ trực tuyến</span>
    </button>

    <div v-else class="chat-box">
      <div class="chat-header">
        <div class="header-title">
          <div class="header-avatar">
            <div class="small-advisor-head">
              <div class="small-headphone-left"></div>
              <div class="small-face"></div>
              <div class="small-headphone-right"></div>
            </div>
          </div>
          <span>Hỗ trợ khách hàng</span>
        </div>
        <button @click="toggleChat" class="close-btn">✖️</button>
      </div>

      <div class="chat-messages" ref="messagesContainer">
        <div v-for="(msg, index) in messages" :key="index" :class="['message-container', msg.sender]">

          <div v-if="msg.sender === 'bot'" class="bot-avatar">
            <div class="bot-advisor-icon">
              <div class="bot-headphone"></div>
              <div class="bot-face"></div>
            </div>
          </div>

          <div class="message-content">
            <div class="message-bubble">
              {{ msg.text }}
            </div>

            <div v-if="msg.products && msg.products.length > 0" class="product-carousel">
              <div v-for="product in msg.products" :key="product.id" class="product-card">
                <img :src="product.main_image_url || 'https://via.placeholder.com/150'" alt="Product Image"
                  class="product-img" />

                <div class="product-info">
                  <h5 class="product-name">{{ product.name }}</h5>
                  <p class="product-price">{{ formatCurrency(product.sale_price || product.price) }}</p>

                  <button @click="goToProduct(product.slug)" class="view-detail-btn">
                    Xem chi tiết
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div v-if="isLoading" class="message-container bot typing">
          <div class="bot-avatar">
            <div class="bot-advisor-icon">
              <div class="bot-headphone"></div>
              <div class="bot-face"></div>
            </div>
          </div>
          <div class="message-bubble"><span>.</span><span>.</span><span>.</span></div>
        </div>
      </div>

      <div class="chat-input">
        <input v-model="userMessage" @keyup.enter="sendMessage" placeholder="Nhập câu hỏi cần hỗ trợ..."
          :disabled="isLoading" />
        <button @click="sendMessage" :disabled="isLoading || !userMessage.trim()">➤</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-widget-container {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 9999;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* === NÚT CHAT TOGGLE - HÌNH NỬA NGƯỜI === */
.chat-toggle-btn {
  position: relative;
  width: 70px;
  height: 70px;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: visible;
  padding: 0;
}

.chat-toggle-btn:hover {
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.6);
}

.chat-toggle-btn:hover .tooltip {
  opacity: 1;
  transform: translateX(0) translateY(-50%);
  visibility: visible;
}

.chat-toggle-btn:hover .advisor-avatar {
  animation: wave 0.6s ease-in-out;
}

@keyframes wave {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-5deg); }
  75% { transform: rotate(5deg); }
}

/* === TOOLTIP === */
.tooltip {
  position: absolute;
  right: calc(100% + 12px);
  top: 50%;
  transform: translateX(0) translateY(-50%);
  background: #2d3748;
  color: white;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.tooltip::after {
  content: '';
  position: absolute;
  right: -8px;
  top: 50%;
  transform: translateY(-50%);
  width: 0;
  height: 0;
  border-left: 8px solid #2d3748;
  border-top: 8px solid transparent;
  border-bottom: 8px solid transparent;
}

/* === AVATAR TƯ VẤN VIÊN === */
.advisor-avatar {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 10px;
}

.advisor-body {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Đầu và tai nghe */
.advisor-head {
  position: relative;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0;
}

.headphone-left,
.headphone-right {
  position: absolute;
  width: 8px;
  height: 12px;
  background: #2d3748;
  border-radius: 6px;
  top: 10px;
}

.headphone-left::before,
.headphone-right::before {
  content: '';
  position: absolute;
  width: 6px;
  height: 6px;
  background: #4a5568;
  border-radius: 50%;
  top: 3px;
  left: 1px;
}

.headphone-left {
  left: -3px;
  transform: rotate(-10deg);
}

.headphone-right {
  right: -3px;
  transform: rotate(10deg);
}

/* Kết nối tai nghe qua đầu */
.headphone-left::after,
.headphone-right::after {
  content: '';
  position: absolute;
  height: 2px;
  background: #2d3748;
  border-radius: 3px 3px 0 0;
  top: -5px;
}

.headphone-left::after {
  width: 20px;
  left: 7px;
  border-radius: 10px 0 0 0;
  transform: rotate(-15deg);
}

.headphone-right::after {
  width: 20px;
  right: 7px;
  border-radius: 0 10px 0 0;
  transform: rotate(15deg);
}

/* Khuôn mặt */
.face {
  width: 26px;
  height: 26px;
  background: #ffd4a3;
  border-radius: 50%;
  position: relative;
  border: 2px solid #fff;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.eyes {
  position: absolute;
  width: 100%;
  top: 10px;
  display: flex;
  justify-content: center;
  gap: 8px;
}

.eye {
  width: 3px;
  height: 3px;
  background: #2d3748;
  border-radius: 50%;
}

.mouth {
  position: absolute;
  bottom: 6px;
  left: 50%;
  transform: translateX(-50%);
  width: 10px;
  height: 5px;
  border: 1.5px solid #2d3748;
  border-top: none;
  border-radius: 0 0 10px 10px;
}

/* Thân */
.advisor-torso {
  width: 38px;
  height: 22px;
  background: #fff;
  border-radius: 20px 20px 10px 10px;
  position: relative;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
  margin-top: -2px;
}

.advisor-torso::before {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  background: #e2e8f0;
  border-radius: 50%;
  top: 3px;
  left: 50%;
  transform: translateX(-50%);
}

/* Tay */
.advisor-arms {
  position: absolute;
  width: 44px;
  top: 32px;
  display: flex;
  justify-content: space-between;
}

.arm-left,
.arm-right {
  width: 8px;
  height: 18px;
  background: #ffd4a3;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.arm-left {
  transform: rotate(15deg);
}

.arm-right {
  transform: rotate(-15deg);
}

/* === CHAT BOX === */
.chat-box {
  width: 380px;
  height: 550px;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* === HEADER === */
.chat-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 18px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
  font-size: 16px;
}

.header-avatar {
  width: 35px;
  height: 35px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.small-advisor-head {
  position: relative;
  width: 24px;
  height: 24px;
  background: #ffd4a3;
  border-radius: 50%;
  border: 2px solid #fff;
}

.small-headphone-left,
.small-headphone-right {
  position: absolute;
  width: 6px;
  height: 10px;
  background: #2d3748;
  border-radius: 4px;
  top: 7px;
}

.small-headphone-left {
  left: -3px;
}

.small-headphone-right {
  right: -3px;
}

.small-face::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at 30% 40%, #fff 1px, transparent 1px),
              radial-gradient(circle at 70% 40%, #fff 1px, transparent 1px),
              radial-gradient(ellipse at 50% 65%, #2d3748 8px 3px, transparent 3px);
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  backdrop-filter: blur(10px);
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

/* === MESSAGES === */
.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f7fafc;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: #e2e8f0;
  border-radius: 10px;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* === MESSAGE CONTAINER === */
.message-container {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.message-container.user {
  flex-direction: row-reverse;
}

.bot-avatar {
  width: 36px;
  height: 36px;
  min-width: 36px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.bot-advisor-icon {
  position: relative;
  width: 22px;
  height: 22px;
  background: #ffd4a3;
  border-radius: 50%;
  border: 2px solid #fff;
}

.bot-headphone {
  position: absolute;
  width: 100%;
  height: 100%;
}

.bot-headphone::before,
.bot-headphone::after {
  content: '';
  position: absolute;
  width: 5px;
  height: 8px;
  background: #2d3748;
  border-radius: 3px;
  top: 7px;
}

.bot-headphone::before {
  left: -2px;
}

.bot-headphone::after {
  right: -2px;
}

.bot-face::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at 35% 45%, #2d3748 1.5px, transparent 1.5px),
              radial-gradient(circle at 65% 45%, #2d3748 1.5px, transparent 1.5px);
}

.message-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 75%;
}

.message-bubble {
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.5;
  word-wrap: break-word;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.message-container.bot .message-bubble {
  background: #ffffff;
  color: #2d3748;
  border-bottom-left-radius: 4px;
}

.message-container.user .message-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom-right-radius: 4px;
  margin-left: auto;
}

/* === TYPING ANIMATION === */
.typing .message-bubble {
  display: flex;
  gap: 4px;
  padding: 12px 20px;
}

.typing .message-bubble span {
  width: 8px;
  height: 8px;
  background: #a0aec0;
  border-radius: 50%;
  animation: typing 1.4s infinite;
}

.typing .message-bubble span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing .message-bubble span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
  }
  30% {
    transform: translateY(-10px);
  }
}

/* === PRODUCT CAROUSEL === */
.product-carousel {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding: 8px 0;
}

.product-carousel::-webkit-scrollbar {
  height: 6px;
}

.product-carousel::-webkit-scrollbar-track {
  background: #e2e8f0;
  border-radius: 10px;
}

.product-carousel::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

.product-card {
  min-width: 180px;
  background: #f7fafc;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.2s;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.product-img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 10px;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-name {
  font-size: 13px;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-price {
  font-size: 14px;
  font-weight: 700;
  color: #667eea;
  margin: 0;
}

.view-detail-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.view-detail-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* === INPUT === */
.chat-input {
  display: flex;
  padding: 16px;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  gap: 10px;
}

.chat-input input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 25px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
}

.chat-input input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.chat-input input:disabled {
  background: #f7fafc;
  cursor: not-allowed;
}

.chat-input button {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.chat-input button:hover:not(:disabled) {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.chat-input button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* === RESPONSIVE === */
@media (max-width: 480px) {
  .chat-box {
    width: calc(100vw - 20px);
    height: calc(100vh - 100px);
    border-radius: 20px 20px 0 0;
  }

  .chat-toggle-btn {
    width: 60px;
    height: 60px;
  }

  .tooltip {
    font-size: 12px;
    padding: 8px 12px;
  }
}
</style>
<script setup>
import { ref, nextTick,onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Import Router để chuyển trang

const router = useRouter(); // Khởi tạo router
const isOpen = ref(false);
const userMessage = ref('');
// Thêm trường 'products' vào cấu trúc tin nhắn
const messages = ref([
    { sender: 'bot', text: 'Xin chào! Tôi là AI Gemini, tôi có thể giúp gì cho bạn?', products: [] }
]);

// --- PHẦN MỚI THÊM: LOAD LỊCH SỬ ---
onMounted(async () => {
  const currentSessionId = localStorage.getItem('chat_session_id');
  
  if (currentSessionId) {
    try {
      // Gọi API lấy lịch sử (Thay URL bằng đường dẫn thật của bạn)
      const response = await axios.get('/chat/history', {
        params: { session_id: currentSessionId }
      });

      // Nếu có lịch sử thì gán vào biến messages
      if (response.data && response.data.length > 0) {
        messages.value = [
            { sender: 'bot', text: 'Xin chào! Tôi là AI Gemini, tôi có thể giúp gì cho bạn?', products: [] },
            ...response.data
        ];
      }
    } catch (error) {
      console.error("Lỗi tải lịch sử chat:", error);
    }
  }
});
// ------------------------------------

const isLoading = ref(false);
const messagesContainer = ref(null);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    scrollToBottom();
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// Hàm chuyển hướng khi bấm nút Xem chi tiết
const goToProduct = (slug) => {
    isOpen.value = false;
    router.push(`/product/${slug}`);
};

const sendMessage = async () => {
    if (!userMessage.value.trim()) return;

    const text = userMessage.value;
    messages.value.push({ sender: 'user', text: text, products: [] });
    userMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    const currentSessionId = localStorage.getItem('chat_session_id');

    try {
        const response = await axios.post('/chat', {
            message: text,
            session_id: currentSessionId
        });

        messages.value.push({
            sender: 'bot',
            text: response.data.reply,
            products: response.data.products || [] // Gán sản phẩm vào đây
        });

        if (response.data.session_id) {
            localStorage.setItem('chat_session_id', response.data.session_id);
        }

    } catch (error) {
        console.error(error);
        messages.value.push({ sender: 'bot', text: 'Xin lỗi, tôi đang mất kết nối.', products: [] });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

