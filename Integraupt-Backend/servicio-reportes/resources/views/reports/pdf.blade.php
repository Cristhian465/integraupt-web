<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estadísticas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-lg { font-size: 16px; }
        .italic { font-style: italic; }
        .mt-4 { margin-top: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="text-center">
        <h1 class="text-lg font-bold">REPORTE DE ESTADÍSTICAS - SISTEMA DE RESERVAS</h1>
        <p class="italic" style="font-size: 10px;">Generado: {{ $fechaGeneracion }}</p>
    </div>

    <div class="mt-4">
        <h2 class="font-bold" style="font-size: 12px;">ESTADÍSTICAS GENERALES</h2>
        <p>Total Estudiantes: {{ $estadisticas['totalEstudiantes'] }}</p>
        <p>Total Docentes: {{ $estadisticas['totalDocentes'] }}</p>
        <p>Reservas Activas: {{ $estadisticas['reservasActivas'] }}</p>
        <p>Tasa de Uso: {{ $estadisticas['tasaUso'] }}%</p>
        <p>Variación: {{ $estadisticas['variacionReservas'] }}</p>
    </div>

    <div class="mt-4">
        <h2 class="font-bold" style="font-size: 12px;">USO DE ESPACIOS (Top 5)</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Código</th>
                    <th style="width: 35%;">Nombre</th>
                    <th style="width: 20%;">Reservas</th>
                    <th style="width: 20%;">Uso %</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($usoEspacios, 0, 5) as $espacio)
                <tr>
                    <td>{{ $espacio['codigoEspacio'] }}</td>
                    <td>{{ $espacio['nombreEspacio'] }}</td>
                    <td>{{ $espacio['totalReservas'] }}</td>
                    <td>{{ number_format($espacio['porcentajeUso'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <h2 class="font-bold" style="font-size: 12px;">RESERVAS POR MES</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 60%;">Mes/Año</th>
                    <th style="width: 40%;">Total Reservas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservasPorMes as $mes)
                <tr>
                    <td>{{ $mes['mes'] }} {{ $mes['anio'] }}</td>
                    <td>{{ $mes['totalReservas'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
