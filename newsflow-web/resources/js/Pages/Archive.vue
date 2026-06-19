<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    locked: { type: Boolean, default: false },
    q: { type: String, default: '' },
    articles: { type: Object, default: null },
});

const query = ref(props.q);

function search() {
    router.get(route('archive'), { q: query.value }, { preserveState: true, preserveScroll: true });
}

function when(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<template>
    <Head title="Archive" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="font-serif text-2xl font-bold text-ink">Archive</h1>
        </template>

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Pro upsell -->
            <div v-if="locked" class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
                <h2 class="font-serif text-xl font-semibold text-ink">Archive is a Pro feature</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                    With Pro, stories that rotate out of your feeds are kept here so you can always catch up on what you missed.
                </p>
                <Link :href="route('billing')" class="mt-4 inline-block rounded-md bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    Upgrade to Pro
                </Link>
            </div>

            <template v-else>
                <p class="text-sm text-gray-500">
                    Stories that have rotated out of your feeds. We keep your history here so nothing is lost.
                </p>

                <form @submit.prevent="search" class="mt-4 flex gap-2">
                    <input v-model="query" type="search" placeholder="Search your archive…"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Search</button>
                </form>

                <div v-if="articles && articles.data.length" class="mt-6">
                    <ul class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                        <li v-for="a in articles.data" :key="a.id" class="p-4">
                            <div class="mb-1 flex items-center gap-2 text-xs text-gray-400">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">{{ a.topic_name }}</span>
                                <span v-if="a.source">{{ a.source }}</span>
                                <span>· archived {{ when(a.archived_at) }}</span>
                            </div>
                            <a :href="a.url" target="_blank" rel="noopener noreferrer" class="font-serif text-base font-semibold leading-snug text-ink hover:text-brand-700">{{ a.headline }}</a>
                            <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ a.description }}</p>
                        </li>
                    </ul>

                    <!-- Pagination -->
                    <div v-if="articles.links.length > 3" class="mt-6 flex flex-wrap justify-center gap-1">
                        <template v-for="(link, i) in articles.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="rounded-md px-3 py-1.5 text-sm"
                                :class="link.active ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                                preserve-scroll
                            />
                            <span v-else v-html="link.label" class="rounded-md px-3 py-1.5 text-sm text-gray-300" />
                        </template>
                    </div>
                </div>

                <div v-else class="mt-6 rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
                    <h3 class="font-serif text-lg font-semibold text-ink">{{ q ? 'No matches in your archive' : 'Your archive is empty' }}</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                        {{ q ? 'Try a different search.' : 'As your feeds refresh each day, older stories will collect here automatically.' }}
                    </p>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
