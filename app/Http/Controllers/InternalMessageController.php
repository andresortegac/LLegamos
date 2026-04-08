<?php

namespace App\Http\Controllers;

use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class InternalMessageController extends Controller
{
    public function index(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $sessionUser = Session::get('user');
        if (!in_array($sessionUser['role'], ['admin', 'conductor'], true)) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a mensajeria interna.');
        }

        $currentUser = User::find($sessionUser['id']);
        if (!$currentUser) {
            Session::forget('user');
            return redirect()->route('login');
        }

        $contacts = $sessionUser['role'] === 'admin'
            ? User::where('role', 'conductor')->orderBy('name')->get()
            : User::where('role', 'admin')->orderBy('name')->get();

        if (!Schema::hasTable('internal_messages')) {
            return view('messages.index', [
                'user' => $sessionUser,
                'contacts' => $contacts,
                'selectedContact' => null,
                'messages' => collect(),
                'unreadBySender' => [],
                'totalUnread' => 0,
            ])->with('error', 'La mensajeria aun no esta disponible. Ejecuta migraciones pendientes.');
        }

        $unreadRows = InternalMessage::selectRaw('sender_id, COUNT(*) as unread_count')
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->groupBy('sender_id')
            ->get();

        $unreadBySender = $unreadRows
            ->pluck('unread_count', 'sender_id')
            ->map(fn ($value) => (int) $value)
            ->toArray();

        $totalUnread = array_sum($unreadBySender);

        $targetId = (int) $request->query('with', 0);
        $selectedContact = $contacts->firstWhere('id', $targetId);
        if (!$selectedContact && $contacts->isNotEmpty()) {
            $selectedContact = $contacts->first();
            $targetId = $selectedContact->id;
        }

        $messages = collect();
        if ($selectedContact) {
            $messages = InternalMessage::with(['sender:id,name,role', 'recipient:id,name,role'])
                ->where(function ($query) use ($currentUser, $selectedContact) {
                    $query->where('sender_id', $currentUser->id)
                        ->where('recipient_id', $selectedContact->id);
                })
                ->orWhere(function ($query) use ($currentUser, $selectedContact) {
                    $query->where('sender_id', $selectedContact->id)
                        ->where('recipient_id', $currentUser->id);
                })
                ->orderBy('created_at')
                ->get();

            InternalMessage::where('sender_id', $selectedContact->id)
                ->where('recipient_id', $currentUser->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            if (isset($unreadBySender[$selectedContact->id])) {
                $totalUnread -= $unreadBySender[$selectedContact->id];
                $unreadBySender[$selectedContact->id] = 0;
            }
        }

        return view('messages.index', [
            'user' => $sessionUser,
            'contacts' => $contacts,
            'selectedContact' => $selectedContact,
            'messages' => $messages,
            'unreadBySender' => $unreadBySender,
            'totalUnread' => max(0, $totalUnread),
        ]);
    }

    public function store(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $sessionUser = Session::get('user');
        if (!in_array($sessionUser['role'], ['admin', 'conductor'], true)) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a mensajeria interna.');
        }

        $currentUser = User::find($sessionUser['id']);
        if (!$currentUser) {
            Session::forget('user');
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1500',
        ], [
            'recipient_id.required' => 'Debes seleccionar un destinatario.',
            'recipient_id.exists' => 'Destinatario no valido.',
            'message.required' => 'Debes escribir un mensaje.',
            'message.max' => 'El mensaje no puede superar 1500 caracteres.',
        ]);

        $recipient = User::find((int) $validated['recipient_id']);
        if (!$recipient) {
            return back()->with('error', 'Destinatario no encontrado.');
        }

        if (!Schema::hasTable('internal_messages')) {
            return back()->with('error', 'La mensajeria aun no esta disponible. Ejecuta migraciones pendientes.');
        }

        if (!$this->isAllowedConversation($currentUser, $recipient)) {
            return back()->with('error', 'Solo se permite mensajeria entre administrador y conductor.');
        }

        InternalMessage::create([
            'sender_id' => $currentUser->id,
            'recipient_id' => $recipient->id,
            'message' => trim($validated['message']),
        ]);

        return redirect()->route('messages.index', ['with' => $recipient->id]);
    }

    private function isAllowedConversation(User $user, User $other): bool
    {
        $pair = [$user->role, $other->role];
        sort($pair);

        return $pair === ['admin', 'conductor'];
    }
}
