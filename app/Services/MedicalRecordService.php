<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Procedure;
use App\Models\ProcedureDetail;
use App\Models\ProcedureDiagnostic;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    public function createMedicalRecord(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Extraer datos específicos
            $diagnosisIds = $data['diagnosis_ids'] ?? [];
            $procedureIds = $data['procedure_ids'] ?? [];
            $sessionsPerProcedure = $data['sessions_per_procedure'] ?? [];
            
            unset($data['diagnosis_ids'], $data['procedure_ids'], $data['sessions_per_procedure']);

            // 2. Crear medical_record
            $medicalRecord = MedicalRecord::create($data);

            // 3. Si tiene diagnósticos o procedimientos, crear Procedure
            if (!empty($diagnosisIds) || !empty($procedureIds)) {
                $procedure = Procedure::create([
                    'appointment_id' => $data['appointment_id'],
                    'patient_id' => $data['patient_id'],
                    'employee_id' => $data['employee_id'],
                    'procedure_date' => now(),
                    'active' => true,
                ]);

                // 4. Vincular procedure a medical_record
                $medicalRecord->update(['procedure_id' => $procedure->id]);

                // 5. Crear procedure_diagnostics
                foreach ($diagnosisIds as $diagId) {
                    ProcedureDiagnostic::create([
                        'procedure_id' => $procedure->id,
                        'diagnostic_id' => $diagId,
                        'active' => true,
                    ]);
                }

                // 6. Crear procedure_details
                foreach ($procedureIds as $procStdId) {
                    ProcedureDetail::create([
                        'procedure_id' => $procedure->id,
                        'procedure_standard_id' => $procStdId,
                        'sessions_authorized' => $sessionsPerProcedure[$procStdId] ?? 1,
                        'sessions_completed' => 0,
                        'status' => 'pending',
                        'active' => true,
                    ]);
                }
            }

            return $medicalRecord->load('procedure.procedureDetails.procedureStandard', 'procedure.procedureDiagnostics.diagnostic');
        });
    }

    public function updateMedicalRecord(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $medicalRecord = MedicalRecord::findOrFail($id);
            
            $diagnosisIds = $data['diagnosis_ids'] ?? null;
            $procedureIds = $data['procedure_ids'] ?? null;
            $sessionsPerProcedure = $data['sessions_per_procedure'] ?? [];
            
            unset($data['diagnosis_ids'], $data['procedure_ids'], $data['sessions_per_procedure']);

            // Actualizar medical_record
            $medicalRecord->update($data);

            // Si existe procedure, actualizar relaciones
            if ($medicalRecord->procedure_id) {
                $procedure = $medicalRecord->procedure;

                if ($diagnosisIds !== null) {
                    ProcedureDiagnostic::where('procedure_id', $procedure->id)->delete();
                    foreach ($diagnosisIds as $diagId) {
                        ProcedureDiagnostic::create([
                            'procedure_id' => $procedure->id,
                            'diagnostic_id' => $diagId,
                            'active' => true,
                        ]);
                    }
                }

                if ($procedureIds !== null) {
                    ProcedureDetail::where('procedure_id', $procedure->id)->delete();
                    foreach ($procedureIds as $procStdId) {
                        ProcedureDetail::create([
                            'procedure_id' => $procedure->id,
                            'procedure_standard_id' => $procStdId,
                            'sessions_authorized' => $sessionsPerProcedure[$procStdId] ?? 1,
                            'sessions_completed' => 0,
                            'status' => 'pending',
                            'active' => true,
                        ]);
                    }
                }
            } elseif (!empty($diagnosisIds) || !empty($procedureIds)) {
                // Crear procedure si no existe
                $procedure = Procedure::create([
                    'appointment_id' => $medicalRecord->appointment_id,
                    'patient_id' => $medicalRecord->patient_id,
                    'employee_id' => $medicalRecord->employee_id,
                    'procedure_date' => now(),
                    'active' => true,
                ]);

                $medicalRecord->update(['procedure_id' => $procedure->id]);

                foreach ($diagnosisIds as $diagId) {
                    ProcedureDiagnostic::create([
                        'procedure_id' => $procedure->id,
                        'diagnostic_id' => $diagId,
                        'active' => true,
                    ]);
                }

                foreach ($procedureIds as $procStdId) {
                    ProcedureDetail::create([
                        'procedure_id' => $procedure->id,
                        'procedure_standard_id' => $procStdId,
                        'sessions_authorized' => $sessionsPerProcedure[$procStdId] ?? 1,
                        'sessions_completed' => 0,
                        'status' => 'pending',
                        'active' => true,
                    ]);
                }
            }

            return $medicalRecord->fresh([
                'procedure.procedureDetails.procedureStandard',
                'procedure.procedureDiagnostics.diagnostic'
            ]);
        });
    }

    public function getByAppointment(int $appointmentId)
    {
        return MedicalRecord::with([
            'procedure.procedureDetails.procedureStandard',
            'procedure.procedureDiagnostics.diagnostic'
        ])->where('appointment_id', $appointmentId)->first();
    }

    public function getPatientHistory(int $patientId)
    {
        return MedicalRecord::with([
            'appointment',
            'procedure.procedureDetails.procedureStandard',
            'procedure.procedureDiagnostics.diagnostic'
        ])
        ->where('patient_id', $patientId)
        ->orderBy('created_at', 'desc')
        ->get();
    }
}
