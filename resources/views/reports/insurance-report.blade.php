<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de {{ $is_idoppril ? 'IDOPPRIL' : $insurance->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 5px;
        }
        
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .company-info {
            margin: 15px 0;
            padding: 8px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        
        .company-info table {
            width: 100%;
        }
        
        .company-info td {
            padding: 3px 8px;
            font-size: 9pt;
        }
        
        .company-info td:first-child {
            font-weight: bold;
            width: 30%;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        table.data-table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #000;
        }
        
        table.data-table td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        table.data-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .total-row {
            background-color: #e8f4f8 !important;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid #000 !important;
        }
        
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
        }
        
        .summary h3 {
            font-size: 11pt;
            margin-bottom: 8px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        
        .summary-item {
            padding: 5px;
            background-color: white;
            border: 1px solid #ddd;
        }
        
        .summary-label {
            font-size: 8pt;
            color: #666;
        }
        
        .summary-value {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 7pt;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        @if(file_exists(public_path('images/Logo.png')))
            <img src="{{ public_path('images/Logo.png') }}" alt="Logo" class="logo">
        @endif
        <h1>
            RECLAMACIÓN POR SERVICIO PRESTADO A 
            {{ $is_idoppril ? 'IDOPPRIL' : strtoupper($insurance->name) }}
        </h1>
    </div>

    <!-- INFORMACIÓN DE LA EMPRESA -->
    <div class="company-info">
        <table>
            <tr>
                <td>NOMBRE DEL ESTABLECIMIENTO:</td>
                <td>{{ $company['name'] }}</td>
            </tr>
            <tr>
                <td>RNC:</td>
                <td>{{ $company['rnc'] }}</td>
            </tr>
            @if(!$is_idoppril)
            <tr>
                <td>CÓDIGO PSS:</td>
                <td>{{ $insurance->provider_code ?? 'N/A' }}</td>
            </tr>
            @endif
            <tr>
                <td>CIUDAD:</td>
                <td>{{ $company['city'] }}</td>
            </tr>
            <tr>
                <td>TELÉFONO:</td>
                <td>{{ $company['phone'] }}</td>
            </tr>
            <tr>
                <td>PERÍODO:</td>
                <td>{{ \Carbon\Carbon::parse($period['start'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>FECHA DE GENERACIÓN:</td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <!-- TABLA DE SERVICIOS -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 10%;">FECHA</th>
                <th style="width: 25%;">NOMBRE DEL AFILIADO</th>
                <th style="width: 15%;">{{ $is_idoppril ? 'NO. CASO' : 'NO. AFILIADO' }}</th>
                <th style="width: 15%;">NO. AUTORIZACIÓN</th>
                <th style="width: 15%;">PROCEDIMIENTO</th>
                <th style="width: 10%;">{{ $is_idoppril ? 'MONTO IDOPPRIL' : 'MONTO SEGURO' }}</th>
                <th style="width: 10%;">COPAGO PACIENTE</th>
                <th style="width: 10%;">MONTO TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $index => $service)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($service->authorization_date)->format('d/m/Y') }}</td>
                <td>{{ $service->patient_name }} {{ $service->patient_last_name }}</td>
                <td class="text-center">
                    {{ $is_idoppril 
                        ? ($service->case_number ?? 'N/A') 
                        : ($service->patient_insurance_code ?? 'N/A') 
                    }}
                </td>
                <td class="text-center">{{ $service->authorization_number }}</td>
                <td class="text-center">{{ $service->procedure_description }}</td>
                <td class="text-right">${{ number_format($service->insurance_amount, 2) }}</td>
                <td class="text-right">${{ number_format($service->patient_amount, 2) }}</td>
                <td class="text-right">${{ number_format($service->total_amount, 2) }}</td>
            </tr>
            @endforeach
            
            <!-- FILA DE TOTALES -->
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL</td>
                <td class="text-right">${{ number_format($summary['total_insurance_amount'], 2) }}</td>
                <td class="text-right">${{ number_format($summary['total_patient_amount'], 2) }}</td>
                <td class="text-right">${{ number_format($summary['total_amount'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- RESUMEN -->
    <div class="summary">
        <h3>RESUMEN DEL REPORTE</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total de Servicios:</div>
                <div class="summary-value">{{ $summary['total_services'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Monto Total:</div>
                <div class="summary-value">${{ number_format($summary['total_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Consultas:</div>
                <div class="summary-value">{{ $summary['consultations_count'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Terapias:</div>
                <div class="summary-value">{{ $summary['therapies_count'] }}</div>
            </div>
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p>{{ $company['name'] }} - {{ $company['city'] }}</p>
        <p>Reporte generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}</p>
    </div>
</body>
</html>