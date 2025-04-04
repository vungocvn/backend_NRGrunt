<?php

namespace App\Http\Controllers;
use App\Exceptions\AuthorizeException;
use App\Models\Role;
use App\Exceptions\APIException;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\UserReq;
use App\Mail\ActiveUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class UserController extends Controller
{
    public function signup(UserReq $request)
    {
        $data = $request->all();
        $user = $this->userSV->create($data);

        return $this->returnJson($user, 201, "resigter success!");
    }

    public function updateProfile(UpdateUser $request)
    {
        $user = $this->getAuth();
        $data = $request->all();
        $dataUpdate = $this->userSV->update($user->id, $data);

        return $this->returnJson($dataUpdate, 200, "update successful!");
    }

    public function getAll(Request $request)
    {
        $this->authorizeRole('Admin');
        $data = $this->userSV->getAll($request);
        if (!$data || empty($data)) {
            throw new APIException(500, "failure!");
        }

        return $this->returnJson($data, 200, "success!");
    }

    public function changeRole($id, Request $rq)
    {
        $this->authorizeRole('Admin');
        $roleName = $this->validateField($rq->role, 'role');

        $role = $this->userSV->changeRole($id, $roleName);
        return $this->returnJson($role, 200, "changed role successfully!");
    }

    public function changeStatus($id, Request $req)
    {
        $this->authorizeRole('Admin');
        $status = $this->validateField($req->status, 'status');

        $this->userSV->changeStatus($id, $status);

        return $this->returnJson($status, 200, "changed status successfully!");
    }

    public function activeByMail()
    {
        $user = $this->getAuth();

        $isAdmin = in_array($user->role, ['Admin', 'Admin']);
        if ($isAdmin) {
            $this->userSV->activeUser($user->hash_code);

            return $this->returnJson([
                'role' => $user->role
            ], 200, "you don't need to activate users because you are an admin!");
        } else {
            if ($user->status === 1) {
                return $this->returnJson(null, 202, "your account is already active!");
            }

            $record = $this->generateOtp($user->email, $user->hash_code, 'active');

            Mail::to($user->email)->queue(new ActiveUser($user->name, $user->hash_code,  $record['otp']));
            return $this->returnJson($user->role, 202, "email sent successfully , please check your email address and continue!");
        }
    }

    public function enable2FAReq()
    {
        $user = $this->getAuth();
        $notEnable = $this->userSV->findByHash($user->hash_code)->is_enabled_2fa;

        if (!$notEnable) {
            return $this->returnJson(true, 200, "You already have enabled 2FA!");
        } else {
            $record = $this->generateOtp($user->email, $user->hash_code, 'enable_2fa');
            Mail::to($user->email)->queue(new ActiveUser($user->name, $user->hash_code, $record['otp']));
            return $this->returnJson(null, 200, "Email sent successfully , please check your email to enable 2FA!");
        }
    }

    public function enable2FAForm($hash)
    {
        $user = $this->userSV->findByHash($hash);
        return view('', compact('user'));
    }

    public function enable2FA(Request $request)
    {
        $this->validateField($request->hash, 'uuid');
        $this->validateField($request->otp, 'otp');

        $this->verifyOTP($request->hash, $request->otp, 'enable_2fa');
        $notEnable = $this->userSV->enable2FA($request->hash);
        $this->deleteOTP($request->hash, 'enable_2fa');

        if (!$notEnable) {
            return $this->returnJson(true, 200, 'you already enabled 2fa!');
        } else {
            return $this->returnJson(true, 202, 'you enabled 2fa!');
        }
    }

    public function activeUsers($hash_code, Request $req)
    {
        $otp = $req->otp;
        if (!$otp) {
            throw new APIException(422, "OTP is required!");
        }
        $this->verifyOTP($hash_code, $otp, 'active');
        $status = $this->userSV->activeUser($hash_code);

        $this->deleteOTP($hash_code, 'active');
        if ($status === 1) {
            return $this->returnJson(null, 201, "your account is already active!");
        }

        return $this->returnJson(null, 200, "active user successfully!");
    }

    public function viewActive($hash_code)
    {
        $user = $this->userSV->findByHash($hash_code);
        if (!$user) {
            throw new APIException(404, "user by this hash not found!");
        }

        $userName = $user->name;
        $email = $user->email;
        $avatar = $user->avatar;
        $hash_code = $user->hash_code;

        return view('active.active', compact('userName', 'email', 'hash_code', 'avatar'));
    }

    public function getCustomers()
    {
        $this->authorizeRole(['Admin', 'Customer']);
        $users = $this->userSV->getAll(['id', 'name', 'email', 'phone', 'address', 'created_at']);
        return $this->returnJson($users, 200, 'Fetched customers successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function getCustomerById($id)
    {
        $this->authorizeRole(['Admin', 'Customer']);
        $user = $this->userSV->findById($id);
        if (!$user) {
            throw new APIException(404, 'User not found');
        }
        return $this->returnJson($user, 200, 'Fetched customer successfully!');
    }

    public function updateUser(Request $request, $id)
    {
        $this->authorizeRole(["Admin", "Customer"]);


        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
        ]);

        $temp = auth()->user();
        $user = User::findOrFail($temp->id);

        $user->name = $request->name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->address = $request->address ?? $user->address;

        if ($request->filled('password')) {
            $user->password = \Hash::make($request->password);
        }

        $user->save();

        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles'),
        ]);
    {
        $this->authorizeRole(["Admin", "Customer"]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:6',

        ]);

        $temp = auth()->user();
        $user = User::findOrFail($temp->id);

        $user->name = $request->name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->address = $request->address ?? $user->address;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
      }
    }

}
