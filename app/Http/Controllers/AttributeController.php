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

    public function listAttribute(){
        $attribute = Attribute::all();
        return response()->json([
            'attribute'  => $attribute
        ]);
    }

}
