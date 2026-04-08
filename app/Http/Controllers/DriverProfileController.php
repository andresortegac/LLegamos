<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\DriverProfile;

class DriverProfileController extends Controller
{
    public function show()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'conductor') {
            return redirect()->route('dashboard');
        }

        $userModel = User::find($user['id']);
        $profile = $userModel->driverProfile;

        return view('driver.profile-edit', [
            'user' => $user,
            'profile' => $profile,
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function detail($profileId)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $admin = Session::get('user');

        if ($admin['role'] !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para ver detalles de conductores.');
        }

        $profile = DriverProfile::with('user')->find($profileId);

        if (!$profile) {
            return back()->with('error', 'Solicitud de conductor no encontrada.');
        }

        return view('dashboard.admin-driver-detail', [
            'user' => $admin,
            'profile' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'conductor') {
            return redirect()->route('dashboard');
        }

        $userModel = User::find($user['id']);
        $profile = $userModel->driverProfile;

        $currentYear = (int) date('Y');
        $minAllowedYear = $currentYear - 10;

        $profilePhotoRule = $profile?->profile_photo_path ? 'nullable' : 'required';
        $licenseDocRule = $profile?->license_document_path ? 'nullable' : 'required';
        $propertyCardRule = $profile?->property_card_path ? 'nullable' : 'required';
        $soatRule = $profile?->soat_document_path ? 'nullable' : 'required';
        $idCardRule = $profile?->id_card_document_path ? 'nullable' : 'required';

        $validated = $request->validate([
            'document_number' => 'required|string|max:50|unique:driver_profiles,document_number,' . $user['id'] . ',user_id',
            'license_number' => 'required|string|max:50|unique:driver_profiles,license_number,' . $user['id'] . ',user_id',
            'vehicle_plate' => 'required|string|max:50|unique:driver_profiles,vehicle_plate,' . $user['id'] . ',user_id',
            'vehicle_type' => 'required|in:auto,moto',
            'birth_date' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'vehicle_model_year' => 'required|integer|min:' . $minAllowedYear . '|max:' . $currentYear,
            'plate_type' => 'required|in:particular',
            'has_four_doors' => 'nullable|boolean',
            'has_seatbelts' => 'required|boolean|in:1',
            'has_air_conditioning' => 'nullable|boolean',
            'profile_photo' => $profilePhotoRule . '|file|mimes:jpg,jpeg,png,webp|max:5120',
            'license_document' => $licenseDocRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'property_card_document' => $propertyCardRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'soat_document' => $soatRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'id_card_document' => $idCardRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'document_number.required' => 'El numero de documento es obligatorio.',
            'document_number.unique' => 'Este numero de documento ya esta registrado.',
            'license_number.required' => 'El numero de licencia es obligatorio.',
            'license_number.unique' => 'Este numero de licencia ya esta registrado.',
            'vehicle_plate.required' => 'La placa del vehiculo es obligatoria.',
            'vehicle_plate.unique' => 'Esta placa ya esta registrada.',
            'vehicle_type.required' => 'Debes seleccionar un tipo de vehiculo.',
            'vehicle_type.in' => 'Solo se permite auto o moto para conductores.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before_or_equal' => 'Debes ser mayor de 18 anos para conducir.',
            'vehicle_model_year.required' => 'El modelo del vehiculo es obligatorio.',
            'vehicle_model_year.min' => 'El vehiculo debe ser de maximo 10 anos de antiguedad.',
            'vehicle_model_year.max' => 'El modelo del vehiculo no es valido.',
            'plate_type.required' => 'Debes indicar el tipo de placa.',
            'plate_type.in' => 'Solo se permiten placas particulares para activar la cuenta.',
            'has_seatbelts.required' => 'Debes confirmar cinturones de seguridad para todos los ocupantes.',
            'has_seatbelts.in' => 'Debes cumplir el requisito de cinturones de seguridad para continuar.',
            'profile_photo.required' => 'La foto de perfil es obligatoria.',
            'license_document.required' => 'Debes subir la licencia de conducir vigente.',
            'property_card_document.required' => 'Debes subir la tarjeta de propiedad.',
            'soat_document.required' => 'Debes subir el SOAT vigente.',
            'id_card_document.required' => 'Debes subir la cédula o documento de identificación.',
        ]);

        if ($validated['vehicle_type'] === 'auto') {
            if (!$request->boolean('has_four_doors')) {
                return back()->withInput()->withErrors([
                    'has_four_doors' => 'Para vehiculo tipo auto debes confirmar que tiene 4 puertas.',
                ]);
            }

            if (!$request->boolean('has_air_conditioning')) {
                return back()->withInput()->withErrors([
                    'has_air_conditioning' => 'Para vehiculo tipo auto debes confirmar que tiene aire acondicionado.',
                ]);
            }
        }

        $data = [
            'document_number' => $validated['document_number'],
            'license_number' => $validated['license_number'],
            'vehicle_plate' => strtoupper($validated['vehicle_plate']),
            'vehicle_type' => $validated['vehicle_type'],
            'birth_date' => $validated['birth_date'],
            'vehicle_model_year' => (int) $validated['vehicle_model_year'],
            'plate_type' => $validated['plate_type'],
            'has_four_doors' => $request->boolean('has_four_doors'),
            'has_seatbelts' => $request->boolean('has_seatbelts'),
            'has_air_conditioning' => $request->boolean('has_air_conditioning'),
            'background_check_passed' => false,
            'verification_status' => 'pending',
            'verification_notes' => null,
            'submitted_at' => now(),
            'verified_at' => null,
            'verified_by_admin_id' => null,
            'status' => 'inactive',
        ];

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request->file('profile_photo')->store('drivers/profile', 'public');
        }

        if ($request->hasFile('license_document')) {
            $data['license_document_path'] = $request->file('license_document')->store('drivers/documents/license', 'public');
        }

        if ($request->hasFile('property_card_document')) {
            $data['property_card_path'] = $request->file('property_card_document')->store('drivers/documents/property-card', 'public');
        }

        if ($request->hasFile('soat_document')) {
            $data['soat_document_path'] = $request->file('soat_document')->store('drivers/documents/soat', 'public');
        }

        if ($request->hasFile('id_card_document')) {
            $data['id_card_document_path'] = $request->file('id_card_document')->store('drivers/documents/id-card', 'public');
        }

        if ($profile) {
            $profile->update($data);
        } else {
            $data['user_id'] = $user['id'];
            DriverProfile::create($data);
        }

        return redirect()->route('driver-profile.show')->with('success', 'Documentacion enviada. Tu cuenta esta en revision del administrador.');
    }

    public function approve($profileId)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $admin = Session::get('user');

        if ($admin['role'] !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para aprobar conductores.');
        }

        $profile = DriverProfile::find($profileId);

        if (!$profile) {
            return back()->with('error', 'Solicitud de conductor no encontrada.');
        }

        $profile->update([
            'verification_status' => 'approved',
            'verification_notes' => null,
            'background_check_passed' => true,
            'verified_at' => now(),
            'verified_by_admin_id' => $admin['id'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Conductor aprobado correctamente.');
    }

    public function reject(Request $request, $profileId)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $admin = Session::get('user');

        if ($admin['role'] !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para rechazar conductores.');
        }

        $request->validate([
            'verification_notes' => 'required|string|max:500',
        ], [
            'verification_notes.required' => 'Debes indicar el motivo del rechazo.',
            'verification_notes.max' => 'El motivo no puede superar 500 caracteres.',
        ]);

        $profile = DriverProfile::find($profileId);

        if (!$profile) {
            return back()->with('error', 'Solicitud de conductor no encontrada.');
        }

        $profile->update([
            'verification_status' => 'rejected',
            'verification_notes' => $request->input('verification_notes'),
            'background_check_passed' => false,
            'verified_at' => now(),
            'verified_by_admin_id' => $admin['id'],
            'status' => 'inactive',
        ]);

        return back()->with('success', 'Solicitud rechazada y conductor notificado en su panel.');
    }

    public function checkProfileCompletion()
    {
        if (!Session::has('user')) {
            return false;
        }

        $user = Session::get('user');

        if ($user['role'] !== 'conductor') {
            return true;
        }

        $userModel = User::find($user['id']);
        $profile = $userModel->driverProfile;

        if (!$profile) {
            return false;
        }

        return $profile->verification_status === 'approved';
    }
}
