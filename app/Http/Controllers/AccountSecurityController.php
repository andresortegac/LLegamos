<?php
namespace App\Http\Controllers;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
class AccountSecurityController extends Controller
{
 public function verificationNotice(){return view('auth.verify-email');}
 public function verify(EmailVerificationRequest $request){$request->fulfill();return redirect()->route('dashboard')->with('success','Correo verificado correctamente.');}
 public function resend(Request $request){if($request->user()->hasVerifiedEmail())return redirect()->route('dashboard');$request->user()->sendEmailVerificationNotification();return back()->with('status','verification-link-sent');}
 public function forgot(){return view('auth.forgot-password');}
 public function sendReset(Request $request){$request->validate(['email'=>'required|email']);$status=Password::sendResetLink($request->only('email'));if(in_array($status,[Password::RESET_LINK_SENT,Password::INVALID_USER],true)){$message='Si el correo esta registrado, te enviamos un enlace para cambiar la contrasena. Revisa la bandeja de entrada y correo no deseado.';return back()->with(['status'=>$message,'success'=>$message]);}return back()->withErrors(['email'=>'No fue posible enviar el enlace en este momento. Intenta nuevamente.']);}
 public function resetForm(Request $request,string $token){return view('auth.reset-password',['token'=>$token,'email'=>$request->query('email')]);}
 public function reset(Request $request){$request->validate(['token'=>'required','email'=>'required|email','password'=>['required','confirmed',PasswordRule::min(8)->letters()->numbers()]]);$status=Password::reset($request->only('email','password','password_confirmation','token'),function($user,$password){$user->forceFill(['password'=>Hash::make($password),'remember_token'=>Str::random(60)])->save();event(new PasswordReset($user));});return $status===Password::PASSWORD_RESET?redirect()->route('login')->with(['status'=>'Tu contrasena fue cambiada correctamente. Ya puedes iniciar sesion.','success'=>'Tu contrasena fue cambiada correctamente. Ya puedes iniciar sesion.']):back()->withErrors(['email'=>'El enlace no es valido o ya vencio. Solicita uno nuevo.']);}
}