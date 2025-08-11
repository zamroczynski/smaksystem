<template>
    <Transition name="fade">
        <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);
let timer: ReturnType<typeof setTimeout> | null = null;

router.on('start', () => {
    timer = setTimeout(() => {
        loading.value = true;
    }, 250);
});

router.on('finish', () => {
    if (timer) {
        clearTimeout(timer);
    }
    loading.value = false;
});
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>