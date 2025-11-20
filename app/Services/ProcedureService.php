<?php

namespace App\Services;

use App\Models\Procedure;
use App\Models\ProcedureDiagnostic;
use App\Models\ProcedureDetail;
use App\Models\DiagnosticStandard;
use App\Models\ProcedureStandard;
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
            // Extraer los arrays
            $diagnosisIds = $data['diagnosis_ids'] ?? [];
            $procedureStandardIds = $data['procedure_ids'] ?? [];
            $sessionsPerProcedure = $data['sessions_per_procedure'] ?? [];

            // Crear el procedimiento principal
            $procedure = Procedure::create($data);

            // ✅ Crear procedure_diagnostics con información completa
            if (!empty($diagnosisIds)) {
                // Buscar todos los diagnostic standards de una vez
                $diagnosticStandards = DiagnosticStandard::whereIn('id', $diagnosisIds)
                    ->get()
                    ->keyBy('id');

                foreach ($diagnosisIds as $diagnosticId) {
                    $diagnosticStandard = $diagnosticStandards->get($diagnosticId);

                    if ($diagnosticStandard) {
                        ProcedureDiagnostic::create([
                            'procedure_id' => $procedure->id,
                            'diagnostic_id' => $diagnosticId,
                            'description' => $diagnosticStandard->description,
                            'type' => $diagnosticStandard->type,
                            'chronic' => $diagnosticStandard->chronic ?? false,
                            'standard' => $diagnosticStandard->code ?? $diagnosticStandard->standard,
                            'active' => true,
                        ]);
                    }
                }
            }

            // ✅ Crear procedure_details con información completa
            if (!empty($procedureStandardIds)) {
                // Buscar todos los procedure standards de una vez
                $procedureStandards = ProcedureStandard::whereIn('id', $procedureStandardIds)
                    ->get()
                    ->keyBy('id');

                foreach ($procedureStandardIds as $standardId) {
                    $procedureStandard = $procedureStandards->get($standardId);
                    $sessionsAuthorized = $sessionsPerProcedure[$standardId] ?? 1;

                    if ($procedureStandard) {
                        ProcedureDetail::create([
                            'procedure_id' => $procedure->id,
                            'procedure_standard_id' => $standardId,
                            'description' => $procedureStandard->description,
                            'notes' => "Código: {$procedureStandard->standard} | Categoría: {$procedureStandard->category}",
                            'sessions_authorized' => $sessionsAuthorized,
                            'sessions_completed' => 0,
                            'status' => 'pending',
                            'active' => true,
                        ]);
                    }
                }
            }

            return $procedure->load([
                'procedureDiagnostics.diagnostic',
                'procedureDetails.procedureStandard'
            ]);
        });
    }

    /**
     * ✅ MODIFICADO: Busca los standards y guarda toda la información
     */
    public function updateProcedure($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $procedure = Procedure::findOrFail($id);

            // Extraer los arrays
            $diagnosisIds = $data['diagnosis_ids'] ?? null;
            $procedureStandardIds = $data['procedure_ids'] ?? null;
            $sessionsPerProcedure = $data['sessions_per_procedure'] ?? [];

            unset($data['diagnosis_ids'], $data['procedure_ids'], $data['sessions_per_procedure']);

            // Actualizar procedimiento principal
            $procedure->update($data);

            // ✅ Actualizar procedure_diagnostics con información completa
            if ($diagnosisIds !== null) {
                ProcedureDiagnostic::where('procedure_id', $id)->delete();

                if (!empty($diagnosisIds)) {
                    // Buscar todos los diagnostic standards
                    $diagnosticStandards = DiagnosticStandard::whereIn('id', $diagnosisIds)
                        ->get()
                        ->keyBy('id');

                    foreach ($diagnosisIds as $diagnosticId) {
                        $diagnosticStandard = $diagnosticStandards->get($diagnosticId);

                        if ($diagnosticStandard) {
                            ProcedureDiagnostic::create([
                                'procedure_id' => $procedure->id,
                                'diagnostic_id' => $diagnosticId,
                                'description' => $diagnosticStandard->description,
                                'type' => $diagnosticStandard->type,
                                'chronic' => $diagnosticStandard->chronic ?? false,
                                'standard' => $diagnosticStandard->code ?? $diagnosticStandard->standard,
                                'active' => true,
                            ]);
                        }
                    }
                }
            }

            // ✅ Actualizar procedure_details con información completa
            if ($procedureStandardIds !== null) {
                $procedure->procedureDetails()->delete();

                if (!empty($procedureStandardIds)) {
                    // Buscar todos los procedure standards
                    $procedureStandards = ProcedureStandard::whereIn('id', $procedureStandardIds)
                        ->get()
                        ->keyBy('id');

                    foreach ($procedureStandardIds as $standardId) {
                        $procedureStandard = $procedureStandards->get($standardId);
                        $sessionsAuthorized = $sessionsPerProcedure[$standardId] ?? 1;

                        if ($procedureStandard) {
                            ProcedureDetail::create([
                                'procedure_id' => $procedure->id,
                                'procedure_standard_id' => $standardId,
                                'description' => $procedureStandard->description,
                                'notes' => "Código: {$procedureStandard->standard} | Categoría: {$procedureStandard->category}",
                                'sessions_authorized' => $sessionsAuthorized,
                                'sessions_completed' => 0,
                                'status' => 'pending',
                                'active' => true,
                            ]);
                        }
                    }
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
