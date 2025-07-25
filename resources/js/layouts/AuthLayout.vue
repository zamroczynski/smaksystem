<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthCardLayout.vue';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <Toaster rich-colors />
        <slot />
    </AuthLayout>
</template>
