<?php

namespace App\Services;

use App\Models\Procedure;
use App\Models\ProcedureDiagnostic;
use Illuminate\Support\Facades\DB;

class ProcedureService
{
    public function getAllProcedures(array $filters = [])
    {
        $query = Procedure::query();

        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['appointment_id'])) {
            $query->where('appointment_id', $filters['appointment_id']);
        }

        if (!empty($filters['procedure_type_id'])) {
            $query->where('procedure_type_id', $filters['procedure_type_id']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with([
            'patient', 
            'employee', 
            'appointment', 
            'procedureType', 
            'insurance',
            'procedureDiagnostics.diagnostic'
        ])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($pagination);
    }

    public function getProcedureById($id)
    {
        return Procedure::with([
            'patient',
            'employee',
            'appointment',
            'procedureType',
            'insurance',
            'procedureDetails',
            'procedureDiagnostics.diagnostic'
        ])->findOrFail($id);
    }

    public function createProcedure(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Extraer diagnosis_ids y procedure_ids
            $diagnosisIds = $data['diagnosis_ids'] ?? [];
            $procedureStandardIds = $data['procedure_ids'] ?? [];
            
            unset($data['diagnosis_ids'], $data['procedure_ids']);

            // Crear el procedimiento principal
            $procedure = Procedure::create($data);

            // Crear relaciones con diagnósticos
            if (!empty($diagnosisIds)) {
                foreach ($diagnosisIds as $diagnosticId) {
                    ProcedureDiagnostic::create([
                        'procedure_id' => $procedure->id,
                        'diagnostic_id' => $diagnosticId,
                        'active' => true,
                    ]);
                }
            }

            // Crear relaciones con procedimientos estándar
            if (!empty($procedureStandardIds)) {
                foreach ($procedureStandardIds as $standardId) {
                    $procedure->procedureDetails()->create([
                        'procedure_standard_id' => $standardId,
                        'active' => true,
                    ]);
                }
            }

            return $procedure->load([
                'procedureDiagnostics.diagnostic',
                'procedureDetails.procedureStandard'
            ]);
        });
    }

    public function updateProcedure($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $procedure = Procedure::findOrFail($id);
            
            // Extraer diagnosis_ids y procedure_ids
            $diagnosisIds = $data['diagnosis_ids'] ?? null;
            $procedureStandardIds = $data['procedure_ids'] ?? null;
            
            unset($data['diagnosis_ids'], $data['procedure_ids']);

            // Actualizar procedimiento principal
            $procedure->update($data);

            // Actualizar diagnósticos si se proporcionaron
            if ($diagnosisIds !== null) {
                // Eliminar existentes
                ProcedureDiagnostic::where('procedure_id', $id)->delete();
                
                // Crear nuevos
                foreach ($diagnosisIds as $diagnosticId) {
                    ProcedureDiagnostic::create([
                        'procedure_id' => $procedure->id,
                        'diagnostic_id' => $diagnosticId,
                        'active' => true,
                    ]);
                }
            }

            // Actualizar procedimientos estándar si se proporcionaron
            if ($procedureStandardIds !== null) {
                // Eliminar existentes
                $procedure->procedureDetails()->delete();
                
                // Crear nuevos
                foreach ($procedureStandardIds as $standardId) {
                    $procedure->procedureDetails()->create([
                        'procedure_standard_id' => $standardId,
                        'active' => true,
                    ]);
                }
            }

            return $procedure->fresh([
                'procedureDiagnostics.diagnostic',
                'procedureDetails.procedureStandard'
            ]);
        });
    }

    public function deleteProcedure($id)
    {
        return DB::transaction(function () use ($id) {
            $procedure = Procedure::findOrFail($id);
            
            // Eliminar relaciones
            ProcedureDiagnostic::where('procedure_id', $id)->delete();
            $procedure->procedureDetails()->delete();
            
            // Eliminar procedimiento
            $procedure->delete();
            
            return true;
        });
    }
}