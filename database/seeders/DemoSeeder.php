<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $company = Company::updateOrCreate(
                ['slug' => 'demo-warung-kopi'],
                [
                    'name'               => 'Demo Warung Kopi',
                    'phone'              => '081234567890',
                    'currency'           => 'IDR',
                    'timezone'           => 'Asia/Jakarta',
                    'tax_rate'           => 0,
                    'is_active'          => true,
                    'payment_provider'   => 'qris_static',
                    'qris_static_string' => '00020101021126610016ID.CO.SHOPEE.WWW01189360091800219193490208219193490303UMI51440014ID.CO.QRIS.WWW0215ID10243642573360303UMI5204581253033605802ID5918Pisang Banana Boon6006BANTUL61055518362070703A0163048CF2',
                ]
            );

            $branch = Branch::updateOrCreate(
                ['company_id' => $company->id, 'name' => 'Cabang Demo'],
                ['address' => 'Jakarta', 'is_active' => true]
            );

            $user = User::updateOrCreate(
                ['email' => 'demo@ekasir.app'],
                [
                    'name'       => 'Demo Owner',
                    'password'   => 'demo123456',
                    'company_id' => $company->id,
                    'branch_id'  => $branch->id,
                    'is_active'  => true,
                ]
            );

            $user->syncRoles(['owner']);

            $kasir = User::updateOrCreate(
                ['email' => 'kasir@ekasir.app'],
                [
                    'name'       => 'Demo Kasir',
                    'password'   => 'demo123456',
                    'company_id' => $company->id,
                    'branch_id'  => $branch->id,
                    'is_active'  => true,
                ]
            );

            $kasir->syncRoles(['cashier']);

            $cats = [
                ['name' => 'Kopi',     'color' => '#92400e', 'sort_order' => 1],
                ['name' => 'Non-Kopi', 'color' => '#065f46', 'sort_order' => 2],
                ['name' => 'Makanan',  'color' => '#1e3a5f', 'sort_order' => 3],
                ['name' => 'Snack',    'color' => '#6b21a8', 'sort_order' => 4],
            ];
            $catIds = [];
            foreach ($cats as $cat) {
                $c = Category::updateOrCreate(
                    ['company_id' => $company->id, 'name' => $cat['name']],
                    ['color' => $cat['color'], 'sort_order' => $cat['sort_order'], 'is_active' => true]
                );
                $catIds[$cat['name']] = $c->id;
            }

            $products = [
                ['name' => 'Kopi Susu Kekinian', 'price' => 22000, 'cat' => 'Kopi'],
                ['name' => 'Americano',           'price' => 20000, 'cat' => 'Kopi'],
                ['name' => 'Cappuccino',          'price' => 25000, 'cat' => 'Kopi'],
                ['name' => 'Espresso',            'price' => 18000, 'cat' => 'Kopi'],
                ['name' => 'Latte',               'price' => 26000, 'cat' => 'Kopi'],
                ['name' => 'Es Teh Manis',        'price' => 8000,  'cat' => 'Non-Kopi'],
                ['name' => 'Es Jeruk',            'price' => 10000, 'cat' => 'Non-Kopi'],
                ['name' => 'Jus Alpukat',         'price' => 18000, 'cat' => 'Non-Kopi'],
                ['name' => 'Matcha Latte',        'price' => 24000, 'cat' => 'Non-Kopi'],
                ['name' => 'Nasi Goreng',         'price' => 25000, 'cat' => 'Makanan'],
                ['name' => 'Mie Goreng',          'price' => 22000, 'cat' => 'Makanan'],
                ['name' => 'Nasi Ayam Geprek',    'price' => 28000, 'cat' => 'Makanan'],
                ['name' => 'Sandwich',            'price' => 30000, 'cat' => 'Makanan'],
                ['name' => 'Pisang Goreng',       'price' => 12000, 'cat' => 'Snack'],
                ['name' => 'Singkong Goreng',     'price' => 10000, 'cat' => 'Snack'],
                ['name' => 'Kentang Goreng',      'price' => 15000, 'cat' => 'Snack'],
            ];

            foreach ($products as $p) {
                Product::updateOrCreate(
                    ['company_id' => $company->id, 'name' => $p['name']],
                    [
                        'category_id' => $catIds[$p['cat']],
                        'price'       => $p['price'],
                        'is_active'   => true,
                        'track_stock' => false,
                    ]
                );
            }

            foreach (range(1, 6) as $n) {
                Table::updateOrCreate(
                    ['branch_id' => $branch->id, 'name' => 'Meja ' . $n],
                    ['capacity' => 4, 'is_active' => true, 'qr_token' => Str::uuid()]
                );
            }

            if (! $company->subscription || $company->subscription->status === 'expired') {
                $plan = \App\Models\Plan::where('slug', 'pro')->first();
                if ($plan) {
                    (new SubscriptionService())->createTrial($company, $plan);
                }
            }
        });

        $this->command->info('Demo seeded: demo@ekasir.app / demo123456');
    }
}
