<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajeria Interna | Llegamos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #0f172a, #1e293b); min-height: 100vh; color: #fff; padding: 20px; }
        .navbar { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); padding: 14px 24px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .navbar h3 { color: #38bdf8; }
        .back-link { color: #93c5fd; text-decoration: none; font-weight: bold; }
        .layout { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 320px 1fr; gap: 18px; }
        .panel { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; overflow: hidden; }
        .panel-title { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.12); color: #7dd3fc; font-weight: bold; }
        .contact-list a { display: block; padding: 12px 16px; color: #e2e8f0; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .contact-list a:hover { background: rgba(56,189,248,0.12); }
        .contact-list a.active { background: rgba(56,189,248,0.2); color: #fff; }
        .contact-pill {
            float: right;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: bold;
        }
        .messages-box { height: 520px; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
        .bubble { max-width: 78%; padding: 10px 12px; border-radius: 10px; line-height: 1.35; font-size: 14px; }
        .from-me { align-self: flex-end; background: #38bdf8; color: #082f49; }
        .from-them { align-self: flex-start; background: rgba(255,255,255,0.16); color: #e2e8f0; }
        .meta { font-size: 11px; opacity: 0.8; margin-top: 5px; }
        .composer { border-top: 1px solid rgba(255,255,255,0.12); padding: 12px; }
        textarea { width: 100%; min-height: 90px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.28); background: rgba(255,255,255,0.05); color: #fff; padding: 10px; }
        .btn { margin-top: 8px; border: none; border-radius: 8px; background: #38bdf8; color: #0f172a; font-weight: bold; padding: 10px 16px; cursor: pointer; }
        .empty { padding: 20px; color: #cbd5e1; text-align: center; }
        .error { margin: 12px 16px; background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.3); color: #fecaca; padding: 10px; border-radius: 8px; }
        @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } .messages-box { height: 380px; } }
    </style>
</head>
<body>
    <div class="navbar">
        <h3>
            Mensajeria Interna
            @if(($totalUnread ?? 0) > 0)
                <span class="contact-pill" style="float: none; margin-left: 8px;">{{ $totalUnread }} no leidos</span>
            @endif
        </h3>
        <a class="back-link" href="{{ route('dashboard.' . $user['role']) }}">Volver al panel</a>
    </div>

    <div class="layout">
        <div class="panel">
            <div class="panel-title">Contactos</div>
            @if($contacts->isEmpty())
                <div class="empty">No hay contactos disponibles.</div>
            @else
                <div class="contact-list">
                    @foreach($contacts as $contact)
                        @php
                            $contactUnread = $unreadBySender[$contact->id] ?? 0;
                        @endphp
                        <a href="{{ route('messages.index', ['with' => $contact->id]) }}" class="{{ $selectedContact && $selectedContact->id === $contact->id ? 'active' : '' }}">
                            {{ $contact->name }}<br>
                            <small>{{ $contact->role }}</small>
                            @if($contactUnread > 0)
                                <span class="contact-pill">{{ $contactUnread }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="panel">
            <div class="panel-title">
                @if($selectedContact)
                    Conversacion con {{ $selectedContact->name }}
                @else
                    Conversacion
                @endif
            </div>

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            @if(session('error'))
                <div class="error">{{ session('error') }}</div>
            @endif

            @if($selectedContact)
                <div class="messages-box" id="messages-box">
                    @forelse($messages as $msg)
                        <div class="bubble {{ $msg->sender_id == $user['id'] ? 'from-me' : 'from-them' }}">
                            <div>{{ $msg->message }}</div>
                            <div class="meta">{{ $msg->sender->name }} - {{ $msg->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @empty
                        <div class="empty">Aun no hay mensajes en esta conversacion.</div>
                    @endforelse
                </div>

                <form class="composer" method="POST" action="{{ route('messages.store') }}">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $selectedContact->id }}">
                    <textarea name="message" placeholder="Escribe tu mensaje..." required></textarea>
                    <button type="submit" class="btn">Enviar mensaje</button>
                </form>
            @else
                <div class="empty">Selecciona un contacto para empezar a chatear.</div>
            @endif
        </div>
    </div>

    <script>
        const box = document.getElementById('messages-box');
        if (box) {
            box.scrollTop = box.scrollHeight;
        }
    </script>
</body>
</html>
