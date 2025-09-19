<?php

namespace App\Services;

use App\Models\Procedure;

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

        return $query->with(['patient', 'employee', 'appointment', 'procedureType', 'insurance'])
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
            'procedureDiagnostics'
        ])->findOrFail($id);
    }

    public function createProcedure(array $data)
    {
        return Procedure::create($data);
    }

    public function updateProcedure($id, array $data)
    {
        $procedure = Procedure::findOrFail($id);
        $procedure->update($data);
        return $procedure;
    }

    public function deleteProcedure($id)
    {
        $procedure = Procedure::findOrFail($id);
        $procedure->delete();
        return true;
    }
}
