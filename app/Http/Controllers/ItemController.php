<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of all items
     */
    public function index()
    {
        $items = Item::with('user')->orderBy('created_at', 'desc')->get();
        
        // Format items to include owner information and full image URL
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $this->fileUploadService->getFileUrl($item->gambar),
                'gambar_path' => $item->gambar,
                'ownerNama' => $item->user->name,
                'ownerEmail' => $item->user->email,
                'user_id' => $item->user_id,
                'isAvailable' => $item->isAvailable(),
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
            'gambar' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048', // File upload
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Upload image
            $imagePath = $this->fileUploadService->uploadItemImage($request->file('gambar'));

            $item = Item::create([
                'nama' => $request->nama,
                'stok' => $request->stok,
                'deskripsi' => $request->deskripsi,
                'max_hari' => $request->max_hari,
                'gambar' => $imagePath,
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
                    'gambar' => $this->fileUploadService->getFileUrl($imagePath),
                    'gambar_path' => $imagePath,
                    'ownerNama' => Auth::user()->name,
                    'ownerEmail' => Auth::user()->email,
                    'user_id' => $item->user_id,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload barang: ' . $e->getMessage()
            ], 500);
        }
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
            'gambar' => 'sometimes|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only(['nama', 'stok', 'deskripsi', 'max_hari']);

            // Handle image update
            if ($request->hasFile('gambar')) {
                // Delete old image
                $this->fileUploadService->deleteFile($item->gambar);
                
                // Upload new image
                $updateData['gambar'] = $this->fileUploadService->uploadItemImage($request->file('gambar'));
            }

            $item->update($updateData);
            $item->refresh(); // Reload the model from database to get fresh values

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'stok' => $item->stok,
                    'deskripsi' => $item->deskripsi,
                    'maxHari' => $item->max_hari,
                    'gambar' => $this->fileUploadService->getFileUrl($item->gambar),
                    'gambar_path' => $item->gambar,
                    'ownerNama' => Auth::user()->name,
                    'ownerEmail' => Auth::user()->email,
                    'user_id' => $item->user_id,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate barang: ' . $e->getMessage()
            ], 500);
        }
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

        try {
            // Delete image from storage
            $this->fileUploadService->deleteFile($item->gambar);
            
            // Delete item
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => '🗑️ Barang berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items by name or description
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $items = Item::with('user')
            ->where(function($q) use ($query) {
                $q->where('nama', 'LIKE', "%{$query}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'stok' => $item->stok,
                'deskripsi' => $item->deskripsi,
                'maxHari' => $item->max_hari,
                'gambar' => $this->fileUploadService->getFileUrl($item->gambar),
                'gambar_path' => $item->gambar,
                'ownerNama' => $item->user->name,
                'ownerEmail' => $item->user->email,
                'user_id' => $item->user_id,
                'isAvailable' => $item->isAvailable(),
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items,
            'query' => $query
        ]);
    }
}
