<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InsuranceReportExport implements 
    FromCollection, 
    WithHeadings, 
    WithStyles, 
    WithTitle,
    WithColumnWidths
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        $rows = collect();
        $insurance = $this->reportData['insurance'];
        $company = $this->reportData['company'];
        $isWorkplaceRisk = $this->reportData['is_workplace_risk'];
        
        // Cabecera del reporte
        $rows->push(['RECLAMACION POR SERVICIO PRESTADO A ' . strtoupper($insurance->name)]);
        $rows->push(['']);
        $rows->push(['NOMBRE DEL ESTABLECIMIENTO:', $company['name']]);
        $rows->push(['RNC:', $company['rnc']]);
        $rows->push(['CODIGO PSS:', $insurance->provider_code ?? 'N/A']);
        $rows->push(['CIUDAD:', $company['city']]);
        $rows->push(['TELEFONO:', $company['phone']]);
        $rows->push(['FECHA:', now()->format('d/m/Y')]);
        $rows->push(['']);
        
        // Cabeceras de tabla
        $headers = ['#', 'FECHA', 'NOMBRE DEL AFILIADO'];
        
        if ($isWorkplaceRisk) {
            $headers[] = 'NO. CASO';
        } else {
            $headers[] = 'NO. AFILIADO';
        }
        
        $headers = array_merge($headers, [
            'NO. AUTORIZACION',
            'PROCEDIMIENTO',
            'MONTO SEGURO',
            'COPAGO PACIENTE',
            'MONTO TOTAL'
        ]);
        
        $rows->push($headers);
        
        // Datos de servicios
        $index = 1;
        foreach ($this->reportData['services'] as $service) {
            $row = [
                $index++,
                date('d/m/Y', strtotime($service->authorization_date)),
                $service->patient_name . ' ' . $service->patient_last_name,
            ];
            
            if ($isWorkplaceRisk) {
                $row[] = $service->case_number ?? 'N/A';
            } else {
                $row[] = $service->patient_insurance_code;
            }
            
            $row = array_merge($row, [
                $service->authorization_number,
                $service->procedure_description,
                '$' . number_format($service->insurance_amount, 2),
                '$' . number_format($service->patient_amount, 2),
                '$' . number_format($service->total_amount, 2),
            ]);
            
            $rows->push($row);
        }
        
        // Totales
        $summary = $this->reportData['summary'];
        $rows->push([
            '', '', '', '', '', 'TOTAL',
            '$' . number_format($summary['total_insurance_amount'], 2),
            '$' . number_format($summary['total_patient_amount'], 2),
            '$' . number_format($summary['total_amount'], 2),
        ]);
        
        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            10 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 30,
            'D' => 15,
            'E' => 15,
            'F' => 20,
            'G' => 15,
            'H' => 15,
            'I' => 15,
        ];
    }

    public function title(): string
    {
        return strtoupper($this->reportData['insurance']->name);
    }
}