<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class AutomatedProcess extends Model
{
    use HasFactory, HasTags;

    protected $fillable = [
        'code',
        'name',
    ];

    public function code(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtolower($value)
        );
    }

    protected function health(): Attribute
    {
        return Attribute::make(function () {
            $health = [];

            $releases = $this->releases;
            $alertingReleases = $this->releases()->withCount('alerts')->get()->where('alerts_count', '>', 0);
            $releasesHealth = 1;
            if (count($releases) > 0) {
                $releasesHealth = 1 - count($alertingReleases) / count($releases);
            }
            array_push($health, $releasesHealth);

            $machines = $this->machines;
            $alertingMachines = $this->machines()->withCount('alerts')->get()->where('alerts_count', '>', 0);
            $machinesHealth = 1;
            if (count($machines) > 0) {
                $machinesHealth = 1 - count($alertingMachines) / count($machines);
            }
            array_push($health, $machinesHealth);

            $queues = $this->queues;
            $alertingQueues = $this->queues()->withCount('alerts')->get()->where('alerts_count', '>', 0);
            $queuesHealth = 1;
            if (count($queues) > 0) {
                $queuesHealth = 1 - count($alertingQueues) / count($queues);
            }
            array_push($health, $queuesHealth);

            return array_sum($health) / count($health);
        });
    }

    public function orchestratorConnections()
    {
        return $this->belongsToMany(
            OrchestratorConnection::class,
            'orchestrator_connection_automated_process',
            'automated_process_id',
            'orchestrator_connection_id',
        )
            ->withPivot(['built_in_alerts', 'processes_supervision', 'machines_supervision', 'queues_supervision', 'kibana_built_in_alerts'])
            ->using(AutomatedProcessOrchestratorConnection::class)
            ->withTimestamps();
    }

    public function orchestratorConnectionTenants()
    {
        return $this->belongsToMany(
            OrchestratorConnectionTenant::class,
            'automated_process_orchestrator_connection_tenant',
            'automated_process_id',
            'tenant_id',
        )
            ->withPivot(['built_in_alerts', 'processes_supervision', 'machines_supervision', 'queues_supervision', 'kibana_built_in_alerts'])
            ->using(AutomatedProcessOrchestratorConnectionTenant::class)
            ->withTimestamps();
    }

    public function releases()
    {
        return $this->belongsToMany(
            OrchestratorConnectionTenantRelease::class,
            'automated_process_orchestrator_connection_tenant_release',
            'automated_process_id',
            'release_id',
        )->withTimestamps();
    }

    public function machines()
    {
        return $this->belongsToMany(
            OrchestratorConnectionTenantMachine::class,
            'automated_process_orchestrator_connection_tenant_machine',
            'automated_process_id',
            'machine_id',
        )->withTimestamps();
    }

    public function queues()
    {
        return $this->belongsToMany(
            OrchestratorConnectionTenantQueue::class,
            'automated_process_orchestrator_connection_tenant_queue',
            'automated_process_id',
            'queue_id',
        )->withTimestamps();
    }

    public function properties()
    {
        return $this->belongsToMany(
            Property::class,
            'automated_process_property',
            'automated_process_id',
            'property_id',
        )->withTimestamps();
    }

    public function alerts()
    {
        return $this->hasMany(OrchestratorConnectionTenantAlert::class, 'automated_process_id');
    }
}
