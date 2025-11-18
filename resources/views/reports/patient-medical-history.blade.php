<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Médico - {{ $patient->firstname }} {{ $patient->lastname }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
        }
        
        .logo { max-width: 120px; height: auto; margin-bottom: 10px; }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .header .subtitle { font-size: 11pt; color: #666; }
        
        .patient-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .patient-info h2 {
            font-size: 13pt;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .info-item { padding: 5px 0; }
        
        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 9pt;
        }
        
        .info-value { color: #000; font-size: 10pt; }
        
        .stats-section {
            background-color: #e8f4f8;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        
        .stat-card {
            background-color: white;
            padding: 10px;
            text-align: center;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label { font-size: 9pt; color: #666; margin-top: 5px; }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #ecf0f1;
            border-left: 4px solid #3498db;
        }
        
        .record-card {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .record-header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .record-date {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .record-doctor {
            font-size: 9pt;
            color: #666;
            margin-top: 3px;
        }
        
        .content-block { margin-bottom: 12px; }
        
        .content-label {
            font-weight: bold;
            color: #555;
            font-size: 9pt;
            margin-bottom: 3px;
        }
        
        .content-value {
            font-size: 9pt;
            color: #333;
            line-height: 1.5;
        }
        
        .vital-signs-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        
        .vital-sign { text-align: center; }
        
        .vital-sign-value {
            font-size: 12pt;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .vital-sign-label { font-size: 8pt; color: #666; }
        
        .diagnosis-list, .procedure-list {
            list-style: none;
            padding: 0;
        }
        
        .diagnosis-list li, .procedure-list li {
            padding: 5px 10px;
            margin: 3px 0;
            background-color: #e8f4f8;
            border-left: 3px solid #3498db;
            font-size: 9pt;
        }
        
        .therapy-indicator {
            background-color: #fff3cd;
            padding: 10px;
            margin-top: 10px;
            border-left: 4px solid #ffc107;
            border-radius: 3px;
        }
        
        .therapy-indicator .content-label { color: #856404; }
        .therapy-indicator .content-value { color: #856404; }
        
        .therapy-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .therapy-table th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            border: 1px solid #2c3e50;
        }
        
        .therapy-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        
        .therapy-table tr:nth-child(even) { background-color: #f8f9fa; }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        
        .antecedentes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        
        .antecedente-item {
            font-size: 9pt;
            padding: 5px;
        }
        
        .antecedente-label {
            font-weight: bold;
            color: #555;
        }
        
        @media print {
            .page-break { page-break-after: always; }
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

    <!-- INFORMACIÓN DEL PACIENTE -->
    <div class="patient-info">
        <h2>DATOS DEL PACIENTE</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre Completo:</div>
                <div class="info-value">{{ $patient->firstname }} {{ $patient->lastname }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Cédula:</div>
                <div class="info-value">{{ $patient->dni ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha de Nacimiento:</div>
                <div class="info-value">
                    @if($patient->birthdate)
                        {{ \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') }}
                        ({{ \Carbon\Carbon::parse($patient->birthdate)->age }} años)
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Género:</div>
                <div class="info-value">{{ $patient->sex === 'M' ? 'Masculino' : ($patient->sex === 'F' ? 'Femenino' : 'N/A') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Teléfono:</div>
                <div class="info-value">{{ $patient->cellphone ?? $patient->phone ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $patient->email ?? 'N/A' }}</div>
            </div>
            @if($patient->insurance)
            <div class="info-item">
                <div class="info-label">Seguro:</div>
                <div class="info-value">{{ $patient->insurance->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">No. de Afiliado:</div>
                <div class="info-value">{{ $patient->insurance_code ?? 'N/A' }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-label">Dirección:</div>
                <div class="info-value">{{ $patient->address ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Ciudad:</div>
                <div class="info-value">{{ $patient->city ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="stats-section">
        <h2 style="font-size: 12pt; margin-bottom: 10px; color: #2c3e50;">RESUMEN DEL HISTORIAL</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_consultations'] }}</div>
                <div class="stat-label">Consultas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_therapies'] }}</div>
                <div class="stat-label">Sesiones de Terapia</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_diagnoses'] }}</div>
                <div class="stat-label">Diagnósticos</div>
            </div>
        </div>
        <div style="margin-top: 15px; font-size: 9pt; color: #555;">
            <strong>Primera Consulta:</strong> {{ $stats['first_consultation'] ?? 'N/A' }} &nbsp;|&nbsp;
            <strong>Última Consulta:</strong> {{ $stats['last_consultation'] ?? 'N/A' }}
        </div>
    </div>

    <!-- HISTORIAL DE CONSULTAS MÉDICAS (medical_records) -->
    @if($medical_records->count() > 0)
    <div class="section">
        <div class="section-title">HISTORIAL DE CONSULTAS MÉDICAS</div>
        
        @foreach($medical_records as $record)
        <div class="record-card">
            <div class="record-header">
                <div class="record-date">
                    Consulta - {{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y') }}
                </div>
                <div class="record-doctor">
                    Médico: Dr. {{ $record->appointment->employee->firstname ?? '' }} 
                    {{ $record->appointment->employee->lastname ?? '' }}
                </div>
            </div>

            <div style="margin-top: 10px;">
                <!-- Motivo de Consulta -->
                @if($record->chief_complaint)
                <div class="content-block">
                    <div class="content-label">Motivo de Consulta:</div>
                    <div class="content-value">{{ $record->chief_complaint }}</div>
                </div>
                @endif

                @if($record->current_illness)
                <div class="content-block">
                    <div class="content-label">Enfermedad Actual:</div>
                    <div class="content-value">{{ $record->current_illness }}</div>
                </div>
                @endif

                <!-- Signos Vitales -->
                @if($options['include_vital_signs'] ?? true)
                @php
                    $hasVitalSigns = $record->blood_pressure_systolic || $record->heart_rate || 
                                    $record->temperature || $record->weight || $record->height || $record->bmi;
                @endphp
                @if($hasVitalSigns)
                <div class="vital-signs-grid">
                    @if($record->blood_pressure_systolic)
                    <div class="vital-sign">
                        <div class="vital-sign-value">
                            {{ $record->blood_pressure_systolic }}/{{ $record->blood_pressure_diastolic }}
                        </div>
                        <div class="vital-sign-label">Presión Arterial (mmHg)</div>
                    </div>
                    @endif
                    @if($record->heart_rate)
                    <div class="vital-sign">
                        <div class="vital-sign-value">{{ $record->heart_rate }}</div>
                        <div class="vital-sign-label">Frecuencia Cardíaca (lpm)</div>
                    </div>
                    @endif
                    @if($record->temperature)
                    <div class="vital-sign">
                        <div class="vital-sign-value">{{ $record->temperature }}°C</div>
                        <div class="vital-sign-label">Temperatura</div>
                    </div>
                    @endif
                    @if($record->weight)
                    <div class="vital-sign">
                        <div class="vital-sign-value">{{ $record->weight }} kg</div>
                        <div class="vital-sign-label">Peso</div>
                    </div>
                    @endif
                    @if($record->height)
                    <div class="vital-sign">
                        <div class="vital-sign-value">{{ $record->height }} cm</div>
                        <div class="vital-sign-label">Altura</div>
                    </div>
                    @endif
                    @if($record->bmi)
                    <div class="vital-sign">
                        <div class="vital-sign-value">{{ number_format($record->bmi, 1) }}</div>
                        <div class="vital-sign-label">IMC</div>
                    </div>
                    @endif
                </div>
                @endif
                @endif

                <!-- Antecedentes Médicos -->
                @if($options['include_medical_history'] ?? true)
                @php
                    $hasAntecedentes = $record->smokes || $record->drinks_alcohol || $record->has_diabetes || 
                                      $record->has_hypertension || $record->has_asthma || $record->allergies;
                @endphp
                @if($hasAntecedentes)
                <div class="content-block">
                    <div class="content-label">Antecedentes Relevantes:</div>
                    <div class="antecedentes-grid">
                        @if($record->smokes)
                        <div class="antecedente-item">
                            <span class="antecedente-label">Fumador:</span> Sí ({{ $record->smoking_frequency ?? 'N/A' }})
                        </div>
                        @endif
                        @if($record->drinks_alcohol)
                        <div class="antecedente-item">
                            <span class="antecedente-label">Alcohol:</span> Sí ({{ $record->alcohol_frequency ?? 'N/A' }})
                        </div>
                        @endif
                        @if($record->has_diabetes)
                        <div class="antecedente-item">
                            <span class="antecedente-label">Diabetes:</span> Sí
                        </div>
                        @endif
                        @if($record->has_hypertension)
                        <div class="antecedente-item">
                            <span class="antecedente-label">Hipertensión:</span> Sí
                        </div>
                        @endif
                        @if($record->has_asthma)
                        <div class="antecedente-item">
                            <span class="antecedente-label">Asma:</span> Sí
                        </div>
                        @endif
                    </div>
                </div>

                @if($record->allergies)
                <div class="content-block">
                    <div class="content-label">Alergias:</div>
                    <div class="content-value">{{ $record->allergies }}</div>
                </div>
                @endif

                @if($record->current_medications)
                <div class="content-block">
                    <div class="content-label">Medicamentos Actuales:</div>
                    <div class="content-value">{{ $record->current_medications }}</div>
                </div>
                @endif
                @endif
                @endif

                <!-- Examen Físico -->
                @if($record->physical_exam)
                <div class="content-block">
                    <div class="content-label">Examen Físico:</div>
                    <div class="content-value">{{ $record->physical_exam }}</div>
                </div>
                @endif

                <!-- Diagnósticos (procedure -> procedure_diagnostics) -->
                @if($record->procedure && $record->procedure->procedureDiagnostics && $record->procedure->procedureDiagnostics->count() > 0)
                <div class="content-block">
                    <div class="content-label">Diagnósticos (CIE-10):</div>
                    <ul class="diagnosis-list">
                        @foreach($record->procedure->procedureDiagnostics as $diag)
                        @if($diag->diagnostic)
                        <li>
                            <strong>{{ $diag->diagnostic->code }}</strong> - 
                            {{ $diag->diagnostic->description }}
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Procedimientos (procedure -> procedure_details) -->
                @if($record->procedure && $record->procedure->procedureDetails && $record->procedure->procedureDetails->count() > 0)
                <div class="content-block">
                    <div class="content-label">Procedimientos Realizados:</div>
                    <ul class="procedure-list">
                        @foreach($record->procedure->procedureDetails as $proc)
                        @if($proc->procedureStandard)
                        <li>
                            <strong>{{ $proc->procedureStandard->code ?? '' }}</strong> - 
                            {{ $proc->procedureStandard->description ?? '' }}
                            @if($record->requires_therapy && $proc->sessions_authorized > 0)
                            <span style="color: #e74c3c; font-weight: bold;">
                                ({{ $proc->sessions_authorized }} sesiones autorizadas)
                            </span>
                            @endif
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Plan de Tratamiento -->
                @if($record->treatment_plan)
                <div class="content-block">
                    <div class="content-label">Plan de Tratamiento:</div>
                    <div class="content-value">{{ $record->treatment_plan }}</div>
                </div>
                @endif

                <!-- Prescripciones -->
                @if(($options['include_prescriptions'] ?? true) && $record->prescriptions)
                <div class="content-block">
                    <div class="content-label">Prescripciones:</div>
                    <div class="content-value">{{ $record->prescriptions }}</div>
                </div>
                @endif

                <!-- Recomendaciones -->
                @if($record->recommendations)
                <div class="content-block">
                    <div class="content-label">Recomendaciones:</div>
                    <div class="content-value">{{ $record->recommendations }}</div>
                </div>
                @endif

                <!-- Indicación de Terapia -->
                @if($record->requires_therapy)
                <div class="therapy-indicator">
                    <div class="content-label">Requiere Terapia Física:</div>
                    <div class="content-value">
                        <strong>Sí</strong> - {{ $record->therapy_sessions_needed ?? 'N/A' }} sesiones recomendadas
                        @if($record->therapy_reason)
                        <br><em>Motivo: {{ $record->therapy_reason }}</em>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="section">
        <div class="section-title">HISTORIAL DE CONSULTAS MÉDICAS</div>
        <p style="text-align: center; padding: 20px; color: #666;">No hay consultas médicas registradas</p>
    </div>
    @endif

    <!-- HISTORIAL DE SESIONES DE TERAPIA (therapy_records) -->
    @if($options['include_therapy_sessions'] ?? true)
        @if($therapy_sessions->count() > 0)
        <div class="section">
            <div class="section-title">HISTORIAL DE SESIONES DE TERAPIA FÍSICA</div>
            
            <table class="therapy-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 15%;">Terapeuta</th>
                        <th style="width: 18%;">Estado Inicial</th>
                        <th style="width: 18%;">Estado Final</th>
                        <th style="width: 12%;">Duración</th>
                        <th style="width: 25%;">Observaciones Finales</th>
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
                        <td>{{ $session->initial_patient_state ?? 'N/A' }}</td>
                        <td>{{ $session->final_patient_state ?? 'N/A' }}</td>
                        <td>{{ $session->duration_minutes ?? 'N/A' }} min</td>
                        <td>{{ $session->final_observations ?? 'N/A' }}</td>
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
        <p style="margin-top: 10px;">
            <em>Historial médico generado el {{ $generated_at }}</em>
        </p>
    </div>
</body>
</html>