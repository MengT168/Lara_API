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
    //     $validator = Validator::make($request->all(), [
    //         'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => 422, 'error' => $validator->messages()], 422);
    //     }

    //     $fileName = $this->uploadFile($request->file('thumbnail'));

    //     $logo = Logo::create([
    //         'thumbnail' => $fileName,
    //         'author' => Auth::id(),
    //         'status' => true,
    //     ]);

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Logo uploaded successfully',
    //         // 'data' => $logo->append('thumbnail_url'),
    //     ]);
    // }

    // public function listLogos(Request $request)
    // {
    //     $query = Logo::query();

    //     if ($request->has('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     $logos = $query->orderByDesc('created_at')->get()->map(function ($logo) {
    //         $logo->thumbnail_url = asset('uploads/' . $logo->thumbnail);
    //         return $logo;
    //     });

    //     return response()->json([
    //         'status' => 200,
    //         'data' => $logos,
    //     ]);
    // }
    // public function toggleLogoStatus($id)
    // {
    //     $logo = Logo::find($id);

    //     if (!$logo) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Logo not found'
    //         ], 404);
    //     }

    //     $logo->status = !$logo->status;
    //     $logo->updated_at = $this->cambodiaTime();
    //     $logo->save();

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Logo status updated successfully',
    //         'data' => $logo
    //     ], 200);
    // }

    // public function updateLogoSubmit(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'status' => 'nullable|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 422,
    //             'error' => $validator->messages()
    //         ], 422);
    //     }

    //     $logo = Logo::find($id);

    //     if (!$logo) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Logo not found'
    //         ], 404);
    //     }

    //     if ($request->hasFile('thumbnail')) {
    //         $oldPath = public_path('uploads/' . $logo->thumbnail);
    //         if (file_exists($oldPath)) {
    //             unlink($oldPath);
    //         }

    //         $fileName = $this->uploadFile($request->file('thumbnail'));
    //         $logo->thumbnail = $fileName;
    //     }

    //     if ($request->has('status')) {
    //         $logo->status = $request->status;
    //     }

    //     $logo->updated_at = $this->cambodiaTime();
    //     $logo->save();

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Logo updated successfully',
    //         'data' => $logo
    //     ], 200);
    // }

    // public function deleteLogo($id)
    // {
    //     $logo = Logo::find($id);

    //     if (!$logo) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Logo not found'
    //         ], 404);
    //     }

    //     $filePath = public_path('uploads/' . $logo->thumbnail);
    //     if (file_exists($filePath)) {
    //         unlink($filePath);
    //     }

    //     $logo->delete();

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Logo deleted successfully'
    //     ], 200);
    // }


    public function addLogoSubmit(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $fileName = $request->file('thumbnail')->store('', 'public');

        $logo = Logo::create([
            'thumbnail' => $fileName,
            'author' => Auth::id(),
            'status' => true,
        ]);

        $logo->thumbnail_url = Storage::url($logo->thumbnail);

        return response()->json(['status' => 200, 'message' => 'Logo uploaded successfully', 'data' => $logo], 200);
    }

    // public function listLogos()
    // {
    //     $logos = Logo::orderByDesc('created_at')->get();

    //     // Add the full, correct URL to every logo in the list
    //     $logos->transform(function ($logo) {
    //         $logo->thumbnail_url = Storage::url($logo->thumbnail);
    //         return $logo;
    //     });

    //     return response()->json(['status' => 200, 'data' => $logos], 200);
    // }

    public function listLogos()
    {
        $logos = Logo::orderByDesc('created_at')->get();

        $logos->transform(function ($logo) {
            $relativeUrl = Storage::url($logo->thumbnail);

            $logo->thumbnail_url = url($relativeUrl);

            return $logo;
        });

        return response()->json(['status' => 200, 'data' => $logos], 200);
    }

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

    public function updateLogoSubmit(Request $request, $id)
    {
        $request->validate(['thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048']);

        $logo = Logo::find($id);
        if (!$logo) {
            return response()->json(['status' => 404, 'message' => 'Logo not found'], 404);
        }

        if ($request->hasFile('thumbnail')) {
            // Delete the old file from storage before uploading the new one
            Storage::disk('public')->delete($logo->thumbnail);
            // Store the new file and get the new filename
            $logo->thumbnail = $request->file('thumbnail')->store('', 'public');
        }

        $logo->save();
        $logo->thumbnail_url = Storage::url($logo->thumbnail);

        return response()->json(['status' => 200, 'message' => 'Logo updated successfully', 'data' => $logo], 200);
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
}
