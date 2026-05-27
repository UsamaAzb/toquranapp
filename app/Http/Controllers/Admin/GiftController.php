<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\StudentGift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GiftController extends Controller
{
    public function index()
    {
        $gifts = Gift::paginate(10);

        return view('admin.Gifts.index', compact('gifts'));
    }

    public function create()
    {
        return view('admin.Gifts.create');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_points_required' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('image')) {

            $validated['image_path'] = $request->file('image')->store('gifts', 'public');
        }
        $gift = Gift::create($validated);

        return redirect()->route('gifts.index')->with('success', 'Gift created successfully!');
    }

    public function edit(Gift $gift)
    {
        return view('admin.Gifts.edit', compact('gift'));
    }

    public function update(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_points_required' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active'); // يرجّع false لو الحقل مش مبعوت

        if ($request->hasFile('image')) {
            if ($gift->image_path) {
                Storage::disk('public')->delete($gift->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('gifts', 'public');
        }

        $gift->update($validated);

        return redirect()->route('gifts.index')->with('success', 'Gift updated successfully!');
    }

    public function checkBeforeDelete(Gift $gift)
    {
        $isUsed = StudentGift::where('gift_id', $gift->id)->exists();

        return response()->json([
            'isUsed' => $isUsed,
            'giftId' => $gift->id,
            'title' => $gift->title,
        ]);
    }

    public function destroy(Gift $gift)
    {
        if ($gift->image_path) {
            Storage::disk('public')->delete($gift->image_path);
        }
        $gift->delete();

        return redirect()->route('gifts.index')->with('success', 'Gift deleted successfully!');
    }
}
