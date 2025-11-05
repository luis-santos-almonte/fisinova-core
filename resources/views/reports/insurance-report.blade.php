<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamación {{ $insurance->name }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
        }
        
        .header {
            text-align: left;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            text-align: center;
        }
        
        .header-info {
            margin: 3px 0;
            font-size: 10pt;
        }
        
        .header-info strong {
            display: inline-block;
            min-width: 220px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9pt;
        }
        
        table th {
            background-color: #e8e8e8;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }
        
        table td {
            border: 1px solid #000;
            padding: 5px 4px;
            font-size: 9pt;
        }
        
        table td.number {
            text-align: center;
            width: 25px;
        }
        
        table td.date {
            text-align: center;
            width: 70px;
        }
        
        table td.amount {
            text-align: right;
            width: 85px;
        }
        
        table td.code {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            text-align: center;
        }
        
        table td.procedure {
            text-align: center;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid #000;
            padding: 8px 4px;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 8pt;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- CABECERA DEL REPORTE -->
    <div class="header">
        <h1>
            @if($is_idoppril)
                RECLAMACIÓN POR SERVICIO PRESTADO A IDOPPRIL
            @else
                RECLAMACIÓN POR SERVICIO PRESTADO A {{ strtoupper($insurance->name) }}
            @endif
        </h1>
        
        <div class="header-info">
            <strong>NOMBRE DEL ESTABLECIMIENTO:</strong> 
            {{ $company['name'] }}
        </div>
        
        <div class="header-info">
            <strong>RNC:</strong> 
            {{ $company['rnc'] }}
        </div>
        
        @if(!$is_idoppril)
        <div class="header-info">
            <strong>CODIGO PSS:</strong> 
            {{ $insurance->provider_code ?? 'N/A' }}
        </div>
        @endif
        
        <div class="header-info">
            <strong>CIUDAD:</strong> 
            {{ $company['city'] }}
        </div>
        
        <div class="header-info">
            <strong>TELEFONO:</strong> 
            {{ $company['phone'] }}
        </div>
        
        <div class="header-info">
            <strong>FECHA:</strong> 
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- TABLA DE SERVICIOS -->
    <table>
        <thead>
            <tr>
                <th class="number">#</th>
                <th class="date">FECHA</th>
                <th>NOMBRE DEL AFILIADO</th>
                <th>
                    @if($is_idoppril)
                        NO. CASO
                    @else
                        NO. AFILIADO
                    @endif
                </th>
                <th>NO. AUTORIZACION</th>
                <th>PROCEDIMIENTO</th>
                <th class="amount">
                    @if($is_idoppril)
                        MONTO IDOPPRIL
                    @else
                        MONTO SEGURO
                    @endif
                </th>
                <th class="amount">COPAGO PACIENTE</th>
                <th class="amount">MONTO TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $index = 1; @endphp
            @foreach($services as $service)
            <tr>
                <td class="number">{{ $index++ }}</td>
                <td class="date">{{ \Carbon\Carbon::parse($service->authorization_date)->format('d/m/Y') }}</td>
                <td>{{ $service->patient_name }} {{ $service->patient_last_name }}</td>
                <td class="code">
                    @if($is_idoppril)
                        {{ $service->case_number ?? 'N/A' }}
                    @else
                        {{ $service->patient_insurance_code ?? 'N/A' }}
                    @endif
                </td>
                <td class="code">{{ $service->authorization_number }}</td>
                <td class="procedure">{{ $service->procedure_description }}</td>
                <td class="amount">${{ number_format($service->insurance_amount, 2) }}</td>
                <td class="amount">${{ number_format($service->patient_amount, 2) }}</td>
                <td class="amount">${{ number_format($service->total_amount, 2) }}</td>
            </tr>
            @endforeach
            
            <!-- FILA DE TOTAL -->
            <tr class="total-row">
                <td colspan="6" style="text-align: right; padding-right: 10px;">
                    <strong>TOTAL:</strong>
                </td>
                <td class="amount">
                    <strong>${{ number_format($summary['total_insurance_amount'], 2) }}</strong>
                </td>
                <td class="amount">
                    <strong>${{ number_format($summary['total_patient_amount'], 2) }}</strong>
                </td>
                <td class="amount">
                    <strong>${{ number_format($summary['total_amount'], 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p>{{ $company['name'] }} | RNC: {{ $company['rnc'] }}</p>
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de servicios: {{ $summary['total_services'] }}</p>
    </div>
</body>
</html>