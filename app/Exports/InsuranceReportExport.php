<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InsuranceReportExport implements FromArray, WithTitle, ShouldAutoSize
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    /**
     * Retornar datos como array simple
     */
    public function array(): array
    {
        $rows = [];
        $insurance = $this->reportData['insurance'];
        $company = $this->reportData['company'];
        $isIdoppril = $this->reportData['is_idoppril'] ?? false;
        
        // Cabecera del reporte
        if ($isIdoppril) {
            $rows[] = ['RECLAMACION POR SERVICIO PRESTADO A IDOPPRIL'];
        } else {
            $rows[] = ['RECLAMACION POR SERVICIO PRESTADO A ' . strtoupper($insurance->name)];
        }
        
        $rows[] = [];
        $rows[] = ['NOMBRE DEL ESTABLECIMIENTO:', $company['name']];
        $rows[] = ['RNC:', $company['rnc']];
        
        if (!$isIdoppril) {
            $rows[] = ['CODIGO PSS:', $insurance->provider_code ?? 'N/A'];
        }
        
        $rows[] = ['CIUDAD:', $company['city']];
        $rows[] = ['TELEFONO:', $company['phone']];
        $rows[] = ['FECHA:', now()->format('d/m/Y')];
        $rows[] = [];
        
        // Cabeceras de tabla
        $headers = ['#', 'FECHA', 'NOMBRE DEL AFILIADO'];
        
        if ($isIdoppril) {
            $headers[] = 'NO. CASO';
        } else {
            $headers[] = 'NO. AFILIADO';
        }
        
        $headers = array_merge($headers, [
            'NO. AUTORIZACION',
            'PROCEDIMIENTO',
            $isIdoppril ? 'MONTO IDOPPRIL' : 'MONTO SEGURO',
            'COPAGO PACIENTE',
            'MONTO TOTAL'
        ]);
        
        $rows[] = $headers;
        
        // Datos de servicios
        $index = 1;
        foreach ($this->reportData['services'] as $service) {
            $row = [
                $index++,
                date('d/m/Y', strtotime($service->authorization_date)),
                $service->patient_name . ' ' . $service->patient_last_name,
            ];
            
            if ($isIdoppril) {
                $row[] = $service->case_number ?? 'N/A';
            } else {
                $row[] = $service->patient_insurance_code ?? 'N/A';
            }
            
            $row = array_merge($row, [
                $service->authorization_number,
                $service->procedure_description,
                '$' . number_format($service->insurance_amount, 2),
                '$' . number_format($service->patient_amount, 2),
                '$' . number_format($service->total_amount, 2),
            ]);
            
            $rows[] = $row;
        }
        
        // Totales
        $summary = $this->reportData['summary'];
        $rows[] = [
            '', '', '', '', '', 'TOTAL',
            '$' . number_format($summary['total_insurance_amount'], 2),
            '$' . number_format($summary['total_patient_amount'], 2),
            '$' . number_format($summary['total_amount'], 2),
        ];
        
        return $rows;
    }

    public function title(): string
    {
        if ($this->reportData['is_idoppril'] ?? false) {
            return 'IDOPPRIL';
        }
        
        return strtoupper($this->reportData['insurance']->name);
    }
}