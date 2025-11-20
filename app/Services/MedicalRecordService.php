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

            // ✅ NO eliminamos los arrays, los guardamos en el medical_record
            $diagnosisIds = $data['diagnosis_ids'] ?? [];
            $procedureIds = $data['procedure_ids'] ?? [];
            $sessionsPerProcedure = $data['sessions_per_procedure'] ?? [];
    
            $medicalRecord = MedicalRecord::create($data);

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
                            'description' => '',
                            'sessions_authorized' => $sessionsPerProcedure[$procStdId] ?? 1,
                            'sessions_completed' => 0,
                            'status' => 'pending',
                            'active' => true,
                        ]);
                    }
                }
            }

            return $medicalRecord->fresh();
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
