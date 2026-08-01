<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->activeBranchId();

        $query = Product::with(['category', 'inventories' => fn ($q) => $q->where('branch_id', $branchId)])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('name');

        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('app.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories     = Category::where('is_active', true)->orderBy('sort_order')->get();
        $modifierGroups = ModifierGroup::with('options')->get();
        return view('app.products.form', compact('categories', 'modifierGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'category_id'       => 'nullable|exists:categories,id',
            'sku'               => 'nullable|string|max:50',
            'barcode'           => 'nullable|string|max:50',
            'description'       => 'nullable|string',
            'unit'              => 'required|string|max:20',
            'price'             => 'required|numeric|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'tax_rate'          => 'nullable|numeric|min:0|max:100',
            'track_stock'       => 'boolean',
            'is_active'         => 'boolean',
            'image'             => 'nullable|image|max:2048',
            'variants'          => 'nullable|array',
            'variants.*.name'   => 'required|string|max:100',
            'variants.*.price_adjustment' => 'nullable|numeric',
            'modifier_groups'   => 'nullable|array',
            'modifier_groups.*' => 'exists:modifier_groups,id',
        ]);

        $company = auth()->user()->company;
        if ($company && ! $company->canAddProduct()) {
            return back()->withErrors(['name' => 'Batas jumlah produk paket Anda telah tercapai. Upgrade paket untuk menambah produk.']);
        }

        $data['track_stock'] = $request->boolean('track_stock', true);
        $data['is_active']   = $request->boolean('is_active', true);
        $data['sku']         = ($data['sku'] ?? null) ?: 'PRD-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $data['barcode']     = ($data['barcode'] ?? null) ?: '899' . str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        $data['cost_price']  = $data['cost_price'] ?? 0;
        $data['tax_rate']    = $data['tax_rate'] ?? 0;

        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        }

        // Variants
        foreach ($request->input('variants', []) as $v) {
            if (! empty($v['name'])) {
                $product->variants()->create([
                    'name'             => $v['name'],
                    'price_adjustment' => $v['price_adjustment'] ?? 0,
                    'is_active'        => true,
                ]);
            }
        }

        // Modifier groups
        if ($request->filled('modifier_groups')) {
            $product->modifierGroups()->sync($request->modifier_groups);
        }

        // Auto-create inventory for all branches
        Branch::where('company_id', auth()->user()->company_id)
            ->pluck('id')
            ->each(fn ($branchId) => Inventory::firstOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branchId],
                ['qty' => 0, 'min_qty' => 0]
            ));

        return redirect()->route('app.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load('variants', 'modifierGroups');
        $categories     = Category::where('is_active', true)->orderBy('sort_order')->get();
        $modifierGroups = ModifierGroup::with('options')->get();
        return view('app.products.form', compact('product', 'categories', 'modifierGroups'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'sku'         => 'nullable|string|max:50',
            'barcode'     => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'unit'        => 'required|string|max:20',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'track_stock' => 'boolean',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data['track_stock'] = $request->boolean('track_stock', true);
        $data['is_active']   = $request->boolean('is_active', true);
        $data['cost_price']  = $data['cost_price'] ?? 0;
        $data['tax_rate']    = $data['tax_rate'] ?? 0;

        $product->update($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        } elseif ($request->boolean('remove_image')) {
            $product->clearMediaCollection('images');
        }

        // Sync modifier groups
        $product->modifierGroups()->sync($request->input('modifier_groups', []));

        return redirect()->route('app.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('app.products.index')->with('success', 'Produk dihapus.');
    }

    // Inline variant CRUD (called via AJAX or sub-form)
    public function storeVariant(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'price_adjustment' => 'nullable|numeric',
        ]);
        $variant = $product->variants()->create(['is_active' => true] + $data);
        return response()->json($variant);
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        $variant->delete();
        return response()->json(['ok' => true]);
    }
}
