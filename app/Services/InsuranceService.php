<?php

namespace App\Services;

use App\Models\Insurance;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Authorization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsuranceService
{
    /**
     * Obtiene todos los seguros con filtros opcionales
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllInsurance(array $filters = [])
    {
        $query = Insurance::query();

        // Filtro por estado activo/inactivo
        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        // Filtro de búsqueda por nombre o código
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('provider_code', 'ILIKE', "%{$filters['search']}%");
            });
        }

        // Filtro específico por nombre
        if (!empty($filters['name'])) {
            $query->where('name', 'ILIKE', "%{$filters['name']}%");
        }

        // Filtro específico por código de proveedor
        if (!empty($filters['provider_code'])) {
            $query->where('provider_code', 'ILIKE', "%{$filters['provider_code']}%");
        }

        // Incluir conteo de pacientes asociados
        if (!empty($filters['with_patient_count'])) {
            $query->withCount('patients');
        }

        // Incluir conteo de citas asociadas
        if (!empty($filters['with_appointment_count'])) {
            $query->withCount('appointments');
        }

        // Paginación
        $pagination = $filters['paginate'] ?? null;

        if ($pagination) {
            return $query->orderBy('name')->simplePaginate($pagination);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Obtiene un seguro por ID con relaciones opcionales
     * 
     * @param int $id
     * @param array $with
     * @return Insurance
     */
    public function getInsuranceById($id, array $with = [])
    {
        $query = Insurance::query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    /**
     * Crea un nuevo seguro
     * 
     * @param array $data
     * @return Insurance
     */
    public function createInsurance(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Establecer valor por defecto para active si no se proporciona
            if (!isset($data['active'])) {
                $data['active'] = true;
            }

            $insurance = Insurance::create($data);

            Log::info('Seguro creado exitosamente', [
                'insurance_id' => $insurance->id,
                'name' => $insurance->name,
                'provider_code' => $insurance->provider_code,
            ]);

            return $insurance;
        });
    }

    /**
     * Actualiza un seguro existente
     * 
     * @param int $id
     * @param array $data
     * @return Insurance
     */
    public function updateInsurance($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $insurance = Insurance::findOrFail($id);
            $insurance->update($data);

            Log::info('Seguro actualizado exitosamente', [
                'insurance_id' => $insurance->id,
                'changes' => $data,
            ]);

            return $insurance->fresh();
        });
    }

    /**
     * Elimina (desactiva) un seguro
     * 
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteInsurance($id)
    {
        return DB::transaction(function () use ($id) {
            $insurance = Insurance::findOrFail($id);

            // Verificar si hay pacientes activos con este seguro
            $activePatientsCount = Patient::where('insurance_id', $id)
                ->where('active', true)
                ->count();

            if ($activePatientsCount > 0) {
                throw new \Exception(
                    "No se puede eliminar el seguro porque tiene {$activePatientsCount} paciente(s) activo(s) asociado(s)."
                );
            }

            // Verificar si hay citas futuras con este seguro
            $futureAppointmentsCount = Appointment::where('insurance_id', $id)
                ->where('appointment_date', '>=', now()->toDateString())
                ->where('active', true)
                ->count();

            if ($futureAppointmentsCount > 0) {
                throw new \Exception(
                    "No se puede eliminar el seguro porque tiene {$futureAppointmentsCount} cita(s) futura(s) asociada(s)."
                );
            }

            // Desactivar en lugar de eliminar
            $insurance->active = false;
            $insurance->save();

            Log::info('Seguro desactivado', [
                'insurance_id' => $insurance->id,
                'name' => $insurance->name,
            ]);

            return true;
        });
    }

    /**
     * Alterna el estado activo/inactivo de un seguro
     * 
     * @param int $id
     * @return Insurance
     */
    public function toggleActive($id)
    {
        return DB::transaction(function () use ($id) {
            $insurance = Insurance::findOrFail($id);
            $insurance->toggle('active');

            Log::info('Estado del seguro cambiado', [
                'insurance_id' => $insurance->id,
                'new_status' => $insurance->active ? 'activo' : 'inactivo',
            ]);

            return $insurance;
        });
    }

    /**
     * Obtiene estadísticas de uso del seguro
     * 
     * @param int $id
     * @return array
     */
    public function getInsuranceStatistics($id)
    {
        $insurance = Insurance::findOrFail($id);

        return [
            'insurance' => [
                'id' => $insurance->id,
                'name' => $insurance->name,
                'provider_code' => $insurance->provider_code,
                'active' => $insurance->active,
            ],
            'patients' => [
                'total' => Patient::where('insurance_id', $id)->count(),
                'active' => Patient::where('insurance_id', $id)->where('active', true)->count(),
            ],
            'appointments' => [
                'total' => Appointment::where('insurance_id', $id)->count(),
                'pending' => Appointment::where('insurance_id', $id)
                    ->where('status', 'programada')
                    ->where('appointment_date', '>=', now()->toDateString())
                    ->count(),
                'completed' => Appointment::where('insurance_id', $id)
                    ->where('status', 'completada')
                    ->count(),
                'cancelled' => Appointment::where('insurance_id', $id)
                    ->where('status', 'cancelada')
                    ->count(),
            ],
            'authorizations' => [
                'total' => Authorization::where('insurance_id', $id)->count(),
                'active' => Authorization::where('insurance_id', $id)
                    ->where('active', true)
                    ->count(),
                'with_pending_sessions' => Authorization::where('insurance_id', $id)
                    ->withPendingSessions()
                    ->count(),
            ],
            'recent_activity' => [
                'last_appointment' => Appointment::where('insurance_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->first()?->created_at,
                'last_authorization' => Authorization::where('insurance_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->first()?->created_at,
            ],
        ];
    }

    /**
     * Busca seguros por término de búsqueda
     * 
     * @param string $term
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchInsurance($term, $limit = 10)
    {
        return Insurance::where('active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'ILIKE', "%{$term}%")
                    ->orWhere('provider_code', 'ILIKE', "%{$term}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'provider_code']);
    }

    /**
     * Verifica si un código de proveedor ya existe
     * 
     * @param string $providerCode
     * @param int|null $excludeId
     * @return bool
     */
    public function providerCodeExists($providerCode, $excludeId = null)
    {
        $query = Insurance::where('provider_code', $providerCode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}