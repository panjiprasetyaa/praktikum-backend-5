<?php

use App\Models\User;
use Modules\Products\Models\Product;
use Modules\Products\Exceptions\InsufficientStockException;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('1. guest cannot access products page and is redirected to login', function () {
    $response = $this->get('/products');
    $response->assertRedirect('/login');
});

test('2. authenticated user can view products list page', function () {
    $response = $this->actingAs($this->user)->get('/products');
    $response->assertStatus(200);
    $response->assertSee('Daftar Produk');
});

test('3. validation error when storing product with empty inputs', function () {
    $response = $this->actingAs($this->user)->post('/products', []);

    $response->assertSessionHasErrors(['name', 'slug', 'price', 'stock']);
    $response->assertSessionHasErrors([
        'name' => 'Nama produk wajib diisi.',
        'slug' => 'Slug produk wajib diisi.',
        'price' => 'Harga produk wajib diisi.',
        'stock' => 'Stok wajib diisi.',
    ]);
});

test('4. validation error when price or stock is negative', function () {
    $response = $this->actingAs($this->user)->post('/products', [
        'name' => 'Produk A',
        'slug' => 'produk-a',
        'price' => -1000,
        'stock' => -5,
    ]);

    $response->assertSessionHasErrors(['price', 'stock']);
    $response->assertSessionHasErrors([
        'price' => 'Harga tidak boleh negatif.',
        'stock' => 'Stok tidak boleh negatif.',
    ]);
});

test('5. validation error when slug is already taken (unique check)', function () {
    Product::create([
        'name' => 'Produk Lama',
        'slug' => 'produk-unik',
        'price' => 50000,
        'stock' => 10,
    ]);

    $response = $this->actingAs($this->user)->post('/products', [
        'name' => 'Produk Baru',
        'slug' => 'produk-unik',
        'price' => 75000,
        'stock' => 20,
    ]);

    $response->assertSessionHasErrors(['slug']);
    $response->assertSessionHasErrors([
        'slug' => 'Slug produk sudah digunakan.',
    ]);
});

test('6. product successfully stored when data is valid', function () {
    $response = $this->actingAs($this->user)->post('/products', [
        'name' => 'Laptop Gaming',
        'slug' => 'laptop-gaming',
        'price' => 15000000,
        'stock' => 5,
        'description' => 'Laptop spesifikasi tinggi.',
    ]);

    $response->assertRedirect('/products');
    $response->assertSessionHas('success', 'Produk berhasil ditambahkan.');
    $this->assertDatabaseHas('products', [
        'slug' => 'laptop-gaming',
        'stock' => 5,
    ]);
});

test('7. product update ignores own slug on unique validation', function () {
    $product = Product::create([
        'name' => 'Mouse Wireless',
        'slug' => 'mouse-wireless',
        'price' => 150000,
        'stock' => 12,
    ]);

    $response = $this->actingAs($this->user)->put("/products/{$product->id}", [
        'name' => 'Mouse Wireless Pro',
        'slug' => 'mouse-wireless',
        'price' => 175000,
        'stock' => 10,
    ]);

    $response->assertRedirect('/products');
    $response->assertSessionHas('success', 'Produk berhasil diperbarui.');
    expect($product->fresh()->name)->toBe('Mouse Wireless Pro');
});

test('8. InsufficientStockException is thrown and caught when reducing more than available stock', function () {
    $product = Product::create([
        'name' => 'Keyboard Mechanical',
        'slug' => 'keyboard-mechanical',
        'price' => 500000,
        'stock' => 3,
    ]);

    $controller = new \Modules\Products\Controllers\ProductController();

    try {
        $controller->reduceStock($product, 10);
        $this->fail('Expected InsufficientStockException was not thrown.');
    } catch (InsufficientStockException $e) {
        expect($e->getProductName())->toBe('Keyboard Mechanical');
        expect($e->getAvailable())->toBe(3);
        expect($e->getRequested())->toBe(10);
        expect($e->getMessage())->toContain("Stok produk 'Keyboard Mechanical' tidak mencukupi");
    }
});

test('9. InsufficientStockException renders JSON response correctly when expectsJson', function () {
    $exception = new InsufficientStockException('Monitor 24 inch', 2, 5);
    $request = \Illuminate\Http\Request::create('/api/reduce-stock', 'POST');
    $request->headers->set('Accept', 'application/json');

    $response = $exception->render($request);

    expect($response->status())->toBe(422);
    $data = json_decode($response->getContent(), true);
    expect($data['error'])->toBe('insufficient_stock');
    expect($data['details']['available'])->toBe(2);
    expect($data['details']['requested'])->toBe(5);
});
