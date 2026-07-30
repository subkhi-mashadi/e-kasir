<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['users', 'tables'])->get();
        return view('app.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('app.branches.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'code'               => 'nullable|string|max:20',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'qris_image'         => 'nullable|image|max:2048',
            'qris_static_string' => 'nullable|string',
            'is_active'          => 'boolean',
        ]);
        $company = auth()->user()->company;
        if ($company && ! $company->canAddBranch()) {
            return back()->withErrors(['name' => 'Batas jumlah cabang paket Anda telah tercapai. Upgrade paket untuk menambah cabang.']);
        }
        $data['company_id'] = auth()->user()->company_id;
        $data['is_active']  = $request->boolean('is_active', true);
        if ($request->hasFile('qris_image')) {
            $data['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        }
        Branch::create($data);
        return redirect()->route('app.branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        return view('app.branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'nullable|string|max:20',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'qris_image' => 'nullable|image|max:2048',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('qris_image')) {
            if ($branch->qris_image) {
                Storage::disk('public')->delete($branch->qris_image);
            }
            $data['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        } else {
            unset($data['qris_image']);
        }
        $branch->update($data);
        return redirect()->route('app.branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('app.branches.index')->with('success', 'Cabang dihapus.');
    }
}
