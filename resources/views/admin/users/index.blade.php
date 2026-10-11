<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Usuarios | Llegamos</title><style>
body{font-family:Segoe UI,sans-serif;background:#0f172a;color:#e2e8f0;margin:0;padding:24px}.wrap{max-width:1250px;margin:auto}a{color:#7dd3fc}.top,.box{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:18px;margin-bottom:18px}.top{display:flex;gap:18px;align-items:center;flex-wrap:wrap}.top h1{margin:0 auto 0 0}.filters{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px}.filters input,.filters select,textarea{background:#0f172a;color:white;border:1px solid #475569;border-radius:8px;padding:10px}button{border:0;border-radius:8px;padding:9px 13px;font-weight:700;cursor:pointer}.search{background:#38bdf8}.danger{background:#ef4444;color:white}.ok{background:#10b981;color:white}.notice{padding:12px;background:#064e3b;border-radius:8px;margin-bottom:14px}.error{color:#fecaca}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:11px;border-bottom:1px solid #334155;vertical-align:top}th{color:#7dd3fc}.status{font-weight:bold}.suspended{color:#fca5a5}.active{color:#86efac}textarea{width:240px;min-height:48px}.pagination nav{margin-top:18px}@media(max-width:800px){.filters{grid-template-columns:1fr}.box{overflow:auto}}
</style>    @include('partials.pwa-head')
</head><body><main class="wrap">
<div class="top"><h1>Administración de usuarios</h1><a href="{{ route('dashboard.admin') }}">Panel</a><a href="{{ route('admin.audit.index') }}">Auditoría</a></div>
@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
@if($errors->any())<div class="box error">{{ $errors->first() }}</div>@endif
<form class="box filters" method="GET">
<input name="search" value="{{ request('search') }}" placeholder="Nombre o correo">
<select name="role"><option value="">Todos los roles</option>@foreach(['pasajero','conductor','admin'] as $role)<option value="{{ $role }}" @selected(request('role')===$role)>{{ ucfirst($role) }}</option>@endforeach</select>
<select name="status"><option value="">Todos los estados</option><option value="active" @selected(request('status')==='active')>Activos</option><option value="suspended" @selected(request('status')==='suspended')>Suspendidos</option></select>
<button class="search">Filtrar</button></form>
<div class="box"><table><thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Actividad</th><th>Acción</th></tr></thead><tbody>
@forelse($users as $item)<tr><td><strong>{{ $item->name }}</strong><br>{{ $item->email }}</td><td>{{ ucfirst($item->role) }}</td>
<td class="status {{ $item->status }}">{{ $item->status==='active'?'Activo':'Suspendido' }}@if($item->suspended_at)<br><small>{{ $item->suspended_at->format('d/m/Y H:i') }}</small>@endif</td>
<td>Registro: {{ $item->created_at?->format('d/m/Y') }}<br>Último acceso: {{ $item->last_login_at?->format('d/m/Y H:i') ?? 'Sin registro' }}</td>
<td>@if($item->id===auth()->id())<small>Cuenta actual protegida</small>
@elseif($item->status==='active')<form method="POST" action="{{ route('admin.users.suspend',$item) }}">@csrf @method('PATCH')<textarea name="reason" required minlength="10" placeholder="Motivo de la suspensión"></textarea><br><button class="danger">Suspender</button></form>
@else<p>{{ $item->suspension_reason }}</p><form method="POST" action="{{ route('admin.users.reactivate',$item) }}">@csrf @method('PATCH')<button class="ok">Reactivar</button></form>@endif</td></tr>
@empty<tr><td colspan="5">No se encontraron usuarios.</td></tr>@endforelse
</tbody></table><div class="pagination">{{ $users->links() }}</div></div>
</main>    @include('partials.pwa-script')
</body></html>
