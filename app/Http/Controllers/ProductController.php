<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    // CRUD protegido (Crear)
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        return response()->json($product, 201);
    }

    // CRUD protegido (Actualizar)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $product->update($request->all());
        return response()->json($product);
    }

    // CRUD protegido (Eliminar)
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
