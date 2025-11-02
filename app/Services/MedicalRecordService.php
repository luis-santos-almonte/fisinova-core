<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    public function createMedicalRecord(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['diagnosis_ids'] = json_encode($data['diagnosis_ids'] ?? []);
            $data['procedure_ids'] = json_encode($data['procedure_ids'] ?? []);

            return MedicalRecord::create($data);
        });
    }

    public function updateMedicalRecord($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $record = MedicalRecord::findOrFail($id);

            if (isset($data['diagnosis_ids'])) {
                $data['diagnosis_ids'] = json_encode($data['diagnosis_ids']);
            }
            if (isset($data['procedure_ids'])) {
                $data['procedure_ids'] = json_encode($data['procedure_ids']);
            }

            $record->update($data);
            return $record;
        });
    }

    public function getByAppointment($appointmentId)
    {
        return MedicalRecord::where('appointment_id', $appointmentId)->first();
    }

    public function getPatientHistory($patientId)
    {
        return MedicalRecord::where('patient_id', $patientId)
            ->with('appointment', 'employee')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
