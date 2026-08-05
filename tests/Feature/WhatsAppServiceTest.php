<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WhatsAppService $service;
    protected Branch $branch;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->service = new WhatsAppService();

        $this->branch = Branch::create([
            'name' => 'Outlet Wijaya Kusuma',
            'code' => 'WJK',
            'address' => 'Jl. Wijaya Kusuma No. 12',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'name' => 'Kasir Wijaya',
            'is_active' => true,
        ]);
        $this->user->assignRole('Developer');

        $this->customer = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Kos Putri Annisa',
            'phone' => '081987654321',
            'member_code' => 'MBR-001',
            'loyalty_tier' => 'Gold',
            'loyalty_points' => 250,
        ]);
    }

    public function test_phone_number_normalization()
    {
        $this->assertEquals('6281234567890', $this->service->normalizePhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', $this->service->normalizePhoneNumber('+62 812-3456-7890'));
        $this->assertEquals('6281234567890', $this->service->normalizePhoneNumber('6281234567890'));
        $this->assertEquals('', $this->service->normalizePhoneNumber(''));
    }

    public function test_generate_whatsapp_url_uses_rawurlencode_and_normalized_phone()
    {
        $message = "Line 1\nLine 2 with spaces & special symbols";
        $url = $this->service->generateWhatsAppUrl('081234567890', $message);

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString('%0A', $url);
        $this->assertStringContainsString('%20', $url);
        $this->assertStringNotContainsString('+', $url);
    }

    public function test_receipt_message_formatting()
    {
        $serviceItem = Service::create([
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Lipat Premium',
            'type' => 'kilogram',
            'unit' => 'kg',
            'base_price' => 10000,
        ]);

        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->user->id,
            'order_number' => 'ORD-WJK-20260805-0001',
            'order_type' => 'outlet',
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'tax_amount' => 0,
            'total' => 45000,
            'paid_amount' => 50000,
            'change_amount' => 5000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'received',
            'estimated_done_at' => now()->addDays(1),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $serviceItem->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'subtotal' => 50000,
            'unit' => 'kg',
        ]);

        $message = $this->service->generateReceiptMessage($order);

        $this->assertStringContainsString('*[ISTANA LAUNDRY - NOTA TRANSAKSI]*', $message);
        $this->assertStringContainsString('Outlet Wijaya Kusuma', $message);
        $this->assertStringContainsString('ORD-WJK-20260805-0001', $message);
        $this->assertStringContainsString('Kos Putri Annisa', $message);
        $this->assertStringContainsString('Cuci Lipat Premium', $message);
        $this->assertStringContainsString('Rp 45.000', $message);
        $this->assertStringContainsString('[LUNAS]', $message);
        $this->assertStringContainsString('track?order_number=ORD-WJK-20260805-0001', $message);
    }

    public function test_ready_notification_message_formatting()
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->user->id,
            'order_number' => 'ORD-WJK-20260805-0002',
            'subtotal' => 120000,
            'discount_amount' => 0,
            'total' => 120000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'status' => 'ready',
        ]);

        $message = $this->service->generateReadyNotificationMessage($order);

        $this->assertStringContainsString('*[ISTANA LAUNDRY - NOTIFIKASI CUCIAN SELESAI]*', $message);
        $this->assertStringContainsString('Kos Putri Annisa', $message);
        $this->assertStringContainsString('ORD-WJK-20260805-0002', $message);
        $this->assertStringContainsString('SELESAI DIPROSES & SIAP DIAMBIL!', $message);
        $this->assertStringContainsString('BELUM LUNAS', $message);
        $this->assertStringContainsString('Rp 120.000', $message);
    }

    public function test_purchase_order_message_formatting()
    {
        $supplier = Supplier::create([
            'name' => 'PT Deterjen Sentosa',
            'contact_person' => 'Budi',
            'phone' => '081233445566',
            'email' => 'budi@deterjen.com',
            'is_active' => true,
        ]);

        $item = InventoryItem::create([
            'branch_id' => $this->branch->id,
            'name' => 'Deterjen Laundry 5L',
            'sku' => 'DET-5L',
            'unit' => 'Jerigen',
            'stock_quantity' => 10,
            'min_stock_alert' => 2,
            'unit_cost' => 50000,
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-WJK-20260805-0001',
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'status' => 'sent',
            'subtotal' => 100000,
            'tax_amount' => 11000,
            'total' => 111000,
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);

        PurchaseOrderItem::create([
            'po_id' => $po->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'unit_cost' => 50000,
            'subtotal' => 100000,
        ]);

        $message = $this->service->generatePurchaseOrderMessage($po);

        $this->assertStringContainsString('*[ISTANA LAUNDRY - OFFICIAL PURCHASE ORDER]*', $message);
        $this->assertStringContainsString('PT Deterjen Sentosa', $message);
        $this->assertStringContainsString('PO-WJK-20260805-0001', $message);
        $this->assertStringContainsString('Deterjen Laundry 5L', $message);
        $this->assertStringContainsString('Rp 111.000', $message);
    }

    public function test_customer_greeting_message_formatting()
    {
        $message = $this->service->generateCustomerGreetingMessage($this->customer, $this->branch);

        $this->assertStringContainsString('*[ISTANA LAUNDRY - INFORMASI MEMBER]*', $message);
        $this->assertStringContainsString('Kos Putri Annisa', $message);
        $this->assertStringContainsString('250 Poin', $message);
    }

    public function test_meta_interactive_payload_generation()
    {
        $payload = $this->service->generateMetaInteractivePayload(
            '081987654321',
            'Header Title',
            'Body text content',
            'Footer',
            [
                ['id' => 'btn_1', 'title' => 'Lacak Order'],
                ['id' => 'btn_2', 'title' => 'Struk Digital'],
            ]
        );

        $this->assertEquals('whatsapp', $payload['messaging_product']);
        $this->assertEquals('6281987654321', $payload['to']);
        $this->assertEquals('interactive', $payload['type']);
        $this->assertEquals('button', $payload['interactive']['type']);
        $this->assertEquals('Header Title', $payload['interactive']['header']['text']);
        $this->assertCount(2, $payload['interactive']['action']['buttons']);
        $this->assertEquals('Lacak Order', $payload['interactive']['action']['buttons'][0]['reply']['title']);
    }

    public function test_invoice_whatsapp_redirect_routes()
    {
        $this->user->assignRole('Owner');

        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->user->id,
            'order_number' => 'ORD-WJK-20260805-0003',
            'subtotal' => 20000,
            'total' => 20000,
            'paid_amount' => 20000,
            'change_amount' => 0,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->get(route('invoices.whatsapp', $order->id));

        $response->assertRedirect();
        $this->assertStringContainsString('https://wa.me/6281987654321?text=', $response->getTargetUrl());

        $responseReady = $this->actingAs($this->user)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->get(route('invoices.ready-whatsapp', $order->id));

        $responseReady->assertRedirect();
        $this->assertStringContainsString('https://wa.me/6281987654321?text=', $responseReady->getTargetUrl());
    }
}
