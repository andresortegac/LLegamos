<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $role = strtolower(trim((string) $request->input('role')));
        $request->merge(['role' => $role]);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:pasajero,conductor,admin',
        ];

        $messages = [
            'name.required' => 'El nombre completo es obligatorio.',
            'name.string' => 'El nombre debe ser un texto valido.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'email.required' => 'El correo electronico es obligatorio.',
            'email.email' => 'El correo electronico no es valido.',
            'email.unique' => 'El correo electronico ya ha sido registrado.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'role.required' => 'Debes seleccionar un tipo de usuario.',
            'role.in' => 'El tipo de usuario seleccionado no es valido.',
        ];

        if ($role === 'pasajero') {
            $rules = array_merge($rules, [
                'id_document_front' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
                'id_document_back' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
                'face_biometric_capture' => 'required|string',
            ]);

            $messages = array_merge($messages, [
            'id_document_front.required' => 'Debes subir la foto frontal de la cedula para registrar un pasajero.',
            'id_document_front.mimes' => 'La foto frontal debe ser jpg, jpeg, png o webp.',
            'id_document_front.max' => 'La foto frontal no debe superar 5 MB.',
            'id_document_back.required' => 'Debes subir la foto trasera de la cedula para registrar un pasajero.',
            'id_document_back.mimes' => 'La foto trasera debe ser jpg, jpeg, png o webp.',
            'id_document_back.max' => 'La foto trasera no debe superar 5 MB.',
            'face_biometric_capture.required' => 'Debes escanear el rostro para registrar un pasajero.',
            ]);
        }

        $request->validate($rules, $messages);

        $passengerSecurityData = [
            'id_document_front_path' => null,
            'id_document_back_path' => null,
            'face_biometric_path' => null,
            'is_identity_verified' => false,
        ];

        if ($role === 'pasajero') {
            $passengerSecurityData = $this->storePassengerSecurityData($request);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'id_document_front_path' => $passengerSecurityData['id_document_front_path'],
            'id_document_back_path' => $passengerSecurityData['id_document_back_path'],
            'face_biometric_path' => $passengerSecurityData['face_biometric_path'],
            'is_identity_verified' => $passengerSecurityData['is_identity_verified'],
        ]);

        Session::put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'El correo electronico es obligatorio.',
            'email.email' => 'El correo electronico no es valido.',
            'password.required' => 'La contrasena es obligatoria.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Credenciales incorrectas')->withInput();
        }

        Session::put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('dashboard');
    }

    public function dashboard()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        return view('dashboard', [
            'user' => Session::get('user')
        ]);
    }

    public function logout()
    {
        Session::forget('user');
        return redirect()->route('welcome');
    }

    private function storePassengerSecurityData(Request $request): array
    {
        $frontPath = $request->file('id_document_front')->store('passengers/id/front', 'public');
        $backPath = $request->file('id_document_back')->store('passengers/id/back', 'public');
        $facePath = $this->storeBiometricFaceImage($request->input('face_biometric_capture'));

        return [
            'id_document_front_path' => $frontPath,
            'id_document_back_path' => $backPath,
            'face_biometric_path' => $facePath,
            'is_identity_verified' => false,
        ];
    }

    private function storeBiometricFaceImage(string $base64Image): string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $base64Image, $matches)) {
            throw ValidationException::withMessages([
                'face_biometric_capture' => 'No se pudo procesar la captura biometrica facial.',
            ]);
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
        $binaryImage = base64_decode(str_replace(' ', '+', $base64Data), true);

        if ($binaryImage === false) {
            throw ValidationException::withMessages([
                'face_biometric_capture' => 'La imagen facial capturada no es valida.',
            ]);
        }

        $path = 'passengers/biometric/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $binaryImage);

        return $path;
    }
}
