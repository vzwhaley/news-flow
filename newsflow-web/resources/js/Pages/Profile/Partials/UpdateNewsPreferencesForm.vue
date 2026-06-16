<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// A practical, deduped timezone list (browser Intl if available, else a curated set).
const timezones = (() => {
    try {
        if (typeof Intl.supportedValuesOf === 'function') {
            return Intl.supportedValuesOf('timeZone');
        }
    } catch (e) { /* fall through */ }
    return [
        'UTC',
        'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
        'America/Indiana/Indianapolis', 'America/Phoenix', 'America/Anchorage', 'Pacific/Honolulu',
        'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Asia/Kolkata', 'Australia/Sydney',
    ];
})();

const hours = Array.from({ length: 24 }, (_, h) => ({
    value: h,
    label: new Date(2000, 0, 1, h).toLocaleTimeString(undefined, { hour: 'numeric' }),
}));

const form = useForm({
    refresh_hour: user.value.refresh_hour ?? 6,
    timezone: user.value.timezone ?? 'UTC',
});

function submit() {
    form.patch(route('preferences.update'), { preserveScroll: true });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">News preferences</h2>
            <p class="mt-1 text-sm text-gray-600">
                Choose when your feed refreshes each day. We’ll gather the latest
                popular stories on your topics at this hour, in your timezone.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div>
                <InputLabel for="refresh_hour" value="Daily refresh time" />
                <select
                    id="refresh_hour"
                    v-model="form.refresh_hour"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                >
                    <option v-for="h in hours" :key="h.value" :value="h.value">{{ h.label }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.refresh_hour" />
            </div>

            <div>
                <InputLabel for="timezone" value="Timezone" />
                <select
                    id="timezone"
                    v-model="form.timezone"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                >
                    <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz.replace(/_/g, ' ') }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.timezone" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
