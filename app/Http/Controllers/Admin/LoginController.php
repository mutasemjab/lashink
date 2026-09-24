<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
  public function show_login_view()
  {
    return view('admin.auth.login');
  }

  public function login(LoginRequest $request)
  {
    if (auth()->guard('admin')->attempt(['username' => $request->input('username'), 'password' => $request->input('password')])) {
      $user = auth()->guard('admin')->user();

      // Only active employees may sign in; the super admin can never be locked out.
      if (!$user->is_super && $user->employment_status !== 'active') {
        auth()->guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.showlogin');
      }

      $request->session()->regenerate();
      return redirect()->route('admin.dashboard');
    } else {
      return redirect()->route('admin.showlogin');
    }
  }

  public function logout(Request $request)
  {
    auth('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('admin.showlogin');
  }


  public function editlogin($id)
  {
    $data = auth('admin')->user();
    return view('admin.auth.edit', compact('data'));
  }



  public function updatelogin(Request $request, $id)
  {
    $admin = auth('admin')->user();

    $request->validate([
      'username' => 'required|string|max:255|unique:admins,username,' . $admin->id,
      'password' => 'required|string|min:6|confirmed',
    ]);

    try {
      $admin->username = $request->get('username');
      $admin->password = Hash::make($request->password);

      if ($admin->save()) {
        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.showlogin');
      } else {
        return redirect()->back()->with(['error' => 'Something wrong']);
      }
    } catch (\Exception $ex) {
      return redirect()->back()
        ->with(['error' => 'عفوا حدث خطأ ما' . $ex->getMessage()])
        ->withInput();
    }
  }


  /*

function make_new_admin(){
$admin=new App\Models\Admin();
$admin->name='admin';
$admin->email='test@gmail.com';
$admin->username='admin';
$admin->password=bcrypt("admin");
$admin->com_code=1;
$admin->save();

}
*/
}
