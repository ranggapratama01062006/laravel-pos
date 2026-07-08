<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        $categories = Product::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('pos', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'sku' => 'required|string|max:80|unique:products,sku',
            'category' => 'required|string|max:80',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($data);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $product,
        ]);
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'cash_received' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $items = collect($request->input('items'));
        $discount = round($request->input('discount', 0), 2);
        $subtotal = round($items->sum('subtotal'), 2);
        $tax = round($subtotal * 0.1, 2);
        $total = round($subtotal + $tax - $discount, 2);
        $cashReceived = round($request->input('cash_received'), 2);

        if ($cashReceived < $total) {
            return response()->json([
                'message' => 'Jumlah tunai tidak mencukupi untuk menyelesaikan transaksi.',
            ], 422);
        }

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                return response()->json(['message' => 'Produk tidak ditemukan.'], 422);
            }

            if ($item['quantity'] > $product->stock) {
                return response()->json([
                    'message' => "Stok produk {$product->name} tidak cukup.",
                ], 422);
            }
        }

        $order = Order::create([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'cash_received' => $cashReceived,
            'change_due' => round(max(0, $cashReceived - $total), 2),
            'notes' => $request->input('notes'),
        ]);

        $orderItems = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            $orderItems[] = [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $product->decrement('stock', $item['quantity']);
        }

        OrderItem::insert($orderItems);

        return response()->json([
            'message' => 'Transaksi berhasil diselesaikan.',
            'receipt' => [
                'order_id' => $order->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'cash_received' => $cashReceived,
                'change_due' => round(max(0, $cashReceived - $total), 2),
                'notes' => $order->notes,
                'items' => $items,
                'created_at' => $order->created_at->format('d M Y H:i'),
            ],
        ]);
    }
}
