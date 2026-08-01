<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ExpirePendingQrOrders extends Command
{
    protected $signature = 'orders:expire-pending';

    protected $description = 'Mark QR orders still waiting for kasir confirmation past 3 minutes as failed';

    public function handle(): void
    {
        $count = Order::where('status', 'open')
            ->where('source', 'qr')
            ->where('created_at', '<=', now()->subMinutes(3))
            ->update([
                'status'           => 'failed',
                'rejection_reason' => 'Kadaluarsa — kasir tidak merespon dalam 3 menit',
            ]);

        if ($count > 0) {
            $this->info("Expired {$count} pending order(s).");
        }
    }
}
