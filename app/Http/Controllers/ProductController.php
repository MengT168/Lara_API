<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function transformProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'thumbnail' => $product->thumbnail,
            'thumbnail_url' => route('serve.image', ['filename' => $product->thumbnail]),
            'regular_price' => $product->regular_price,
            'sale_price' => $product->sale_price,
            'viewer' => $product->viewer,
            'description' => $product->description,
            'attributes' => $product->attributes->groupBy('type')->map(function ($items) {
                return $items->map(function ($attr) {
                    return [
                        'id' => $attr->id,
                        'value' => $attr->value,
                    ];
                })->values();
            }),
        ];
    }

    public function transformProducts($products)
    {
        return $products->map(function ($product) {
            return $this->transformProduct($product);
        })->values();
    }

    // public function addProductSubmit(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:191',
    //         'qty' => 'required|integer|min:0',
    //         'regular_price' => 'required|numeric',
    //         'sale_price' => 'required|numeric',
    //         'size' => 'required|array',
    //         'color' => 'required|array',
    //         'category' => 'required|integer|exists:category,id',
    //         'thumbnail' => 'nullable|image|max:2048',
    //         'description' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => 422, 'error' => $validator->errors()], 422);
    //     }

    //     try {
    //         $fileName = null;
    //         if ($request->hasFile('thumbnail')) {
    //             $fileName = $request->file('thumbnail')->store('', 'public');
    //         }

    //         $product = Product::create([
    //             'name' => $request->name,
    //             'slug' => $this->slug($request->name),
    //             'quantity' => $request->qty,
    //             'regular_price' => $request->regular_price,
    //             'sale_price' => $request->sale_price,
    //             'category' => $request->category,
    //             'thumbnail' => $fileName,
    //             'author' => Auth::id(),
    //             'description' => $request->description,
    //         ]);

    //         $attributeIds = array_merge($request->size ?? [], $request->color ?? []);
    //         $product->attributes()->attach($attributeIds);

    //         return response()->json(['status' => 200, 'message' => 'Product created successfully', 'data' => $product], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'message' => 'Product creation failed',
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }

    
    
//     public function addProductSubmit(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'name' => 'required|string|max:191',
//         'qty' => 'required|integer|min:0',
//         'regular_price' => 'required|numeric',
//         'sale_price' => 'required|numeric',
//         'size' => 'required|array',
//         'color' => 'required|array',
//         'category' => 'required|integer|exists:category,id',
//         'thumbnail' => 'nullable|image|max:2048',
//         'description' => 'nullable|string',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 422,
//             'error' => $validator->errors()
//         ], 422);
//     }

//     try {
//         $fileName = null;
//         if ($request->hasFile('thumbnail')) {
//             $file = $request->file('thumbnail');

//             // Generate filename: timestamp + original name
//             $fileName = time() . '_' . $file->getClientOriginalName();

//             $uploadPath = public_path('uploads');
//             if (!file_exists($uploadPath)) {
//                 mkdir($uploadPath, 0755, true);
//             }

//             $file->move($uploadPath, $fileName);
//         }

//         $product = Product::create([
//             'name' => $request->name,
//             'slug' => $this->slug($request->name), // You must have slug() method
//             'quantity' => $request->qty,
//             'regular_price' => $request->regular_price,
//             'sale_price' => $request->sale_price,
//             'category' => $request->category,
//             'thumbnail' => $fileName, // Only filename saved
//             'author' => Auth::id(),
//             'description' => $request->description,
//         ]);

//         $attributeIds = array_merge($request->size ?? [], $request->color ?? []);
//         $product->attributes()->attach($attributeIds);

//         return response()->json([
//             'status' => 200,
//             'message' => 'Product created successfully',
//             'data' => $product
//         ], 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => 'Product creation failed',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

public function addProductSubmit(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:191',
        'qty' => 'required|integer|min:0',
        'regular_price' => 'required|numeric',
        'sale_price' => 'required|numeric',
        'size' => 'required|string',
        'color' => 'required|string',
        'category' => 'required|integer|exists:category,id',
        'thumbnail' => 'nullable|image|max:2048',
        'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 422, 'error' => $validator->errors()], 422);
    }

    try {
       $fileName = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $uploadPath = public_path('uploads');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $fileName);
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'quantity' => $request->qty,
            'regular_price' => $request->regular_price,
            'sale_price' => $request->sale_price,
            'category' => $request->category,
            'thumbnail' => $fileName,
            'author' => Auth::id(),
            'description' => $request->description,
        ]);

        $sizeIds = json_decode($request->size, true) ?? [];
        $colorIds = json_decode($request->color, true) ?? [];

        $attributeIds = array_merge($sizeIds, $colorIds);
        if (!empty($attributeIds)) {
            $product->attributes()->attach($attributeIds);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product created successfully',
            'data' => $product
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Product creation failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
   
    public function listProduct()
{
    $products = Product::with('attributes')->latest()->get();

    $data = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'quantity' => $product->quantity,
            'regular_price' => $product->regular_price,
            'sale_price' => $product->sale_price,
            'category' => $product->category,
            'thumbnail' => $product->thumbnail,
            'thumbnail_url' => route('serve.image', ['filename' => $product->thumbnail]),
            'viewer' => $product->viewer,
            'author' => $product->author,
            'description' => $product->description,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'attributes' => $product->attributes->groupBy('type')->map(function ($items) {
                return $items->map(function ($attr) {
                    return [
                        'id' => $attr->id,
                        'value' => $attr->value,
                    ];
                })->values();
            })
        ];
    });

    return response()->json([
        'status' => 200,
        'data' => $data
    ]);
}

    
    // public function updateProductSubmit(Request $request, $id)
    // {
    //     $product = Product::find($id);

    //     if (!$product) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Product not found'
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'sometimes|required|string|max:191',
    //         'qty' => 'sometimes|required|integer|min:0',
    //         'regular_price' => 'sometimes|required|numeric',
    //         'sale_price' => 'sometimes|required|numeric',
    //         'category' => 'sometimes|required|integer|exists:category,id',
    //         'size' => 'sometimes|array',
    //         'color' => 'sometimes|array',
    //         'thumbnail' => 'nullable|image|max:2048',
    //         'description' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => 422, 'error' => $validator->errors()], 422);
    //     }

    //     if ($request->hasFile('thumbnail')) {
    //         $file = $request->file('thumbnail');
    //         $fileName = rand(1, 999) . '-' . $file->getClientOriginalName();
    //         $file->storeAs('public/uploads', $fileName);
    //         $product->thumbnail = $fileName;
    //     }

    //     $product->name = $request->name ?? $product->name;
    //     $product->slug = $this->slug($product->name);
    //     $product->quantity = $request->qty ?? $product->quantity;
    //     $product->regular_price = $request->regular_price ?? $product->regular_price;
    //     $product->sale_price = $request->sale_price ?? $product->sale_price;
    //     $product->category = $request->category ?? $product->category;
    //     $product->description = $request->description ?? $product->description;
    //     $product->updated_at = now();
    //     $product->save();

    //     if ($request->has('size') || $request->has('color')) {
    //         $attributeIds = array_merge($request->size ?? [], $request->color ?? []);
    //         $product->attributes()->sync($attributeIds);
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Product updated successfully',
    //         'data' => $product
    //     ]);
    // }

    
    
    public function updateProductSubmit(Request $request, $id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:191',
        'qty' => 'sometimes|required|integer|min:0',
        'regular_price' => 'sometimes|required|numeric',
        'sale_price' => 'sometimes|required|numeric',
        'category' => 'sometimes|required|integer|exists:category,id',
        'size' => 'sometimes|string',
        'color' => 'sometimes|string',
        'thumbnail' => 'nullable|image|max:2048',
        'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 422, 'error' => $validator->errors()], 422);
    }

    try {
        $fileName = $product->thumbnail; 
        if ($request->hasFile('thumbnail')) {
            if ($fileName) {
                Storage::disk('public')->delete($fileName);
            }
            $fileName = $request->file('thumbnail')->store('', 'public');
        }
        
        $product->name = $request->name ?? $product->name;
        $product->slug = \Str::slug($product->name);
        $product->quantity = $request->qty ?? $product->quantity;
        $product->regular_price = $request->regular_price ?? $product->regular_price;
        $product->sale_price = $request->sale_price ?? $product->sale_price;
        $product->category = $request->category ?? $product->category;
        $product->description = $request->description ?? $product->description;
        $product->thumbnail = $fileName; 
        $product->updated_at = now();
        $product->save();

        if ($request->has('size') || $request->has('color')) {
            $sizeIds = json_decode($request->size, true) ?? [];
            $colorIds = json_decode($request->color, true) ?? [];
            
            $attributeIds = array_merge($sizeIds, $colorIds);
            $product->attributes()->sync($attributeIds);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Product update failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }

        $product->attributes()->detach();

        if ($product->thumbnail && Storage::exists('public/uploads/' . $product->thumbnail)) {
            Storage::delete('public/uploads/' . $product->thumbnail);
        }

        $product->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function productDetail($slug)
    {
        $product = Product::with('attributes')->where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }

        $product->increment('viewer');

        $related = Product::with('attributes')
            ->where('category', $product->category)
            ->where('id', '<>', $product->id)
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return response()->json([
            'status' => 200,
            'product' => $this->transformProduct($product),
            'related_products' => $this->transformProducts($related),
        ]);
    }

     public function searchProducts(Request $request)
    {
        $request->validate(['q' => 'required|string']);
        $searchTerm = $request->q;

        $products = Product::where('name', 'LIKE', '%' . $searchTerm . '%')
                           ->with('attributes')
                           ->latest()
                           ->get();

        $products->transform(function ($product) {
            $product->thumbnail_url = route('serve.image', ['filename' => $product->thumbnail]);
            return $product;
        });

        return response()->json(['status' => 200, 'data' => $products]);
    }
}
