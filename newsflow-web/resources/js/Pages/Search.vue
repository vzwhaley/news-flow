<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    q: { type: String, default: '' },
    locked: { type: Boolean, default: false },
    feed: { type: Array, default: () => [] },
    saved: { type: Array, default: () => [] },
});

const query = ref(props.q);

function search() {
    router.get(route('search'), { q: query.value }, { preserveState: true, preserveScroll: true });
}

const hasResults = () => props.feed.length || props.saved.length;
</script>

<template>
    <Head title="Search" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="font-serif text-2xl font-bold text-ink">Search</h1>
        </template>

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Pro upsell -->
            <div v-if="locked" class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
                <h2 class="font-serif text-xl font-semibold text-ink">Search is a Pro feature</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                    Upgrade to search across all your topic feeds and saved articles at once.
                </p>
                <Link :href="route('billing')" class="mt-4 inline-block rounded-md bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    Upgrade to Pro
                </Link>
            </div>

            <template v-else>
                <form @submit.prevent="search" class="flex gap-2">
                    <input
                        v-model="query"
                        type="search"
                        autofocus
                        placeholder="Search your feeds and saved articles…"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Search</button>
                </form>

                <!-- Results -->
                <div v-if="q" class="mt-8">
                    <p v-if="!hasResults()" class="rounded-lg bg-gray-50 p-6 text-center text-sm text-gray-500">
                        No matches for “{{ q }}”.
                    </p>

                    <section v-if="feed.length" class="mb-8">
                        <h2 class="mb-3 font-serif text-lg font-bold text-ink">In your feeds <span class="text-sm font-normal text-gray-400">({{ feed.length }})</span></h2>
                        <ul class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                            <li v-for="a in feed" :key="'f'+a.id" class="p-4">
                                <div class="mb-1 flex items-center gap-2 text-xs text-gray-400">
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">{{ a.topic_name }}</span>
                                    <span v-if="a.source">{{ a.source }}</span>
                                    <span v-if="a.is_read" class="text-green-600">· read</span>
                                </div>
                                <a :href="a.url" target="_blank" rel="noopener noreferrer" class="font-serif text-base font-semibold leading-snug text-ink hover:text-brand-700">{{ a.headline }}</a>
                                <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ a.description }}</p>
                            </li>
                        </ul>
                    </section>

                    <section v-if="saved.length">
                        <h2 class="mb-3 font-serif text-lg font-bold text-ink">In your saved <span class="text-sm font-normal text-gray-400">({{ saved.length }})</span></h2>
                        <ul class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                            <li v-for="a in saved" :key="'s'+a.id" class="p-4">
                                <div class="mb-1 flex items-center gap-2 text-xs text-gray-400">
                                    <span v-if="a.topic_name" class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">{{ a.topic_name }}</span>
                                    <span v-if="a.source">{{ a.source }}</span>
                                </div>
                                <a :href="a.url" target="_blank" rel="noopener noreferrer" class="font-serif text-base font-semibold leading-snug text-ink hover:text-brand-700">{{ a.headline }}</a>
                                <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ a.description }}</p>
                            </li>
                        </ul>
                    </section>
                </div>

                <p v-else class="mt-8 text-center text-sm text-gray-400">
                    Type a word or phrase to search across every topic you follow and everything you’ve saved.
                </p>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
