<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    // دالة لتحديث بيانات البروفايل
    public function update(Request $request)
    {
        $user = Auth::user();

        // التحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // تحديث البيانات
        $user->username = $request->username;
        $user->location = $request->location;
        $user->birth_date = $request->birth_date;

        // تحديث الصورة الشخصية إذا تم تحميل صورة جديدة
        if ($request->hasFile('profile_picture')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->profile_picture) {
                Storage::delete('public/profile_pictures/' . $user->profile_picture);
            }

            // حفظ الصورة الجديدة
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile_pictures', $filename);

            // تحديث الصورة في قاعدة البيانات
            $user->profile_picture = $filename;
        }

        // حفظ التعديلات
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    // دالة لاسترجاع بيانات بروفايل المستخدم
    public function getProfile()
    {
        // الحصول على بيانات المستخدم الحالي
        $user = Auth::user();

        // إرجاع البيانات المطلوبة
        return response()->json([
            'username' => $user->username,
            'email' => $user->email,      // البريد الإلكتروني ثابت لا يمكن تغييره
            'phone' => $user->phone,      // الهاتف ثابت لا يمكن تغييره
            'location' => $user->location,
            'birth_date' => $user->birth_date,
        ]);
    }
}
