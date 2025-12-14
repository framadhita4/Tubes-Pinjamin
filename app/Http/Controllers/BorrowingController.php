<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Services\BorrowingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    protected $borrowingService;

    public function __construct(BorrowingService $borrowingService)
    {
        $this->borrowingService = $borrowingService;
    }

    /**
     * Display a listing of borrowings
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $borrowings = $this->borrowingService->getUserBorrowings($user->id, $user->role);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $borrowings
            ]);
        }

        return view('borrowings.index', compact('borrowings'));
    }

    /**
     * Store a new borrowing request
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'foto_ktm' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'lama_hari' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            $data = $request->all();
            $data['peminjam_id'] = Auth::id();
            $data['foto_ktm'] = $request->file('foto_ktm');

            $borrowing = $this->borrowingService->createBorrowingRequest($data);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan peminjaman berhasil dikirim',
                'data' => $borrowing->load(['item', 'peminjam'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permintaan peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified borrowing
     */
    public function show($id)
    {
        $borrowing = Borrowing::with(['item', 'peminjam', 'item.user'])
            ->findOrFail($id);

        // Check authorization
        $user = Auth::user();
        if ($borrowing->peminjam_id !== $user->id && $borrowing->item->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $borrowing
        ]);
    }

    /**
     * Approve a borrowing request (Costumer only)
     */
    public function approve($id)
    {
        try {
            $borrowing = Borrowing::findOrFail($id);
            
            // Check if user is the owner
            if ($borrowing->item->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $borrowing = $this->borrowingService->approveBorrowing($id);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil disetujui',
                'data' => $borrowing->load(['item', 'peminjam'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a borrowing request (Costumer only)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'nullable|string|max:500'
        ]);

        try {
            $borrowing = Borrowing::findOrFail($id);
            
            // Check if user is the owner
            if ($borrowing->item->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $borrowing = $this->borrowingService->rejectBorrowing($id, $request->alasan_penolakan);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil ditolak',
                'data' => $borrowing->load(['item', 'peminjam'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request return for a borrowing
     */
    public function requestReturn(Request $request, $id)
    {
        $request->validate([
            'kondisi' => 'required|string|max:500',
            'foto_kondisi' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $borrowing = Borrowing::findOrFail($id);
            
            // Check if user is the borrower
            if ($borrowing->peminjam_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $data = [
                'kondisi' => $request->kondisi,
                'foto_kondisi' => $request->file('foto_kondisi')
            ];

            $borrowing = $this->borrowingService->requestReturn($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan pengembalian berhasil dikirim',
                'data' => $borrowing->load(['item', 'peminjam'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve return request (Costumer only)
     */
    public function approveReturn(Request $request, $id)
    {
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        try {
            $borrowing = Borrowing::findOrFail($id);
            
            // Check if user is the owner
            if ($borrowing->item->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $borrowing = $this->borrowingService->approveReturn($id, $request->rating);

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian berhasil disetujui',
                'data' => $borrowing->load(['item', 'peminjam'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get borrowing history
     */
    public function history()
    {
        $user = Auth::user();
        
        if ($user->role === 'peminjam') {
            $borrowings = Borrowing::where('peminjam_id', $user->id)
                ->whereIn('status', ['returned', 'rejected', 'cancelled'])
                ->with(['item', 'item.user'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $borrowings = Borrowing::whereHas('item', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['returned', 'rejected', 'cancelled'])
            ->with(['item', 'peminjam'])
            ->orderBy('updated_at', 'desc')
            ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $borrowings
        ]);
    }

    /**
     * Cancel a borrowing request
     */
    public function cancel($id)
    {
        try {
            $borrowing = Borrowing::findOrFail($id);
            
            // Only borrower can cancel their own request
            if ($borrowing->peminjam_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Can only cancel if status is pending
            if ($borrowing->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dapat membatalkan permintaan yang masih pending'
                ], 400);
            }

            $borrowing = $this->borrowingService->cancelBorrowing($id);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan peminjaman berhasil dibatalkan',
                'data' => $borrowing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }
}

