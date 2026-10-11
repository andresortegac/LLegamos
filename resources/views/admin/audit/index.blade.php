<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Auditoría | Llegamos</title><style>
body{font-family:Segoe UI,sans-serif;background:#0f172a;color:#e2e8f0;margin:0;padding:24px}.wrap{max-width:1300px;margin:auto}a{color:#7dd3fc}.top,.box{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:18px;margin-bottom:18px}.top{display:flex;gap:18px;align-items:center}.top h1{margin-right:auto}.filters{display:grid;grid-template-columns:2fr 2fr 1fr 1fr 1fr auto;gap:8px}.filters input,.filters select{background:#0f172a;color:white;border:1px solid #475569;border-radius:8px;padding:9px}button{background:#38bdf8;border:0;border-radius:8px;padding:9px 13px;font-weight:bold}table{width:100%;border-collapse:collapse;font-size:13px}th,td{text-align:left;padding:9px;border-bottom:1px solid #334155}th{color:#7dd3fc}@media(max-width:900px){.filters{grid-template-columns:1fr}.box{overflow:auto}}
</style>    @include('partials.pwa-head')
</head><body><main class="wrap">
<div class="top"><h1>Registro de auditoría</h1><a href="{{ route('dashboard.admin') }}">Panel</a><a href="{{ route('admin.users.index') }}">Usuarios</a></div>
<form class="box filters" method="GET"><select name="user_id"><option value="">Todos los usuarios</option>@foreach($users as $item)<option value="{{ $item->id }}" @selected((string)request('user_id')===(string)$item->id)>{{ $item->name }} — {{ $item->email }}</option>@endforeach</select>
<input name="event" value="{{ request('event') }}" placeholder="Evento o ruta"><select name="method"><option value="">Método</option>@foreach(['POST','PUT','PATCH','DELETE'] as $method)<option @selected(request('method')===$method)>{{ $method }}</option>@endforeach</select>
<input type="date" name="from" value="{{ request('from') }}"><input type="date" name="to" value="{{ request('to') }}"><button>Filtrar</button></form>
<div class="box"><table><thead><tr><th>Fecha</th><th>Usuario</th><th>Evento</th><th>Solicitud</th><th>Resultado</th><th>IP</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td><td>{{ $log->user?->name ?? 'Usuario eliminado' }}<br>{{ $log->user?->email }}</td><td>{{ $log->event }}</td><td>{{ $log->method }} /{{ $log->path }}</td><td>{{ $log->response_status }}</td><td>{{ $log->ip_address }}</td></tr>
@empty<tr><td colspan="6">No hay eventos para estos filtros.</td></tr>@endforelse
</tbody></table>{{ $logs->links() }}</div>
</main>    @include('partials.pwa-script')
</body></html>
