<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function addCategorySubmit(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:191',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'error' => $validator->messages()
        ], 422);
    }

    $name = $request->name;

    if ($this->checkExistPost('category', 'name', $name) > 0) {
        return response()->json([
            'status' => 409,
            'message' => 'Category already exists'
        ], 409);
    }

    $slug = $this->slug($name);

    $insert = Category::insert([
        'name' => $name,
        'slug' => $slug,
        'created_at' => $this->cambodiaTime()
    ]);

    if ($insert) {
        return response()->json([
            'status' => 200,
            'message' => 'Category added successfully'
        ], 200);
    } else {
        return response()->json([
            'status' => 500,
            'message' => 'Category add failed'
        ], 500);
    }
}

    public function listCategory(){
        $category = Category::all();
        return response()->json([
            'category'  => $category
        ]);
    }

    public function updateCategorySubmit(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:191',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'error' => $validator->messages()
        ], 422);
    }

    $category = Category::find($id);

    if (!$category) {
        return response()->json([
            'status' => 404,
            'message' => 'Category not found'
        ], 404);
    }

    $exist = Category::where('name', $request->name)
        ->where('id', '!=', $id)
        ->exists();

    if ($exist) {
        return response()->json([
            'status' => 409,
            'message' => 'Category name already in use'
        ], 409);
    }

    $category->name = $request->name;
    $category->slug = $this->slug($request->name);
    $category->updated_at = $this->cambodiaTime();

    if ($category->save()) {
        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully'
        ], 200);
    } else {
        return response()->json([
            'status' => 500,
            'message' => 'Category update failed'
        ], 500);
    }
}

public function deleteCategory($id)
{
    $category = Category::find($id);

    if (!$category) {
        return response()->json([
            'status' => 404,
            'message' => 'Category not found'
        ], 404);
    }

    if ($category->delete()) {
        return response()->json([
            'status' => 200,
            'message' => 'Category deleted successfully'
        ], 200);
    } else {
        return response()->json([
            'status' => 500,
            'message' => 'Category deletion failed'
        ], 500);
    }
}


}
