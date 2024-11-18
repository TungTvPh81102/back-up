<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApiResponseTrait;
use App\Traits\ImageUploadTrait;
use App\Traits\LoggableTrait;
use Cloudinary\Cloudinary;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponseTrait, ApiResponseHelpers, LoggableTrait, ImageUploadTrait;

    const FOLDER_NAME = 'products';
    const FOLDER_NAME_VARIANT = 'variants';

    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Product::query()
                ->with([
                    'category',
                    'brand',
                    'variants',
                    'variants.attributes'
                ])
                ->latest('id')
                ->paginate(10);

            if ($data->isEmpty()) {
                return $this->respondNotFound('Không có sản phẩm nào');
            }

            return $this->respondOk('Danh sách sản phẩm', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Đã có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'], '-') : null;

            $uploadedSingleImages = [];
            $uploadedMultipleImages = [];

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');

                $uploadResult = $this->uploadImage($thumbnail, self::FOLDER_NAME);

                if ($uploadResult) {
                    $data['thumbnail'] = $uploadResult;
                    $uploadedSingleImages[] = $uploadResult;
                } else {
                    $data['thumbnail'] = null;
                }
            } else {
                $data['thumbnail'] = null;
            }

            $product = Product::create($data);

            if ($data['is_variants_enabled'] && !empty($data['variants'])) {
                $productVariants = $data['variants'];

                foreach ($productVariants as $variant) {
                    if (!empty($variant['thumbnails'])) {
                        $multipleUploadImages = $this->multipleUploadImage($variant['thumbnails'], self::FOLDER_NAME_VARIANT);
                        $variant['thumbnails'] = json_encode($multipleUploadImages);

                        if ($multipleUploadImages) {
                            $variant['thumbnails'] = json_encode($multipleUploadImages);
                            $uploadedMultipleImages[] = $multipleUploadImages;
                        } else {
                            $variant['thumbnails'] = null;
                        }
                    } else {
                        $variant['thumbnails'] = null;
                    }

                    $variant['product_id'] = $product->id;

                    $variantModel = $product->variants()->create($variant);

                    foreach ($variant['attribute_values'] as $attributeData) {
                        $attributeValue = AttributeValue::find($attributeData['id']);

                        $variantModel->attributes()->attach($attributeValue);
                    }
                }
            }

            DB::commit();

            return $this->respondCreated('Tạo sản phẩm thành công', $product);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            foreach ($uploadedSingleImages as $uploadedImage) {
                $this->deleteImage($uploadedImage, self::FOLDER_NAME);
            }

            foreach ($uploadedMultipleImages as $images) {
                $this->deleteMultipleImage($images, self::FOLDER_NAME_VARIANT);
            }

            if ($e instanceof ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Đã có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::query()
                ->with([
                    'category',
                    'brand',
                    'variants',
                    'variants.attributes'
                ])
                ->find($id);

            if (empty($product)) {
                return $this->respondNotFound('Không tìm thế dữ liệu');
            }

            return $this->respondOk('Chi tiết sản phẩm: ' . $product->name, $product);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Đã có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $product = Product::query()
                ->with([
                    'category',
                    'brand',
                    'variants',
                    'variants.attributes'
                ])
                ->find($id);

            if (empty($product)) {
                return $this->respondNotFound('Không tìm thấy sản phẩm');
            }

            $data = $request->validated();

            $data['slug'] = !empty($data['name']) ? Str::slug($data['name'], '-') : $product->slug;

            $uploadedSingleImages = [];
            $uploadedMultipleImages = [];

            if ($request->hasFile('thumbnail')) {
                $this->deleteImage($product->thumbnail, self::FOLDER_NAME);

                $thumbnail = $request->file('thumbnail');

                $uploadResult = $this->uploadImage($thumbnail, self::FOLDER_NAME);

                if ($uploadResult) {
                    $data['thumbnail'] = $uploadResult;
                    $uploadedSingleImages[] = $uploadResult;
                } else {
                    $data['thumbnail'] = $product->thumbnail;
                }
            } else {
                $data['thumbnail'] = $product->thumbnail;
            }

            $product->update($data);

            if ($data['is_variants_enabled'] && !empty($data['variants'])) {
                $productVariants = $data['variants'];

                foreach ($productVariants as $variant) {
                    if (!empty($variant['thumbnails'])) {
                        $oldImage = json_decode($variant['thumbnails'], true);

                        $this->deleteMultipleImage($oldImage, self::FOLDER_NAME_VARIANT);

                        $multipleUploadImages = $this->multipleUploadImage($variant['thumbnails'], self::FOLDER_NAME_VARIANT);
                        $variant['thumbnails'] = json_encode($multipleUploadImages);

                        if ($multipleUploadImages) {
                            $variant['thumbnails'] = json_encode($multipleUploadImages);
                            $uploadedMultipleImages[] = $multipleUploadImages;
                        } else {
                            $variant['thumbnails'] =  $product->variants->thumbnails;;
                        }
                    } else {
                        $variant['thumbnails'] = $product->variants->thumbnails;
                    }

                    $product->variants()->update($variant);

                    foreach ($variant['attribute_values'] as $attributeData) {
                        $attributeValue = AttributeValue::find($attributeData['id']);

                        $variant->attributes()->sync($attributeValue);
                    }
                }
            }

            DB::commit();

            return $this->respondOk('Cập nhật sản phẩm', $product);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            foreach ($uploadedSingleImages as $uploadedImage) {
                $this->deleteImage($uploadedImage, self::FOLDER_NAME);
            }

            foreach ($uploadedMultipleImages as $images) {
                $this->deleteMultipleImage($images, self::FOLDER_NAME_VARIANT);
            }

            if ($e instanceof ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Đã có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::query()->find($id);

            if (empty($product)) {
                return $this->respondNotFound('Không tìm thấy sản phẩm');
            }

            $product->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Đã có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
