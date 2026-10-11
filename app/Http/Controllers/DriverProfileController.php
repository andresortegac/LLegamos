<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\DriverProfile;
use App\Services\LivenessService;

class DriverProfileController extends Controller
{
    public function __construct(private readonly LivenessService $liveness) {}

    public function livenessChallenge(Request $request)
    {
        return response()->json($this->liveness->issue((int) $request->user()->id));
    }
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
        $technicalRule = $profile?->technical_review_path ? 'nullable' : 'required';

        $validated = $request->validate([
            'document_number' => 'required|string|max:50|unique:driver_profiles,document_number,' . $user['id'] . ',user_id',
            'license_number' => 'required|string|max:50|unique:driver_profiles,license_number,' . $user['id'] . ',user_id',
            'license_expires_at' => 'required|date|after:today',
            'vehicle_plate' => 'required|string|max:50|unique:driver_profiles,vehicle_plate,' . $user['id'] . ',user_id',
            'vehicle_type' => 'required|in:auto,moto',
            'vehicle_make' => 'required|string|max:80',
            'vehicle_model' => 'required|string|max:80',
            'vehicle_color' => 'required|string|max:40',
            'passenger_capacity' => 'required|integer|min:1|max:8',
            'birth_date' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'vehicle_model_year' => 'required|integer|min:' . $minAllowedYear . '|max:' . $currentYear,
            'plate_type' => 'required|in:particular',
            'has_four_doors' => 'nullable|boolean',
            'has_seatbelts' => 'required|boolean|in:1',
            'has_air_conditioning' => 'nullable|boolean',
            'profile_photo_capture' => $profilePhotoRule . '|string|max:7500000',
            'liveness_token' => $profilePhotoRule . '|string|size:64',
            'license_document' => $licenseDocRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'property_card_document' => $propertyCardRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'soat_document' => $soatRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'id_card_document' => $idCardRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'soat_expires_at' => 'required|date|after:today',
            'technical_review_expires_at' => 'required|date|after:today',
            'technical_review_document' => $technicalRule . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'is_vehicle_owner' => 'required|boolean',
            'owner_authorization_document' => 'required_if:is_vehicle_owner,0|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'background_check_consent' => 'accepted',
            'policies_accepted' => 'accepted',
            'training_completed' => 'accepted',
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
            'profile_photo_capture.required' => 'Debes tomar una selfie en tiempo real con la camara.',
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
            'license_expires_at' => $validated['license_expires_at'],
            'vehicle_plate' => strtoupper($validated['vehicle_plate']),
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_make' => $validated['vehicle_make'],
            'vehicle_model' => $validated['vehicle_model'],
            'vehicle_color' => $validated['vehicle_color'],
            'passenger_capacity' => (int) $validated['passenger_capacity'],
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
            'soat_expires_at' => $validated['soat_expires_at'],
            'technical_review_expires_at' => $validated['technical_review_expires_at'],
            'is_vehicle_owner' => $request->boolean('is_vehicle_owner'),
            'background_check_consent_at' => now(),
            'policies_accepted_at' => now(),
            'training_completed_at' => now(),
            'verification_checks' => null,
        ];

        if ($request->filled('profile_photo_capture')) {
            $capture = $request->string('profile_photo_capture')->toString();
            if (!preg_match('/^data:image\/jpeg;base64,([A-Za-z0-9+\/=]+)$/', $capture, $matches)) {
                return back()->withInput()->withErrors(['profile_photo_capture' => 'La captura de camara no es valida.']);
            }
            $image = base64_decode($matches[1], true);
            $dimensions = $image !== false ? @getimagesizefromstring($image) : false;
            if ($image === false || strlen($image) > 5 * 1024 * 1024 || !$dimensions || $dimensions['mime'] !== 'image/jpeg' || $dimensions[0] < 320 || $dimensions[1] < 320) {
                return back()->withInput()->withErrors(['profile_photo_capture' => 'La selfie debe ser una imagen JPEG valida, clara y de al menos 320 x 320 pixeles.']);
            }
            $challenge = $this->liveness->consume((int) $user['id'], (string) $request->input('liveness_token'));
            $path = 'drivers/profile/' . Str::uuid() . '.jpg';
            Storage::disk(config('filesystems.private'))->put($path, $image);
            $data['profile_photo_path'] = $path;
            $data['liveness_challenge_id'] = $challenge->id;
            $data['selfie_captured_at'] = now();
            $data['identity_provider'] = 'manual';
            $data['face_match_score'] = null;
            $data['liveness_score'] = null;
        }

        if ($request->hasFile('license_document')) {
            $data['license_document_path'] = $request->file('license_document')->store('drivers/documents/license', config('filesystems.private'));
        }

        if ($request->hasFile('property_card_document')) {
            $data['property_card_path'] = $request->file('property_card_document')->store('drivers/documents/property-card', config('filesystems.private'));
        }

        if ($request->hasFile('soat_document')) {
            $data['soat_document_path'] = $request->file('soat_document')->store('drivers/documents/soat', config('filesystems.private'));
        }

        if ($request->hasFile('technical_review_document')) {
            $data['technical_review_path'] = $request->file('technical_review_document')->store('drivers/documents/technical-review', config('filesystems.private'));
        }

        if ($request->hasFile('owner_authorization_document')) {
            $data['owner_authorization_path'] = $request->file('owner_authorization_document')->store('drivers/documents/owner-authorization', config('filesystems.private'));
        }

        if ($request->hasFile('id_card_document')) {
            $data['id_card_document_path'] = $request->file('id_card_document')->store('drivers/documents/id-card', config('filesystems.private'));
        }

        if ($profile) {
            $profile->update($data);
        } else {
            $data['user_id'] = $user['id'];
            DriverProfile::create($data);
        }

        return redirect()->route('driver-profile.show')->with('success', 'Documentacion enviada. Tu cuenta esta en revision del administrador.');
    }

    public function approve(Request $request, $profileId)
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

        $requiredProfileData = ['document_number','license_number','license_expires_at','vehicle_plate','vehicle_make','vehicle_model','vehicle_color','passenger_capacity','profile_photo_path','liveness_challenge_id','selfie_captured_at','license_document_path','property_card_path','soat_document_path','soat_expires_at','technical_review_path','technical_review_expires_at','id_card_document_path','background_check_consent_at','policies_accepted_at','training_completed_at'];
        foreach ($requiredProfileData as $field) {
            if (blank($profile->{$field})) {
                return back()->with('error', 'No se puede aprobar: el expediente del conductor esta incompleto.');
            }
        }
        if (!$profile->is_vehicle_owner && blank($profile->owner_authorization_path)) {
            return back()->with('error', 'No se puede aprobar: falta la autorizacion del propietario.');
        }

        $checks = ['identity_verified', 'license_verified', 'soat_verified', 'property_verified', 'background_verified', 'vehicle_inspected', 'photo_verified', 'liveness_verified', 'policies_verified'];
        $request->validate(array_fill_keys($checks, 'accepted'));

        if ($profile->license_expires_at?->isPast() || $profile->soat_expires_at?->isPast() || $profile->technical_review_expires_at?->isPast()) {
            return back()->with('error', 'No se puede aprobar: existe documentacion vencida.');
        }

        $profile->update([
            'verification_status' => 'approved',
            'verification_notes' => null,
            'background_check_passed' => true,
            'verification_checks' => array_fill_keys($checks, true),
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
            'verification_checks' => null,
        ]);

        return back()->with('success', 'Solicitud rechazada y conductor notificado en su panel.');
    }

    public function downloadDocument(Request $request, int $profileId, string $document)
    {
        $profile = DriverProfile::findOrFail($profileId);
        $user = $request->user();

        if ($user->role !== 'admin' && $profile->user_id !== $user->id) {
            abort(403, 'No tienes permiso para consultar este documento.');
        }

        $documents = [
            'profile' => $profile->profile_photo_path,
            'license' => $profile->license_document_path,
            'property-card' => $profile->property_card_path,
            'soat' => $profile->soat_document_path,
            'id-card' => $profile->id_card_document_path,
            'technical-review' => $profile->technical_review_path,
            'owner-authorization' => $profile->owner_authorization_path,
        ];

        $path = $documents[$document] ?? null;
        $disk = Storage::disk(config('filesystems.private'));
        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, null, ['Content-Disposition' => 'inline']);
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
