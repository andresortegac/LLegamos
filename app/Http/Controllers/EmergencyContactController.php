<?php
namespace App\Http\Controllers;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
class EmergencyContactController extends Controller
{
 public function index(Request $request){return view('safety.contacts',['contacts'=>EmergencyContact::where('user_id',$request->user()->id)->get(),'user'=>session('user')]);}
 public function store(Request $request){$data=$request->validate(['name'=>'required|string|max:120','phone'=>'required|string|max:30','relationship'=>'nullable|string|max:80']);$data['user_id']=$request->user()->id;EmergencyContact::create($data);return back()->with('success','Contacto agregado.');}
 public function destroy(Request $request,int $id){EmergencyContact::where('user_id',$request->user()->id)->findOrFail($id)->delete();return back()->with('success','Contacto eliminado.');}
}