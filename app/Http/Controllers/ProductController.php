<?php

namespace App\Http\Controllers;

use App\Exceptions\APIException;
use App\Http\Requests\ProductReq;
use App\Service\extend\IServiceProduct as ExtendIServiceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Review;

class ProductController extends Controller
{
    private ExtendIServiceProduct $productSV;

    public function __construct(ExtendIServiceProduct $productSV)
    {
        $this->productSV = $productSV;
    }
    public function getAll(Request $request)
    {
        $requestParam = $request->query();
        $user = auth()->user();

        if ($user && ($this->hasRole(['Admin', 'Admin']))) {
            $dataPage = $this->productSV->managerAllProducts($requestParam);
        } else {
            $dataPage = $this->productSV->getAll($requestParam);
        }
        $data = $this->getDataPaginate($dataPage);
        $productIds = array_column($data['items'], 'id');

        $ratings = \App\Models\Review::selectRaw('product_id, AVG(rating) as avg_rating, COUNT(*) as rating_count')
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');
        foreach ($data['items'] as &$item) {
            if (!isset($item['id'])) continue;

            $item['sold_quantity'] = $this->productSV->getTotalSold($item['id']);

            $rating = $ratings[$item['id']] ?? null;
            $item['avg_rating'] = $rating ? round($rating->avg_rating, 1) : 0;
            $item['rating_count'] = $rating->rating_count ?? 0;
        }

        return $this->returnJson($data, 200, "success!");
    }

    public function create(ProductReq $request)
    {
        $this->authorizeRole(['Admin', 'Admin']);
        $data = $request->all();

        try {
            \Log::info('Product Create Request:', $data);

            $result = $this->productSV->create($data);

            if ($result) {
                return $this->returnJson($result, 200, "created successfully!");
            } else {
                throw new APIException(500, "Create failed!");
            }
        } catch (\Exception $e) {
            \Log::error('Create product error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getById($id)
    {
        $data = $this->productSV->findById($id);

        $data = $this->productSV->findById($id);

        if (!empty($data)) {
            $data['avg_rating'] = round(Review::where('product_id', $id)->avg('rating') ?? 0, 1);
            $data['rating_count'] = Review::where('product_id', $id)->count();

            return $this->returnJson($data, 200, "success!");
        }
    }

    public function update($id, ProductReq $request)
    {
        $this->authorizeRole(['Admin', 'Admin']);
        $data = $request->all();
        $result = $this->productSV->update($id, $data);

        if ($result) {
            return $this->returnJson($result, 200, "success!");
        } else {
            throw new APIException(500, "failure!");
        }
    }
    public function destroy($id)
    {
        $this->authorizeRole(['Admin', 'Admin']);
        $result = $this->productSV->delete($id);
        if ($result) {
            return $this->returnJson($result, 200, "success!");
        } else {
            throw new APIException(500, "failure!");
        }
    }

    public function changeStatus($id)
    {
        $this->authorizeRole(['Admin', 'Admin']);
        $result = $this->productSV->changeStatus($id);
        return $this->returnJson($result, 200, "success!");
    }
    public function uploadImage(Request $request)
    {
        // Kiểm tra nếu file đã được gửi
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return response()->json(['error' => 'No valid file uploaded'], 400);
        }

        // Xác định thư mục lưu trữ
        $directory = storage_path('app/public/images');

        // Kiểm tra nếu thư mục chưa có thì tạo
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0777, true);
        }

        // Lấy ảnh từ request và tạo tên ảnh ngẫu nhiên
        $image = $request->file('file');
        $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();

        // Lưu ảnh vào thư mục 'public/images'
        $path = $image->storeAs('public/images', $imageName);

        // Trả về URL của ảnh đã upload
        $imageUrl = asset('storage/images/' . $imageName);

        return response()->json([
            'status' => 200,
            'message' => 'Image uploaded successfully!',
            'filePath' => $imageUrl
        ]);
    }
}
