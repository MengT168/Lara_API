<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function addAttributeSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|max:191',
            'value' => 'required|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages()
            ], 422);
        }

        $type = strtolower($request->type);
        $value = strtolower($request->value);

        $exists = Attribute::whereRaw('LOWER(type) = ?', [$type])
            ->whereRaw('LOWER(value) = ?', [$value])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 409,
                'message' => 'Attribute already exists'
            ], 409);
        }

        $attribute = Attribute::create([
            'type' => $request->type,
            'value' => $request->value,
            'created_at' => $this->cambodiaTime(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Attribute added successfully',
            'data' => $attribute
        ], 200);
    }

    public function listAttribute()
    {
        $attribute = Attribute::all();
        return response()->json([
            'attribute'  => $attribute
        ]);
    }

    public function updateAttribute(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|max:191',
            'value' => 'required|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages()
            ], 422);
        }

        $type = strtolower($request->type);
        $value = strtolower($request->value);

        $exists = Attribute::whereRaw('LOWER(type) = ?', [$type])
            ->whereRaw('LOWER(value) = ?', [$value])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 409,
                'message' => 'Attribute with same type and value already exists'
            ], 409);
        }

        $attribute = Attribute::find($id);

        if (!$attribute) {
            return response()->json([
                'status' => 404,
                'message' => 'Attribute not found'
            ], 404);
        }

        $attribute->type = $request->type;
        $attribute->value = $request->value;
        $attribute->updated_at = $this->cambodiaTime();
        $attribute->save();

        return response()->json([
            'status' => 200,
            'message' => 'Attribute updated successfully',
            'data' => $attribute
        ], 200);
    }

    public function deleteAttribute($id)
    {
        $attribute = Attribute::find($id);

        if (!$attribute) {
            return response()->json([
                'status' => 404,
                'message' => 'Attribute not found'
            ], 404);
        }

        $attribute->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Attribute deleted successfully'
        ], 200);
    }
}
