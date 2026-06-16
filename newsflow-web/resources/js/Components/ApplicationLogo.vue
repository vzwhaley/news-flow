<script setup>
defineProps({
    withWordmark: { type: Boolean, default: false },
});
</script>

<template>
    <span class="inline-flex items-center gap-2">
        <!-- Mark: stylised "flowing" news lines -->
        <svg
            viewBox="0 0 40 40"
            class="h-9 w-9 shrink-0"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <rect width="40" height="40" rx="9" fill="#2563eb" />
            <path
                d="M9 27c4-6 6-9 11-9s7 3 11 9"
                stroke="white"
                stroke-width="2.4"
                stroke-linecap="round"
            />
            <path
                d="M9 20c4-6 6-9 11-9s7 3 11 9"
                stroke="#bfdbfe"
                stroke-width="2.4"
                stroke-linecap="round"
            />
            <circle cx="20" cy="13" r="2" fill="white" />
        </svg>
        <span
            v-if="withWordmark"
            class="font-serif text-xl font-bold tracking-tight text-ink"
        >
            News<span class="text-brand-600">Flow</span>
        </span>
    </span>
</template>
