<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of all items
     */
    public function index()
    {
        $items = Item::with('user')->orderBy('created_at', 'desc')->get();
        
        // Format items to include owner information
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $item->gambar,
                'ownerNama' => $item->user->name,
                'ownerEmail' => $item->user->email,
                'user_id' => $item->user_id,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Display items owned by the authenticated user
     */
    public function myItems()
    {
        $items = Item::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $item->gambar,
                'ownerNama' => Auth::user()->name,
                'ownerEmail' => Auth::user()->email,
                'user_id' => $item->user_id,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'stok' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'max_hari' => 'required|integer|min:1',
            'gambar' => 'required|string', // base64 encoded image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $item = Item::create([
            'nama' => $request->nama,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'max_hari' => $request->max_hari,
            'gambar' => $request->gambar,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Barang berhasil diupload!',
            'item' => [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $item->gambar,
                'ownerNama' => Auth::user()->name,
                'ownerEmail' => Auth::user()->email,
                'user_id' => $item->user_id,
            ]
        ], 201);
    }

    /**
     * Display the specified item
     */
    public function show($id)
    {
        $item = Item::with('user')->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $item->gambar,
                'ownerNama' => $item->user->name,
                'ownerEmail' => $item->user->email,
                'user_id' => $item->user_id,
            ]
        ]);
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        // Check if user owns the item
        if ($item->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|string|max:255',
            'stok' => 'sometimes|integer|min:0',
            'deskripsi' => 'nullable|string',
            'max_hari' => 'sometimes|integer|min:1',
            'gambar' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($request->only(['nama', 'stok', 'deskripsi', 'max_hari', 'gambar']));

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'item' => [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $item->gambar,
                'ownerNama' => Auth::user()->name,
                'ownerEmail' => Auth::user()->email,
                'user_id' => $item->user_id,
            ]
        ]);
    }

    /**
     * Remove the specified item
     */
    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        // Check if user owns the item
        if ($item->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Barang berhasil dihapus!'
        ]);
    }
}
