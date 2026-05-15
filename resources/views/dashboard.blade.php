<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solutronic Laundry Cloud</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #101014;
            color: #f5f5f5;
            margin: 0;
            padding: 32px;
        }

        .container {
            max-width: 1180px;
            margin: auto;
        }

        .header {
            border: 1px solid #7c3cff;
            padding: 22px;
            margin-bottom: 24px;
            background: #171720;
            border-radius: 12px;
        }

        h1 {
            margin: 0;
            color: #ffffff;
        }

        .subtitle {
            color: #bdbdbd;
            margin-top: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .card {
            background: #1f1f2a;
            border: 1px solid #333344;
            padding: 20px;
            border-radius: 10px;
        }

        .machine-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #ffffff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #7c3cff;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #9b6cff;
        }

        .btn-secondary {
            background: #333344;
            margin-bottom: 20px;
        }

        select, input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 6px;
            border: none;
            box-sizing: border-box;
        }

        .alert-success {
            background: #123d22;
            padding: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #37d36b;
            border-radius: 6px;
        }

        .alert-error {
            background: #3d1212;
            padding: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #d33737;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #1f1f2a;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #333344;
            padding: 10px;
            font-size: 13px;
        }

        th {
            background: #29293a;
        }

        .status-pending {
            color: #ffcc00;
            font-weight: bold;
        }

        .status-processing {
            color: #00b7ff;
            font-weight: bold;
        }

        .status-activated {
            color: #37d36b;
            font-weight: bold;
        }

        .status-failed {
            color: #ff4d4d;
            font-weight: bold;
        }

        .small {
            color: #aaa;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    </script>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Solutronic Laundry Cloud</h1>
        <div class="subtitle">
            Panel básico de pruebas para activación remota de lavadoras y secadoras.
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <h2>Crear orden de activación</h2>

    <div class="grid">
        @foreach($machines as $machineId => $machineName)
            <div class="card">
                <div class="machine-title">{{ $machineId }}</div>
                <p>{{ $machineName }}</p>

                <form method="POST" action="{{ route('activations.store') }}">
                    @csrf

                    <input type="hidden" name="machine_id" value="{{ $machineId }}">

                    <label class="small">Tipo de activación</label>
                    <select name="activation_type">
                        <option value="manual_admin">Activación manual</option>
                        <option value="bancard">Pago Bancard</option>
                        <option value="subscription">Suscripción</option>
                        <option value="points">Puntos</option>
                    </select>

                    <label class="small">Referencia</label>
                    <input type="text" name="reference" value="TEST_{{ $machineId }}">

                    <button type="submit">Crear orden para {{ $machineId }}</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('activations.reset_processing') }}">
        @csrf
        <button class="btn-secondary" type="submit">
            Reiniciar órdenes en processing a pending
        </button>
    </form>

    <h2>Últimas activaciones</h2>
    <p class="small">La tabla se actualiza automáticamente cada 5 segundos.</p>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Activation ID</th>
            <th>Máquina</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Mensaje</th>
            <th>Creado</th>
            <th>Enviado</th>
            <th>Procesado</th>
        </tr>
        </thead>
        <tbody>
        @foreach($activations as $activation)
            <tr>
                <td>{{ $activation->id }}</td>
                <td>{{ $activation->activation_id }}</td>
                <td>{{ $activation->machine_id }}</td>
                <td>{{ $activation->activation_type }}</td>
                <td class="status-{{ $activation->status }}">{{ $activation->status }}</td>
                <td>{{ $activation->message }}</td>
                <td>{{ $activation->created_at }}</td>
                <td>{{ $activation->sent_at }}</td>
                <td>{{ $activation->processed_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>