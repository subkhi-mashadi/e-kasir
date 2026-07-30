<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function payment()
    {
        abort_unless(auth()->user()->hasRole('owner') && ! session('is_demo'), 403);

        $company = auth()->user()->company;
        return view('app.settings.payment', compact('company'));
    }

    public function updatePayment(Request $request)
    {
        abort_unless(auth()->user()->hasRole('owner') && ! session('is_demo'), 403);

        $data = $request->validate([
            'midtrans_server_key'    => 'nullable|string|max:500',
            'midtrans_client_key'    => 'nullable|string|max:500',
            'midtrans_is_production' => 'boolean',
            'xendit_secret_key'      => 'nullable|string|max:500',
            'payment_provider'       => 'required|in:midtrans,xendit,qris_static',
            'qris_static_string'     => 'nullable|string',
        ]);

        $data['midtrans_is_production'] = $request->boolean('midtrans_is_production');

        auth()->user()->company->update($data);

        return back()->with('success', 'Konfigurasi pembayaran berhasil disimpan.');
    }
}
