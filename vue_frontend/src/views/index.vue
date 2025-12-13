<template>
  <div class="home-page">
    <Header />
    
    <main class="main-content">
      <router-view />
      <WelcomePopup />
    </main>
    
    <Footer />
    
    <ChatWidget />
  </div>
</template>

<script setup>
import Header from '../components/header.vue';
import Footer from '../components/footer.vue';
import ChatWidget from '../components/ChatWidget.vue';
import WelcomePopup from '../components/WelcomePopup.vue';
</script>