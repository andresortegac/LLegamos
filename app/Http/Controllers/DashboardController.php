<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\DriverProfile;
use App\Models\InternalMessage;
use App\Models\Trip;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        return match($user['role']) {
            'pasajero' => $this->pasajero(),
            'conductor' => $this->conductor(),
            'admin' => $this->admin(),
            default => redirect()->route('login'),
        };
    }

    public function pasajero()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        return view('dashboard.pasajero', [
            'user' => $user,
        ]);
    }

    public function conductor()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');
        $userModel = User::with('driverProfile')->find($user['id']);
        $profile = $userModel?->driverProfile;

        $canOperate = $profile && $profile->verification_status === 'approved';
        $unreadMessagesCount = $this->getUnreadMessagesCount((int) $user['id']);
        $activeTrip = Trip::where('driver_id', $user['id'])
            ->whereIn('status', ['accepted', 'in_progress'])
            ->latest()
            ->first();
        $completedTrips = Trip::where('driver_id', $user['id'])->where('status', 'completed')->count();

        return view('dashboard.conductor', [
            'user' => $user,
            'profile' => $profile,
            'canOperate' => $canOperate,
            'unreadMessagesCount' => $unreadMessagesCount,
            'activeTrip' => $activeTrip,
            'completedTrips' => $completedTrips,
        ]);
    }

    public function admin()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        $pendingDrivers = DriverProfile::with('user')
            ->where('verification_status', 'pending')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $unreadMessagesCount = $this->getUnreadMessagesCount((int) $user['id']);

        return view('dashboard.admin', [
            'user' => $user,
            'pendingDrivers' => $pendingDrivers,
            'unreadMessagesCount' => $unreadMessagesCount,
        ]);
    }

    private function getUnreadMessagesCount(int $userId): int
    {
        if (!Schema::hasTable('internal_messages')) {
            return 0;
        }

        return InternalMessage::where('recipient_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
