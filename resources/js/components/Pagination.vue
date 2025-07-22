<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'; // Importuj komponent Button z Shadcn/Vue

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links.length > 3" class="flex justify-center mt-4">
        <div class="flex flex-wrap items-center gap-2">
            <template v-for="(link, key) in links" :key="key">
                <Button
                    v-if="link.url === null"
                    variant="outline"
                    class="pointer-events-none opacity-50"
                    disabled
                >
                    <span v-html="link.label"></span>
                </Button>
                <Button
                    v-else
                    as-child
                    :variant="link.active ? 'default' : 'outline'"
                >
                    <Link :href="link.url">
                        <span v-html="link.label"></span>
                    </Link>
                </Button>
            </template>
        </div>
    </div>
</template>