@extends('layouts.app')
@section('title', isset($branch) ? 'Edit Cabang' : 'Tambah Cabang')
@section('page-title', isset($branch) ? 'Edit Cabang' : 'Tambah Cabang')

@section('content')

<div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-800 mb-5">
        {{ isset($branch) ? 'Edit Cabang' : 'Tambah Cabang Baru' }}
    </h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($branch) ? route('app.branches.update', $branch) : route('app.branches.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if (isset($branch))
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">Nama Cabang <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $branch->name ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: Cabang Utama">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="code">Kode Cabang</label>
            <input type="text" id="code" name="code"
                   value="{{ old('code', $branch->code ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: BRN-01">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="phone">Nomor Telepon</label>
            <input type="text" id="phone" name="phone"
                   value="{{ old('phone', $branch->phone ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: 021-12345678">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="address">Alamat</label>
            <textarea id="address" name="address" rows="3"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 resize-none"
                      placeholder="Alamat lengkap cabang">{{ old('address', $branch->address ?? '') }}</textarea>
        </div>

        {{-- QRIS Static String --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="qris_static_string">String QRIS Statis (opsional)</label>
            <p class="text-xs text-slate-400 mb-2">Paste string QRIS dari mesin EDC/merchant app. Sistem akan otomatis mengubahnya menjadi QRIS dinamis per transaksi.</p>
            <textarea id="qris_static_string" name="qris_static_string" rows="3"
                      placeholder="00020101021226..."
                      class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono outline-none focus:border-amber-500 resize-none">{{ old('qris_static_string', $branch->qris_static_string ?? '') }}</textarea>
        </div>

        {{-- QRIS Image --}}
        <div x-data="{preview: '{{ isset($branch) && $branch->qris_image ? Storage::url($branch->qris_image) : '' }}',
                       onFile(e){ const f=e.target.files[0]; if(f) this.preview=URL.createObjectURL(f); }}">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar QRIS (opsional)</label>
            <p class="text-xs text-slate-400 mb-2">Upload gambar QRIS statis untuk ditampilkan ke pelanggan saat bayar via QR ordering.</p>
            <template x-if="preview">
                <div class="mb-2">
                    <img :src="preview" alt="QRIS Preview" class="w-40 h-40 object-contain border border-slate-200 rounded-xl bg-white p-2">
                </div>
            </template>
            <input type="file" name="qris_image" accept="image/*" @change="onFile($event)"
                   class="w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="receipt_paper_width">Ukuran Kertas Printer Struk</label>
            <select id="receipt_paper_width" name="receipt_paper_width"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500">
                @php $paperWidth = old('receipt_paper_width', $branch->receipt_paper_width ?? 80); @endphp
                <option value="58" {{ (int) $paperWidth === 58 ? 'selected' : '' }}>58mm (thermal kecil)</option>
                <option value="80" {{ (int) $paperWidth === 80 ? 'selected' : '' }}>80mm (standar)</option>
            </select>
            <p class="text-xs text-slate-400 mt-1.5">Sesuaikan dengan ukuran kertas printer thermal cabang ini, biar struk gak melebar/kepotong.</p>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 accent-amber-500 cursor-pointer">
            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Aktif</label>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                {{ isset($branch) ? 'Simpan Perubahan' : 'Tambah Cabang' }}
            </button>
            <a href="{{ route('app.branches.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection
