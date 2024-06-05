<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function index()
    {
        $users = User::query()->where('users.id', '!=', '1')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->join('users_status', 'users.status_id', '=', 'users_status.id')
            ->select(
                'users.*',
                'departments.name as departments',
                'users_status.name as status')
            ->paginate();

        return response()->json($users);
    }

    public function create()
    {
        $users_status = DB::table('users_status')
            ->select(
                'id as value',
                'name as label'
            )
            ->get();
        $departments = DB::table('departments')
            ->select(
                'id as value',
                'name as label'
            )
            ->get();

        return response()->json([
            'users_status' => $users_status,
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'status_id' => 'required',
            'username' => 'required|unique:users,username',
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'department_id' => 'required',
            'password' => 'required|confirmed'
        ], [
            'status_id.required' => 'Nhap tinh trang',
            'username.required' => 'Nhap ten tai khoan',
            'username.unique' => 'Ten tai khoan da ton tai',

            'name.required' => 'Nhap ho va ten',
            'name.max' => 'Nhap toi da 255 ky tu',

            'email.required' => 'Nhap email',
            'email.email' => 'Email khong hop le',
            'email.unique' => 'Email da ton tai',

            'department_id.required' => 'Nhap phong ban',
            'password.required' => 'Nhap mat khau',
            'password.confirmed' => 'Xac nhan lai mat khau'
        ]);

        // nen $request['status_id']
        User::create([
            'status_id' => $request['status_id'],
            'username' => $request['username'],
            'name' => $request['name'],
            'email' => $request['email'],
            'department_id' => $request['department_id'],
            'password' => Hash::make($request['password'])
        ]);

    }

    public function edit($id)
    {
        $user = User::query()->find($id);

        $users_status = DB::table('users_status')
            ->select(
                'id as value',
                'name as label'
            )
            ->get();
        $departments = DB::table('departments')
            ->select(
                'id as value',
                'name as label'
            )
            ->get();

        return response()->json([
            'user' => $user,
            'users_status' => $users_status,
            'departments' => $departments
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status_id' => 'required',
            'username' => 'required | unique:users,username,'.$id,  //khong duoc co dau cach o dau phay
            'name' => 'required | max:255',
            'email' => 'required | email',
            'department_id' => 'required'
        ], [
            'status_id.required' => 'Nhap tinh trang',
            'username.required' => 'Nhap ten tai khoan',
            'username.unique' => 'Ten tai khoan da ton tai',

            'name.required' => 'Nhap ho va ten',
            'name.max' => 'Nhap toi da 255 ky tu',

            'email.required' => 'Nhap email',
            'email.email' => 'Email khong hop le',

            'department_id.required' => 'Nhap phong ban'
        ]);

        User::query()->find($id)->update([
            'status_id' => $request['status_id'],
            'username' => $request['username'],
            'name' => $request['name'],
            'email' => $request['email'],
            'department_id' => $request['department_id']
        ]);

        if ($request['change_password'] == true){
            $validated = $request->validate([
                'password' => 'required | confirmed'
            ], [
                'password.required' => 'Nhap mat khau',
                'password.confirmed' => 'Xac nhan lai mat khau'
            ]);

            User::query()->find($id)->update([
                'password' => Hash::make($request['password']),
                'change_password_at' => NOW()
            ]);
        }
    }

    public function destroy($id)
    {
        User::query()->find($id)->delete();
    }
}
