<script setup>
import ArticleCard from '@/Components/ArticleCard.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    topic: { type: Object, required: true },
});

const refreshing = ref(false);
const removing = ref(false);

const lastRefreshed = computed(() => {
    if (!props.topic.last_refreshed_at) return 'Not yet refreshed';
    const d = new Date(props.topic.last_refreshed_at);
    return 'Updated ' + d.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
});

function refresh() {
    refreshing.value = true;
    router.post(route('topics.refresh', props.topic.id), {}, {
        preserveScroll: true,
        onFinish: () => (refreshing.value = false),
    });
}

function remove() {
    if (!confirm(`Stop following "${props.topic.name}"? This removes its feed.`)) return;
    removing.value = true;
    router.delete(route('topics.destroy', props.topic.id), {
        preserveScroll: true,
        onFinish: () => (removing.value = false),
    });
}
</script>

<template>
    <section class="mb-12">
        <!-- Topic masthead -->
        <div class="mb-4 flex items-end justify-between border-b-2 border-ink pb-2">
            <div>
                <h2 class="font-serif text-2xl font-bold tracking-tight text-ink">
                    {{ topic.name }}
                </h2>
                <p class="text-xs text-gray-400">{{ lastRefreshed }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="refresh"
                    :disabled="refreshing"
                    class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                >
                    <svg
                        class="h-4 w-4"
                        :class="{ 'animate-spin': refreshing }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ refreshing ? 'Refreshing…' : 'Refresh' }}
                </button>
                <button
                    @click="remove"
                    :disabled="removing"
                    class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-50"
                    title="Stop following"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Articles grid -->
        <div
            v-if="topic.articles.length"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <ArticleCard
                v-for="(a, i) in topic.articles"
                :key="a.id"
                :article="a"
                :rank="i + 1"
            />
        </div>
        <p v-else class="rounded-lg bg-gray-50 p-6 text-center text-sm text-gray-500">
            No articles yet — hit Refresh to pull the latest stories.
        </p>
    </section>
</template>
