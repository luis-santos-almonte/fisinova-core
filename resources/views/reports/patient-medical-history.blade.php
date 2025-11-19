<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - {{ $patient->firstname }} {{ $patient->lastname }}</title>
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
            color: #333;
            background-color: #fff;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db;
        }
        
        .logo {
            max-width: 120px;
            margin-bottom: 8px;
        }
        
        h1 {
            font-size: 16pt;
            color: #2c3e50;
            margin: 8px 0 4px 0;
        }
        
        .subtitle {
            font-size: 10pt;
            color: #7f8c8d;
        }
        
        /* ✅ DATOS DEL PACIENTE - FORMATO FORMULARIO */
        .patient-info {
            background-color: #ecf0f1;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .patient-info h2 {
            font-size: 11pt;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 4px;
        }
        
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 6px;
        }
        
        .form-field {
            display: flex;
            background-color: white;
            padding: 4px 8px;
            border-radius: 3px;
            align-items: center;
        }
        
        .form-field.flex-2 { flex: 2; }
        .form-field.flex-1 { flex: 1; }
        .form-field.flex-3 { flex: 3; }
        
        .field-label {
            font-weight: bold;
            color: #555;
            font-size: 8pt;
            margin-right: 6px;
            white-space: nowrap;
        }
        
        .field-value {
            color: #333;
            font-size: 8pt;
        }
        
        /* ESTADÍSTICAS */
        .stats-section {
            background-color: #e8f4f8;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        
        .stat-card {
            text-align: center;
        }
        
        .stat-number {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            font-size: 8pt;
            color: #666;
            margin-top: 3px;
        }
        
        /* ANTECEDENTES */
        .antecedentes-section {
            background-color: #fff9e6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 4px solid #f39c12;
        }
        
        .antecedentes-section h2 {
            font-size: 10pt;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .antecedentes-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .antecedente-item {
            flex: 1 1 48%;
            font-size: 8pt;
            padding: 4px 6px;
            background-color: white;
            border-radius: 2px;
        }
        
        .antecedente-label {
            font-weight: bold;
            color: #555;
        }
        
        /* TABLAS */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding: 6px 10px;
            background-color: #ecf0f1;
            border-left: 4px solid #3498db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }
        
        table thead {
            background-color: #3498db;
            color: white;
        }
        
        table th,
        table td {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* ✅ SALTO DE PÁGINA PARA TERAPIAS */
        .therapy-section {
            page-break-before: always;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #ccc;
            text-align: center;
            font-size: 7pt;
            color: #666;
        }
        
        @media print {
            body { padding: 10px; }
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        @if(file_exists(public_path('images/Logo.png')))
            <img src="{{ public_path('images/Logo.png') }}" alt="Logo" class="logo">
        @endif
        <h1>HISTORIAL MÉDICO COMPLETO</h1>
        <div class="subtitle">{{ $company['name'] }}</div>
    </div>

    <!-- ✅ DATOS DEL PACIENTE - FORMATO FORMULARIO HORIZONTAL -->
    <div class="patient-info">
        <h2>DATOS DEL PACIENTE</h2>
        
        <!-- Fila 1: Nombre, Cédula -->
        <div class="form-row">
            <div class="form-field flex-3">
                <span class="field-label">Nombre Completo:</span>
                <span class="field-value">{{ $patient->firstname }} {{ $patient->lastname }}</span>
            </div>
            <div class="form-field flex-2">
                <span class="field-label">Cédula:</span>
                <span class="field-value">{{ $patient->dni ?? 'N/A' }}</span>
            </div>
        </div>
        
        <!-- Fila 2: Fecha Nacimiento, Edad, Sexo -->
        <div class="form-row">
            <div class="form-field flex-2">
                <span class="field-label">Fecha de Nacimiento:</span>
                <span class="field-value">
                    {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'N/A' }}
                </span>
            </div>
            <div class="form-field flex-1">
                <span class="field-label">Edad:</span>
                <span class="field-value">
                    {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->age . ' años' : 'N/A' }}
                </span>
            </div>
            <div class="form-field flex-1">
                <span class="field-label">Sexo:</span>
                <span class="field-value">{{ $patient->sex === 'M' ? 'Masculino' : ($patient->sex === 'F' ? 'Femenino' : 'N/A') }}</span>
            </div>
        </div>
        
        <!-- Fila 3: Teléfono, Email -->
        <div class="form-row">
            <div class="form-field flex-1">
                <span class="field-label">Teléfono:</span>
                <span class="field-value">{{ $patient->cellphone ?? $patient->phone ?? 'N/A' }}</span>
            </div>
            <div class="form-field flex-2">
                <span class="field-label">Email:</span>
                <span class="field-value">{{ $patient->email ?? 'N/A' }}</span>
            </div>
        </div>
        
        <!-- Fila 4: Seguro, No. Afiliado -->
        @if($patient->insurance)
        <div class="form-row">
            <div class="form-field flex-2">
                <span class="field-label">Seguro Médico:</span>
                <span class="field-value">{{ $patient->insurance->name }}</span>
            </div>
            <div class="form-field flex-1">
                <span class="field-label">No. de Afiliado:</span>
                <span class="field-value">{{ $patient->insurance_code ?? 'N/A' }}</span>
            </div>
        </div>
        @endif
        
        <!-- Fila 5: Ciudad, Dirección -->
        <div class="form-row">
            <div class="form-field flex-1">
                <span class="field-label">Ciudad:</span>
                <span class="field-value">{{ $patient->city ?? 'N/A' }}</span>
            </div>
            <div class="form-field flex-2">
                <span class="field-label">Dirección:</span>
                <span class="field-value">{{ $patient->address ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- ✅ ESTADÍSTICAS SIN DIAGNÓSTICOS -->
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_consultations'] }}</div>
            <div class="stat-label">Consultas Médicas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_therapies'] }}</div>
            <div class="stat-label">Sesiones de Terapia</div>
        </div>
        <div class="stat-card" style="text-align: left;">
            <div style="font-size: 8pt; color: #555;">
                <strong>Primera Consulta:</strong> {{ $stats['first_consultation'] ?? 'N/A' }}<br>
                <strong>Última Consulta:</strong> {{ $stats['last_consultation'] ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- ✅ ÁREA DE ANTECEDENTES -->
    @php
        $lastRecordWithAntecedents = $medical_records->first(function($record) {
            return $record->allergies || $record->current_medications || 
                   $record->other_conditions || $record->family_history ||
                   $record->has_diabetes || $record->has_hypertension || 
                   $record->has_asthma || $record->previous_surgeries ||
                   $record->smokes || $record->drinks_alcohol;
        });
    @endphp

    @if($lastRecordWithAntecedents && ($options['include_medical_history'] ?? true))
    <div class="antecedentes-section">
        <h2>ANTECEDENTES MÉDICOS (Última actualización: {{ \Carbon\Carbon::parse($lastRecordWithAntecedents->created_at)->format('d/m/Y') }})</h2>
        <div class="antecedentes-grid">
            @if($lastRecordWithAntecedents->allergies)
            <div class="antecedente-item">
                <span class="antecedente-label">Alergias:</span> {{ $lastRecordWithAntecedents->allergies }}
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->current_medications)
            <div class="antecedente-item">
                <span class="antecedente-label">Medicamentos:</span> {{ $lastRecordWithAntecedents->current_medications }}
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->has_diabetes || $lastRecordWithAntecedents->has_hypertension || $lastRecordWithAntecedents->has_asthma)
            <div class="antecedente-item">
                <span class="antecedente-label">Condiciones:</span>
                @if($lastRecordWithAntecedents->has_diabetes) Diabetes @endif
                @if($lastRecordWithAntecedents->has_hypertension) Hipertensión @endif
                @if($lastRecordWithAntecedents->has_asthma) Asma @endif
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->other_conditions)
            <div class="antecedente-item">
                <span class="antecedente-label">Otras:</span> {{ $lastRecordWithAntecedents->other_conditions }}
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->previous_surgeries)
            <div class="antecedente-item">
                <span class="antecedente-label">Cirugías:</span> {{ $lastRecordWithAntecedents->previous_surgeries }}
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->family_history)
            <div class="antecedente-item">
                <span class="antecedente-label">Familiares:</span> {{ $lastRecordWithAntecedents->family_history }}
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->smokes)
            <div class="antecedente-item">
                <span class="antecedente-label">Tabaquismo:</span> {{ $lastRecordWithAntecedents->smoking_frequency ?? 'Sí' }}
            </div>
            @endif
            
            @if($lastRecordWithAntecedents->drinks_alcohol)
            <div class="antecedente-item">
                <span class="antecedente-label">Alcohol:</span> {{ $lastRecordWithAntecedents->alcohol_frequency ?? 'Sí' }}
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ✅ HISTORIAL DE CONSULTAS MÉDICAS - TABLA -->
    @if($medical_records->count() > 0)
    <div class="section-title">HISTORIAL DE CONSULTAS MÉDICAS</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 15%;">Médico</th>
                <th style="width: 20%;">Motivo</th>
                <th style="width: 25%;">Diagnósticos CIE-10</th>
                <th style="width: 30%;">Procedimientos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medical_records as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y') }}</td>
                <td>
                    Dr. {{ $record->appointment->employee->firstname ?? '' }} 
                    {{ $record->appointment->employee->lastname ?? '' }}
                </td>
                <td>{{ $record->chief_complaint ?? 'N/A' }}</td>
                <td>
                    {{-- ✅ TODOS LOS DIAGNÓSTICOS --}}
                    @if($record->procedure && $record->procedure->procedureDiagnostics->count() > 0)
                        @foreach($record->procedure->procedureDiagnostics as $diag)
                            <div style="margin-bottom: 3px;">
                                <strong>{{ $diag->diagnostic->code ?? '' }}</strong> - {{ $diag->diagnostic->description ?? '' }}
                            </div>
                        @endforeach
                    @else
                        <em style="color: #999;">Sin diagnósticos</em>
                    @endif
                </td>
                <td>
                    {{-- ✅ TODOS LOS PROCEDIMIENTOS --}}
                    @if($record->procedure && $record->procedure->procedureDetails->count() > 0)
                        @foreach($record->procedure->procedureDetails as $detail)
                            <div style="margin-bottom: 3px;">
                                <strong>{{ $detail->procedureStandard->code ?? '' }}</strong> - {{ $detail->procedureStandard->description ?? '' }}
                                @if($detail->sessions_authorized > 1)
                                    <span style="color: #666;">({{ $detail->sessions_authorized }} sesiones)</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <em style="color: #999;">Sin procedimientos</em>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- ✅ HISTORIAL DE TERAPIAS - TABLA CON SALTO DE PÁGINA -->
    @if($options['include_therapy_sessions'] ?? true)
        @if($therapy_sessions->count() > 0)
        <div class="therapy-section">
            <div class="section-title">HISTORIAL DE SESIONES DE TERAPIA FÍSICA</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 15%;">Terapeuta</th>
                        <th style="width: 30%;">Procedimientos Realizados</th>
                        <th style="width: 15%;">Estado Inicial</th>
                        <th style="width: 15%;">Estado Final</th>
                        <th style="width: 10%;">Duración</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($therapy_sessions as $session)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($session->started_at ?? $session->created_at)->format('d/m/Y') }}</td>
                        <td>
                            {{ $session->therapist->firstname ?? '' }} 
                            {{ $session->therapist->lastname ?? '' }}
                        </td>
                        <td>
                            {{-- ✅ TODOS LOS PROCEDIMIENTOS DE LA TERAPIA --}}
                            @if($session->appointment && $session->appointment->consultation_appointment_id)
                                @php
                                    $consultationProcedure = \App\Models\Procedure::with('procedureDetails.procedureStandard')
                                        ->where('appointment_id', $session->appointment->consultation_appointment_id)
                                        ->first();
                                @endphp
                                @if($consultationProcedure && $consultationProcedure->procedureDetails->count() > 0)
                                    @foreach($consultationProcedure->procedureDetails as $detail)
                                        <div style="margin-bottom: 2px;">
                                            • {{ $detail->procedureStandard->code ?? '' }} - {{ $detail->procedureStandard->description ?? '' }}
                                        </div>
                                    @endforeach
                                @else
                                    <em style="color: #999;">Sin procedimientos</em>
                                @endif
                            @else
                                <em style="color: #999;">N/A</em>
                            @endif
                        </td>
                        <td>{{ $session->initial_patient_state ?? 'N/A' }}</td>
                        <td>{{ $session->final_patient_state ?? 'N/A' }}</td>
                        <td>{{ $session->duration_minutes ?? 'N/A' }} min</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endif

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p><strong>{{ $company['name'] }}</strong></p>
        <p>{{ $company['city'] }} | Teléfono: {{ $company['phone'] }} | RNC: {{ $company['rnc'] }}</p>
        <p style="margin-top: 8px;">
            <em>Historial médico generado el {{ $generated_at }}</em>
        </p>
        <p style="margin-top: 4px; font-size: 6pt; color: #999;">
            Este documento es confidencial y contiene información médica protegida.
        </p>
    </div>
</body>
</html>