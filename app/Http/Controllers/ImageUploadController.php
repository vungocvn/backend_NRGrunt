<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        // Kiểm tra xem ảnh có được gửi kèm trong request không
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Lấy file ảnh từ request
            $image = $request->file('image');

            // Tạo tên ảnh ngẫu nhiên để tránh trùng lặp
            $imageName = time() . '_' . $image->getClientOriginalName();

            // Lưu ảnh vào thư mục public/uploads (hoặc thư mục bạn muốn lưu)
            $imagePath = $image->storeAs('public/upload', $imageName);

            return response()->json([
                'status' => 200,
                'message' => 'Image uploaded successfully!',
                'data' => [
                    'imagePath' => $imagePath
                ]
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No image uploaded or the file is invalid',
            ], 400);
        }
    }
}
