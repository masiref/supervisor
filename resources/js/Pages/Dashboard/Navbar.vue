<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { ref, inject, computed } from 'vue';

const abbr = inject('abbr');

const props = defineProps({
    pendingAlertsCount: Number,
    closedAlertsCount: Number,
});

const pendingAlertsCount = computed(() => props.pendingAlertsCount);
const newPendingAlertsCount = ref(0);
const closedAlertsCount = ref(props.closedAlertsCount);
const newClosedAlertsCount = ref(0);
</script>

<template>
    <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500"
        id="dashboard-tab" data-tabs-toggle="#dashboard-tab-content" role="tablist">
        <li class="mr-2">
            <Link :href="route('dashboard')" class="inline-flex p-4 rounded-md"
                :class="{
                    'text-white bg-blue-600': route().current() === 'dashboard',
                    'text-blue-50 bg-gray-100 hover:bg-blue-600 hover:text-white': route().current() !== 'dashboard',
                }">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="mr-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ __('Overview') }}
            </Link>
        </li>
        <li class="mr-2">
            <Link :href="route('pending-alerts.index')" class="inline-flex relative p-4 rounded-md"
                :class="{
                    'text-white bg-blue-600': route().current().startsWith('pending-alerts'),
                    'text-blue-50 bg-gray-100 hover:bg-blue-600 hover:text-white': !route().current().startsWith('pending-alerts'),
                }">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="mr-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5" />
                </svg>
                {{ __('Pending alerts') }}
                <span class="bg-blue-300 text-blue-800 text-xs font-semibold ml-2 px-2.5 py-0.5 rounded">
                    {{ abbr(pendingAlertsCount, 1) }}
                </span>
                <span v-if="newPendingAlertsCount > 0"
                    class="inline-flex absolute -top-2 -right-2 justify-center items-center w-6 h-6 text-xs font-bold text-white bg-red-500 rounded-full border-2 border-white">
                    {{ abbr(newPendingAlertsCount, 1) }}
                </span>
            </Link>
        </li>
        <li class="mr-2">
            <Link :href="route('closed-alerts.index')" class="inline-flex p-4 rounded-md"
                :class="{
                    'text-white bg-blue-600': route().current().startsWith('closed-alerts'),
                    'text-blue-50 bg-gray-100 hover:bg-blue-600 hover:text-white': !route().current().startsWith('closed-alerts'),
                }">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="mr-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                {{ __('Closed alerts') }}
                <span class="bg-blue-300 text-blue-800 text-xs font-semibold ml-2 px-2.5 py-0.5 rounded">
                    {{ abbr(closedAlertsCount, 1) }}
                </span>
                <span v-if="newClosedAlertsCount > 0"
                    class="inline-flex absolute -top-2 -right-2 justify-center items-center w-6 h-6 text-xs font-bold text-white bg-red-500 rounded-full border-2 border-white">
                    {{ abbr(newClosedAlertsCount, 1) }}
                </span>
            </Link>
        </li>
        <li class="mr-2 relative">
            <button class="inline-flex p-4 rounded-md bg-gray-100 hover:bg-blue-100 text-blue-50 disabled:opacity-25"
                disabled="disabled">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="mr-2 w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                </svg>
                {{ __('Analysis') }}
            </button>
            <span
                class="inline-flex absolute -top-2 -right-14 justify-center items-center h-6 text-xs font-bold text-white bg-blue-500 rounded-sm border-1 border-white p-2">
                {{ __('Coming soon') }}
            </span>
        </li>
    </ul>
</template>