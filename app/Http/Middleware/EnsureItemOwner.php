<?php

namespace App\Http\Middleware;

use App\Models\Item;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureItemOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $itemId = $request->route('id') ?? $request->route('item');
        
        if (!$itemId) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID not found in request'
            ], 400);
        }

        $item = Item::find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        if ($item->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not own this item.'
            ], 403);
        }

        // Add item to request for later use
        $request->merge(['item' => $item]);

        return $next($request);
    }
}
