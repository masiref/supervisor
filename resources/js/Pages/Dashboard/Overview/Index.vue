<script setup>
import { ref, onMounted, inject } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageContentHeader from '@/Components/PageContentHeader.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import Navbar from '../Navbar.vue';
import Tiles from './Partials/Tiles.vue';
import AlertsIndicators from './Partials/AlertsIndicators.vue';
import OrchestratorConnectionsChart from './Charts/OrchestratorConnections.vue';
import OrchestratorConnectionTenantsChart from './Charts/OrchestratorConnectionTenants.vue';
import MostAlertingEntitiesChart from './Charts/MostAlertingEntities.vue';
import AutomatedProcessesHealthIndicator from './Charts/AutomatedProcessesHealthIndicator.vue';
import AlertsOverTimeChart from './Charts/AlertsOverTime.vue';

const translate = inject('translate');

const chartConfiguration = {
    labels: {
        connectivityStatuses: [
            translate('Verified'),
            translate('Not verified yet'),
            translate('Invalid'),
        ],
    },
    colors: {
        connectivityStatuses: [
            '#52c41a', // green-50
            '#faaf14', // warning-50
            '#f5222d', // error-50
        ],
        mostAlertingEntities: {
            orchestratorConnections: {
                bar: '#a78bfa', // violet-400
                text: '#4c1d95', // violet-900
            },
            orchestratorConnectionTenants: {
                bar: '#38c6f4', // cyan-50
                text: '#164e63', // cyan-900
            },
            automatedProcesses: {
                bar: '#f472b6', // pink-400
                text: '#831843', // pink-900
            },
        },
    },
};

const props = defineProps({
    orchestratorConnections: Array,
    orchestratorConnectionTenants: Array,
    automatedProcesses: Array,
    pendingAlertsCount: Number,
    closedAlertsCount: Number,
    mostAlertingAutomatedProcesses: Array,
    alerts: Object,
});

const orchestratorConnectionsCount = ref(props.orchestratorConnections.length);
const automatedProcessesCount = ref(props.automatedProcesses.length);

const pendingAlertsCount = ref(props.pendingAlertsCount);
const newPendingAlertsCount = ref(0);

const closedAlertsCount = ref(props.closedAlertsCount);
const newClosedAlertsCount = ref(0);

const alertsOverTime = ref(props.alerts.overTime);
const alertsEveryFifteenMinutes = ref(props.alerts.everyFifteenMinutes.data);
const alertsEveryFifteenMinutesCategories = ref(props.alerts.everyFifteenMinutes.categories);
const alertsEveryHour = ref(props.alerts.everyHour.data);
const alertsEveryHourCategories = ref(props.alerts.everyHour.categories);
const alertsEveryFourHours = ref(props.alerts.everyFourHours.data);
const alertsEveryFourHoursCategories = ref(props.alerts.everyFourHours.categories);
const alertsEveryday = ref(props.alerts.everyday.data);
const alertsEverydayCategories = ref(props.alerts.everyday.categories);
const alertsEveryWeek = ref(props.alerts.everyWeek.data);
const alertsEveryWeekCategories = ref(props.alerts.everyWeek.categories);
const alertsEveryMonth = ref(props.alerts.everyMonth.data);
const alertsEveryMonthCategories = ref(props.alerts.everyMonth.categories);

onMounted(() => {
    Echo.channel('orchestrator-connection')
        .listen('.new', (data) => {
            console.log(data);
            orchestratorConnectionsCount.value++;
        });
    Echo.channel('automated-process')
        .listen('.new', (data) => {
            console.log(data);
            automatedProcessesCount.value++;
        });
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
    <AppLayout :title="__('Dashboard') + ' > ' + __('Overview')">
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
                <div class="grid grid-cols-4 gap-4">
                    <!-- Tiles -->
                    <Tiles :orchestrator-connections="orchestratorConnections"
                        :automated-processes="automatedProcesses"
                        :pending-alerts-count="pendingAlertsCount" />

                    <div class="col-span-4">
                        <SectionBorder />
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-8 h-8 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5" />
                            </svg>
                            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                                {{ __('Alerts indicators') }}
                            </div>
                        </div>
                        <div class="grid grid-cols-6 gap-4 mt-4">
                            <!-- Alerts indicators -->
                            <AlertsIndicators :pending-alerts-count="pendingAlertsCount"
                                :alerts-every-fifteen-minutes="alertsEveryFifteenMinutes"
                                :alerts-every-fifteen-minutes-categories="alertsEveryFifteenMinutesCategories"
                                :alerts-every-hour="alertsEveryHour"
                                :alerts-every-hour-categories="alertsEveryHourCategories"
                                :alerts-every-four-hours="alertsEveryFourHours"
                                :alerts-every-four-hours-categories="alertsEveryFourHoursCategories"
                                :alerts-everyday="alertsEveryday"
                                :alerts-everyday-categories="alertsEverydayCategories"
                                :alerts-every-week="alertsEveryWeek"
                                :alerts-every-week-categories="alertsEveryWeekCategories"
                                :alerts-every-month="alertsEveryMonth"
                                :alerts-every-month-categories="alertsEveryMonthCategories" />

                                <!-- Alerts over time chart -->
                                <AlertsOverTimeChart :alerts-over-time="alertsOverTime" />
                        </div>

                        <SectionBorder />
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                                {{ __('Other indicators') }}
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-4 mt-4">
                            <!-- Orchestrator connections chart -->
                            <OrchestratorConnectionsChart :orchestrator-connections="orchestratorConnections"
                                :chart-configuration="chartConfiguration" />

                            <!-- Most alerting orchestrator connections chart -->
                            <MostAlertingEntitiesChart
                                id="most-alerting-orchestrator-connections-bar-chart"
                                :entities="orchestratorConnections"
                                :title="__('Most alerting orchestrator connections')"
                                :colors="{
                                    bar: chartConfiguration.colors.mostAlertingEntities.orchestratorConnections.bar,
                                    text: chartConfiguration.colors.mostAlertingEntities.orchestratorConnections.text
                                }" />

                            <!-- Automated processes health indicator -->
                            <AutomatedProcessesHealthIndicator :automated-processes="automatedProcesses" />

                            <!-- Orchestrator connection tenants chart -->
                            <OrchestratorConnectionTenantsChart :orchestrator-connection-tenants="orchestratorConnectionTenants"
                            :chart-configuration="chartConfiguration" />

                            <!-- Most alerting orchestrator connection tenants chart -->
                            <MostAlertingEntitiesChart
                                id="most-alerting-orchestrator-connection-tenants-bar-chart"
                                :entities="orchestratorConnectionTenants.map(tenant => {
                                    return {
                                        code: tenant.orchestrator_connection.code,
                                        name: tenant.name,
                                        alerts_count: tenant.alerts_count,
                                    };
                                })"
                                :title="__('Most alerting orchestrator connection tenants')"
                                :colors="{
                                    bar: chartConfiguration.colors.mostAlertingEntities.orchestratorConnectionTenants.bar,
                                    text: chartConfiguration.colors.mostAlertingEntities.orchestratorConnectionTenants.text
                                }" />

                            <!-- Most alerting automated processes chart -->
                            <MostAlertingEntitiesChart
                                id="most-alerting-automated-processes-bar-chart"
                                :entities="automatedProcesses"
                                :title="__('Most alerting automated business processes')"
                                :colors="{
                                    bar: chartConfiguration.colors.mostAlertingEntities.automatedProcesses.bar,
                                    text: chartConfiguration.colors.mostAlertingEntities.automatedProcesses.text
                                }" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
