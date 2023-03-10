<script setup>
import { ref, onMounted, reactive, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ActionConfirmationModal from './Partials/ActionConfirmationModal.vue';
import BulkConfirmationModal from './Partials/BulkConfirmationModal.vue';
import BulkButtons from './Partials/BulkButtons.vue';
import Filters from './Partials/Filters/Index.vue';
import PageContentHeader from '@/Components/PageContentHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import Tiles from './Partials/Tiles.vue';
import Navbar from '../Navbar.vue';
import { Link, useForm } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    alerts: Object,
    alertsCount: Number,
    closedAlertsCount: Number,
    automatedProcessesCount: Number,
    alertsAverageResolutionTimeEveryday: Object,
    alertsAverageResolutionTime: String,
    filters: Object,
    alertsProperties: Object,
});

const pendingAlertsCount = ref(props.alertsCount);
const newPendingAlertsCount = ref(0);
const closedAlertsCount = ref(props.closedAlertsCount);
const newClosedAlertsCount = ref(0);
const automatedProcessesCount = ref(props.automatedProcessesCount);

const sorting = reactive(props.filters.sorting ?? {
    field: 'id',
    direction: 'desc',
});
const filtersData = computed(() => {
    const data = props.filters.data;

    if (data) {
        return {
            alert: {
                creationDateRange: data.alert.creationDateRange ?? [],
                selectedSeverities: data.alert.selectedSeverities ?? [],
                selectedNotificationNames: data.alert.selectedNotificationNames ?? [],
                selectedComponents: data.alert.selectedComponents ?? [],
            },
        };
    }

    return {
        alert: {
            creationDateRange: [],
            selectedSeverities: [],
            selectedNotificationNames: [],
            selectedComponents: [],
        },
    };
});

const singleAction = ref('');
const confirmingAction = ref(false);
const bulkAction = ref('');
const confirmingBulkAction = ref(false);
const selected = ref([]);

const form = useForm({});

watch(sorting, function (value) {
    Inertia.get(route('pending-alerts.index'), {
        sorting: {
            field: value.field,
            direction: value.direction,
        },
    }, {
        preserveScroll: true,
        preserveState: true,
    });
});

const filterOnAlertProperties = () => {
    const alertFilters = filtersData.value.alert;
    let attributes = alertFilters.creationDateRange ? { creationDateRange: alertFilters.creationDateRange } : {};
    if (alertFilters.selectedSeverities) {
        attributes.selectedSeverities = alertFilters.selectedSeverities;
    }
    if (alertFilters.selectedNotificationNames) {
        attributes.selectedNotificationNames = alertFilters.selectedNotificationNames;
    }
    if (alertFilters.selectedComponents) {
        attributes.selectedComponents = alertFilters.selectedComponents;
    }

    Inertia.get(route('pending-alerts.index'), {
        data: {
            alert: attributes,
        },
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const sort = (field) => {
    if (sorting.field !== field) {
        sorting.direction = 'asc';
    } else {
        sorting.direction = sorting.direction === 'asc' ? 'desc' : 'asc';
    }
    sorting.field = field;
};

const edit = (item) => {
    Inertia.get(route('pending-alerts.edit', {
        alert: item.id
    }));
};

const triggerAction = (action, item) => {
    singleAction.value = action;
    Object.assign(form, item);
    confirmingAction.value = true;
};

const read = (callback) => {
    Inertia.post(route('pending-alerts.read', {
        alert: form.id,
    }), {}, {
        onSuccess: () => {
            callback();
        },
    });
};

const lock = (callback) => {
    Inertia.post(route('pending-alerts.lock', {
        alert: form.id,
    }), {}, {
        onSuccess: () => {
            callback();
        },
    });
};

const unlock = (callback) => {
    Inertia.post(route('pending-alerts.unlock', {
        alert: form.id,
    }), {}, {
        onSuccess: () => {
            callback();
        },
    });
};

const singleDo = computed(() => {
    switch (singleAction.value) {
        case 'read':
            return read;
        case 'lock':
            return lock;
        case 'unlock':
            return unlock;
    }
});

const selectAll = computed({
    get() {
        return props.alerts.data.length > 0 ? selected.value.length == props.alerts.data.length : false;
    },
    set(value) {
        let values = [];
        if (value) {
            props.alerts.data.forEach(function (alert) {
                values.push(alert.id);
            });
        }

        selected.value = values;
    }
});

const triggerBulkAction = (action) => {
    bulkAction.value = action;
    confirmingBulkAction.value = true;
};

const bulkRead = (callback) => {
    Inertia.post(route('pending-alerts.bulk-read'), {
        selected: props.selected
    }, {
        onSuccess: () => {
            callback();
        },
    });
};

const bulkLock = (callback) => {
    Inertia.post(route('pending-alerts.bulk-lock'), {
        selected: props.selected
    }, {
        onSuccess: () => {
            callback();
        },
    });
};

const bulkUnlock = (callback) => {
    Inertia.post(route('pending-alerts.bulk-unlock'), {
        selected: props.selected
    }, {
        onSuccess: () => {
            callback();
        },
    });
};

const bulkDo = computed(() => {
    switch (bulkAction.value) {
        case 'read':
            return bulkRead;
        case 'lock':
            return bulkLock;
        case 'unlock':
            return bulkUnlock;
    }
});

const resetBulkActions = () => {
    confirmingBulkAction.value = false;
    selected.value = [];
};

onMounted(() => {
    Echo.channel('orchestrator-connection-tenant-alert')
        .listen('.new', (data) => {
            console.log(data);
            pendingAlertsCount.value++;
            newPendingAlertsCount.value++;
        });
    Echo.channel('orchestrator-connection-tenant-alert')
        .listen('.closed', (data) => {
            console.log(data);
            pendingAlertsCount.value--;
            newPendingAlertsCount.value--;
            closedAlertsCount.value++;
            newClosedAlertsCount.value++;
        });
});
</script>

<template>
    <AppLayout :title="__('Dashboard') + ' > ' + __('Pending alerts')">
        <div class="bg-white shadow-xl sm:rounded-lg">
            <PageContentHeader :text="__('Supervise your digital workforce')">
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-10 h-10 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                </template>
            </PageContentHeader>

            <!-- Navbar -->
            <div class="p-6 sm:px-20 bg-gray-200 bg-opacity-25">
                <Navbar :pending-alerts-count="pendingAlertsCount"
                    :closed-alerts-count="closedAlertsCount" />
            </div>

            <!-- Main content -->
            <div class="px-6 pb-6 sm:px-20 bg-gray-200 bg-opacity-25">
                <div class="grid grid-cols-5 gap-4 mb-4">
                    <!-- Tiles -->
                    <Tiles :pending-alerts-count="pendingAlertsCount"  
                        :alerts-average-resolution-time-everyday="alertsAverageResolutionTimeEveryday"
                        :alerts-average-resolution-time="alertsAverageResolutionTime" />
                </div>

                <Filters :data="filtersData" 
                    :alerts-properties="props.alertsProperties"
                    @alert-property-updated="filterOnAlertProperties" class="mb-4" />
                
                <BulkButtons class="mb-4" :selected="selected"
                    @reading="triggerBulkAction('read')"
                    @locking="triggerBulkAction('lock')"
                    @unlocking="triggerBulkAction('unlock')"
                    @cancel="resetBulkActions" />

                <table class="min-w-full divide-y divide-y-gray-200 text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input type="checkbox" class="rounded text-blue-50 shadow-sm focus:border-blue-50" :class="{
                                        'bg-gray-100 border-gray-300 hover:border-gray-400': alerts.data.length == 0,
                                        'bg-white border-gray-400 hover:border-gray-neutral-55': alerts.data.length > 0
                                    }" v-model="selectAll" name="checkbox-all"
                                        :disabled="alerts.data.length == 0">
                                    <label for="checkbox-all" class="sr-only">{{ __('Select all items')}}</label>
                                </div>
                            </th>
                            <th scope="col" class="py-3 px-6">
                                <Link @click.prevent="sort('id')" class="flex items-center">
                                    <span>{{ __('ID') }}</span>
                                    <span class="ml-2">
                                        <!-- up -->
                                        <svg v-if="sorting.field == 'id' && sorting.direction == 'asc'"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                        <!-- down -->
                                        <svg v-else-if="sorting.field == 'id' && sorting.direction == 'desc'"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                        <!-- up down -->
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </span>
                                </Link>
                            </th>
                            <th scope="col" class="py-3 px-6">
                                <span>{{ __('Creation date') }}</span>
                            </th>
                            <th scope="col" class="py-3 px-6">
                                <span>{{ __('Severity') }}</span>
                            </th>
                            <th scope="col" class="py-3 px-6">
                                <span>{{ __('Type') }}</span>
                            </th>
                            <th scope="col" class="py-3 px-6">
                                <span>{{ __('Component') }}</span>
                            </th>
                            <th scope="col" class="py-3 px-6">
                                <span>{{ __('UiPath Orchestrator / Tenant') }}</span>
                            </th>
                            <th v-if="automatedProcessesCount > 0" scope="col" class="py-3 px-6">
                                <span>{{ __('Automated business process') }}</span>
                            </th>
                            <th scope="col" class="py-3 px-6 text-center">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in alerts.data" :key="item.id" class="bg-white border-b">
                            <td class="p-4 w-4">
                                <div class="flex items-center">
                                    <input type="checkbox"
                                        class="rounded border-gray-400 hover:border-gray-neutral-55 text-blue-50 shadow-sm focus:border-blue-50"
                                        v-model="selected" :name="'checkbox-' + item.id" :value="item.id" number>
                                    <label :for="'checkbox-' + item.id" class="sr-only">{{ __('Select item') }}</label>
                                </div>
                            </td>
                            <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                {{ item.id_padded }}
                            </th>
                            <td class="py-4 px-6">
                                <span :data-tooltip-target="'tooltip-default-' + item.id">
                                {{ item.creation_time_for_humans }}
                                </span>
                                <div :id="'tooltip-default-' + item.id" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-1 tooltip dark:bg-gray-700">
                                    Tooltip content
                                <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                {{ __(item.severity) }}
                            </td>
                            <td class="py-4 px-6">
                                {{ __(item.notification_name) }}
                            </td>
                            <td class="py-4 px-6">
                                {{ __(item.component) }}
                            </td>
                            <td class="py-4 px-6">
                                <dl class="max-w-md text-gray-900 divide-y divide-gray-200">
                                    <div class="flex flex-col pb-3">
                                        <dt class="mb-1 text-gray-500">{{ __('UiPath Orchestrator') }}</dt>
                                        <dd class="font-semibold">
                                            {{ item.tenant.orchestrator_connection.code }} -
                                            {{ item.tenant.orchestrator_connection.name }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col pt-3">
                                        <dt class="mb-1 text-gray-500">{{ __('Tenant') }}</dt>
                                        <dd class="font-semibold">{{ item.tenant.name }}</dd>
                                    </div>
                                </dl>
                            </td>
                            <td v-if="automatedProcessesCount > 0" class="py-4 px-6">
                                <span v-if="item.automated_process">
                                    {{ item.automated_process.code }} -
                                    {{ item.automated_process.name }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex justify-center">
                                    <button type="button"
                                        @click.prevent="edit(item)"
                                        class="inline-flex items-center px-2 py-1 border border-gray-400 text-sm leading-4 font-medium rounded-md text-gray-neutral-55 bg-white hover:bg-gray-300 hover:text-gray-500 focus:outline-none focus:bg-gray-300 active:bg-gray-300 transition mr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                        @click.prevent="triggerAction('read', item)"
                                        class="inline-flex items-center px-2 py-1 border border-gray-400 text-sm leading-4 font-medium rounded-md text-gray-neutral-55 bg-white hover:bg-gray-300 hover:text-gray-500 focus:outline-none focus:bg-gray-300 active:bg-gray-300 transition mr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                        @click.prevent="triggerAction('lock', item)"
                                        class="inline-flex items-center px-2 py-1 border border-gray-400 text-sm leading-4 font-medium rounded-md text-gray-neutral-55 bg-white hover:bg-gray-300 hover:text-gray-500 focus:outline-none focus:bg-gray-300 active:bg-gray-300 transition mr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                        @click.prevent="triggerAction('unlock', item)"
                                        class="inline-flex items-center px-2 py-1 border border-gray-400 text-sm leading-4 font-medium rounded-md text-gray-neutral-55 bg-white hover:bg-gray-300 hover:text-gray-500 focus:outline-none focus:bg-gray-300 active:bg-gray-300 transition mr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <BulkButtons class="mt-4" :selected="selected"
                    @reading="triggerBulkAction('read')"
                    @locking="triggerBulkAction('lock')"
                    @unlocking="triggerBulkAction('unlock')"
                    @cancel="resetBulkActions" />

                <!-- paginator -->
                <Pagination v-show="selected.length == 0" class="mt-4" :links="alerts.links"
                    :from="alerts.from" :to="alerts.to"
                    :total="alerts.total" />
            </div>
        </div>

        <ActionConfirmationModal :form="form" :action="singleAction" :do="singleDo" :show="confirmingAction" @close="confirmingAction = false" />
        <BulkConfirmationModal v-if="confirmingBulkAction"
            @close="resetBulkActions" :selected="selected"
            :action="bulkAction" :do="bulkDo" />
    </AppLayout>
</template>