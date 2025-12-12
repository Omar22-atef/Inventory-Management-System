<?php

namespace App\Http\Controllers;

use App\Mail\OrderSupplierMail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupplierOrderController extends Controller
{
    public function sendOrderEmail(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $supplier = $product->supplier;
        if (!$supplier || empty($supplier->email)) {
            return response()->json(['message' => 'No supplier email found for this product'], 422);
        }

        Mail::to($supplier->email)->queue(
            new OrderSupplierMail($product, $supplier, $product->quantity)
        );

        return response()->json(['message' => 'Email sent to supplier']);
    }
}
