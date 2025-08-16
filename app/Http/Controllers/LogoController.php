<?php

namespace App\Http\Controllers;

use App\Models\Logo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LogoController extends Controller
{

    // public function addLogoSubmit(Request $request)
    // {
    //     $request->validate([
    //         'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     $fileName = $request->file('thumbnail')->store('', 'public');

    //     $logo = Logo::create([
    //         'thumbnail' => $fileName,
    //         'author' => Auth::id(),
    //         'status' => true,
    //     ]);

    //     $logo->thumbnail_url = Storage::url($logo->thumbnail);

    //     return response()->json(['status' => 200, 'message' => 'Logo uploaded successfully', 'data' => $logo], 200);
    // }



    public function addLogoSubmit(Request $request)
{
    $validator = Validator::make($request->all(), [
        'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 422, 'error' => $validator->errors()], 422);
    }

    try {
        $fileName = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');

            // Create filename with timestamp
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Upload path
            $uploadPath = public_path('uploads');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file
            $file->move($uploadPath, $fileName);
        }

        // check if this is the first logo
        $isFirstLogo = !Logo::exists();

        $logo = Logo::create([
            'thumbnail' => $fileName,
            'author' => Auth::id(),
            'status' => $isFirstLogo, 
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Logo uploaded successfully',
            'data' => [
                'id' => $logo->id,
                'thumbnail' => $logo->thumbnail,
                'thumbnail_url' => route('serve.image', ['filename' => $logo->thumbnail]),
                'author' => $logo->author,
                'status' => $logo->status,
                'created_at' => $logo->created_at,
            ]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Logo upload failed',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function listLogos()
{
    $logos = Logo::orderByDesc('created_at')->get();

    $data = $logos->map(function ($logo) {
        return [
            'id' => $logo->id,
            'thumbnail' => $logo->thumbnail,
            'thumbnail_url' => route('serve.image', ['filename' => $logo->thumbnail]),
            'author' => $logo->author,
            'status' => $logo->status,
            'created_at' => $logo->created_at,
            'updated_at' => $logo->updated_at,
        ];
    });

    return response()->json([
        'status' => 200,
        'data' => $data
    ], 200);
}



    // public function listLogos()
    // {
    //     $logos = Logo::orderByDesc('created_at')->get();

    //     $logos->transform(function ($logo) {
    //         $relativeUrl = Storage::url($logo->thumbnail);

    //         $logo->thumbnail_url = url($relativeUrl);

    //         return $logo;
    //     });

    //     return response()->json(['status' => 200, 'data' => $logos], 200);
    // }

    public function toggleLogoStatus($id)
    {
        $logo = Logo::find($id);
        if (!$logo) {
            return response()->json(['status' => 404, 'message' => 'Logo not found'], 404);
        }

        $logo->status = !$logo->status;
        $logo->save();

        return response()->json(['status' => 200, 'message' => 'Logo status updated successfully', 'data' => $logo], 200);
    }

    // public function updateLogoSubmit(Request $request, $id)
    // {
    //     $request->validate(['thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048']);

    //     $logo = Logo::find($id);
    //     if (!$logo) {
    //         return response()->json(['status' => 404, 'message' => 'Logo not found'], 404);
    //     }

    //     if ($request->hasFile('thumbnail')) {
    //         // Delete the old file from storage before uploading the new one
    //         Storage::disk('public')->delete($logo->thumbnail);
    //         // Store the new file and get the new filename
    //         $logo->thumbnail = $request->file('thumbnail')->store('', 'public');
    //     }

    //     $logo->save();
    //     $logo->thumbnail_url = Storage::url($logo->thumbnail);

    //     return response()->json(['status' => 200, 'message' => 'Logo updated successfully', 'data' => $logo], 200);
    // }

    
    public function updateLogoSubmit(Request $request, $id)
{
    $request->validate([
        'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $logo = Logo::find($id);
    if (!$logo) {
        return response()->json(['status' => 404, 'message' => 'Logo not found'], 404);
    }

    if ($request->hasFile('thumbnail')) {
        if ($logo->thumbnail && file_exists(public_path('uploads/logo/' . $logo->thumbnail))) {
            unlink(public_path('uploads/logo/' . $logo->thumbnail));
        }

        // Generate unique file name
        $file = $request->file('thumbnail');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path('uploads/logo'), $filename);

        $logo->thumbnail = $filename;
    }

    $logo->save();

    // Return API response with full URL
    $logo->thumbnail_url = asset('uploads/logo/' . $logo->thumbnail);

    return response()->json([
        'status' => 200,
        'message' => 'Logo updated successfully',
        'data' => $logo
    ], 200);
}

    
    public function deleteLogo($id)
    {
        $logo = Logo::find($id);
        if (!$logo) {
            return response()->json(['status' => 404, 'message' => 'Logo not found'], 404);
        }

        // Use the Storage facade to safely delete the file
        Storage::disk('public')->delete($logo->thumbnail);

        $logo->delete();

        return response()->json(['status' => 200, 'message' => 'Logo deleted successfully'], 200);
    }

    public function getLogo()
{
    $logos = Logo::where('status', 1)->get();

    $logos->map(function ($logo) {
        $logo->thumbnail_url = $logo->thumbnail 
            ? route('serve.image', ['filename' => $logo->thumbnail]) 
            : null;
        return $logo;
    });

    return response()->json([
        'status' => 200,
        'message' => 'Logo get successfully',
        'data' => $logos
    ]);
}
}
