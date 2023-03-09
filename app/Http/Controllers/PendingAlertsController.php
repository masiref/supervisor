<?php

namespace App\Http\Controllers;

use App\Http\Resources\AutomatedProcessResource;
use App\Http\Resources\OrchestratorConnectionTenantAlertResource;
use App\Http\Resources\OrchestratorConnectionTenantResource;
use App\Models\AutomatedProcess;
use App\Models\OrchestratorConnectionTenant;
use App\Models\OrchestratorConnectionTenantAlert;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class PendingAlertsController extends Controller
{
    public function index()
    {
        request()->validate([
            'sorting.direction' => ['in:asc,desc'],
            'sorting.field' => ['in:id'],
        ]);

        $periods = 7;

        $alerts = OrchestratorConnectionTenantAlert::query()
            ->when(request('sorting'), function ($query, $sorting) {
                $query->orderBy($sorting['field'], $sorting['direction']);
            })
            ->when(!request('sorting'), function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->when(request('data.alert.creationDateRange'), function ($query, $creationDateRange) {
                $query->where('creation_time', '>=', $creationDateRange[0])
                    ->where('creation_time', '<=', $creationDateRange[1]);
            })
            ->when(request('data.alert.selectedSeverities'), function ($query, $selectedSeverities) {
                $query->whereIn('severity', $selectedSeverities);
            })
            ->when(request('data.alert.selectedNotificationNames'), function ($query, $selectedNotificationNames) {
                $query->whereIn('notification_name', $selectedNotificationNames);
            })
            ->when(request('data.alert.selectedComponents'), function ($query, $selectedComponents) {
                $query->whereIn('component', $selectedComponents);
            })
            ->where('read_at', null)
            ->paginate(config('constants.pagination.items_per_page'))
            ->withQueryString()
            ->through(fn ($alert) => [
                'id' => $alert->id,
                'id_padded' => str_pad($alert->id, 4, '0', STR_PAD_LEFT),
                'tenant' => new OrchestratorConnectionTenantResource(
                    OrchestratorConnectionTenant::with('orchestratorConnection')->find($alert->tenant_id)
                ),
                'automated_process' => $alert->automated_process_id ? new AutomatedProcessResource(
                    AutomatedProcess::find($alert->automated_process_id)
                ) : null,
                'external_id' => $alert->external_id,
                'notification_name' => $alert->notification_name,
                '_data' => $alert->data,
                'component' => $alert->component,
                'severity' => $alert->severity,
                'creation_time' => $alert->creation_time,
                'creation_time_for_humans' => Carbon::parse($alert->creation_time)->diffForHumans(Carbon::now()),
                'unread' => $alert->unread,
                'deep_link_relative_url' => $alert->deep_link_relative_url,
                'read_at' => $alert->read_at,
                'resolution_time_in_seconds' => $alert->resolution_time_in_seconds,
            ]);

        $alertsCollection = OrchestratorConnectionTenantAlertResource::collection(
            OrchestratorConnectionTenantAlert::all()->sortBy('read_at')->where('read_at', null)
        );
        $alertsCount = $alertsCollection->count();
        $alertsSeverities = $alertsCollection->pluck('severity')->unique();
        $alertsNotificationNames = $alertsCollection->pluck('notification_name')->unique();
        $alertsComponents = $alertsCollection->pluck('component')->unique();

        $closedAlerts = OrchestratorConnectionTenantAlertResource::collection(
            OrchestratorConnectionTenantAlert::all()->sortBy('read_at')->where('read_at', !null)
        );
        $closedAlertsCount = $closedAlerts->count();

        $automatedProcessesCount = AutomatedProcess::all()->count();

        $alertsAverageResolutionTimeEveryday = $this->getAlertsAverageResolutionTimeEveryday($periods);

        return Inertia::render('Dashboard/PendingAlerts/Index', [
            'alerts' => $alerts,
            'alertsCount' => $alertsCount,
            'closedAlertsCount' => $closedAlertsCount,
            'automatedProcessesCount' => $automatedProcessesCount,
            'alertsAverageResolutionTimeEveryday' => $alertsAverageResolutionTimeEveryday,
            'alertsAverageResolutionTime' => $this->getAlertsAverageResolutionTime(),
            'filters' => Request::only(['data', 'sorting']),
            'alertsProperties' => [
                'severity' => $alertsSeverities,
                'notificationName' => $alertsNotificationNames,
                'component' => $alertsComponents,
            ],
        ]);
    }

    public function edit(OrchestratorConnectionTenantAlert $alert)
    {
        return redirect()->route('pending-alerts.index');
    }

    public function read(OrchestratorConnectionTenantAlert $alert)
    {
        return redirect()->route('pending-alerts.index');
    }

    public function lock(OrchestratorConnectionTenantAlert $alert)
    {
        return redirect()->route('pending-alerts.index');
    }

    public function unlock(OrchestratorConnectionTenantAlert $alert)
    {
        return redirect()->route('pending-alerts.index');
    }

    public function bulkRead()
    {
        return redirect()->route('pending-alerts.index');
    }

    public function bulkLock()
    {
        return redirect()->route('pending-alerts.index');
    }

    public function bulkUnlock()
    {
        return redirect()->route('pending-alerts.index');
    }

    private function getAlertsAverageResolutionTimeEveryday($periods)
    {
        $format = 'd/m';

        $currentDate = Carbon::now();
        $lowerLimitDate = $currentDate->setHours(0)->setMinutes(0)->setSeconds(0)->subDays($periods);
        $collection = OrchestratorConnectionTenantAlertResource::collection(
            OrchestratorConnectionTenantAlert::all()
                ->where('read_at', !null)
                ->where('creation_time', '>=', $lowerLimitDate)
        )->collection->sortBy('creation_time');
        $data = $collection
            ->groupBy(function ($alert) use ($format) {
                return Carbon::parse($alert->creation_time)
                    ->format($format);
            })->map(function ($group) {
                return [
                    'original' => '',
                    'forHumans' => CarbonInterval::seconds($group->avg('resolution_time_in_seconds'))->cascade()->forHumans(['parts' => 2]),
                ];
            });
        $categories = array_map(function ($index) use ($format, $lowerLimitDate) {
            return $lowerLimitDate->copy()->addDays($index)->format($format);
        }, range(0, $periods));

        return [
            'data' => $data,
            'categories' => $categories,
        ];
    }

    private function getAlertsAverageResolutionTime()
    {
        $average = OrchestratorConnectionTenantAlert::all()
            ->where('read_at', !null)
            ->avg('resolution_time_in_seconds');

        if ($average) {
            return CarbonInterval::seconds($average)->cascade()->forHumans(['parts' => 2]);
        }

        return null;
    }
}
