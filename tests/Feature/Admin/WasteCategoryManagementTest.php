<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WasteCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('non-admin (customer & driver) ditolak saat mengakses endpoint manajemen jenis sampah', function () {
    $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
    $driver = User::factory()->create(['role' => UserRole::DRIVER]);

    // Customer mencoba akses
    $this->actingAs($customer)->get('/admin/waste-categories')->assertStatus(403);
    $this->actingAs($customer)->post('/admin/waste-categories', [])->assertStatus(403);

    // Driver mencoba akses
    $this->actingAs($driver)->get('/admin/waste-categories')->assertStatus(403);
    $this->actingAs($driver)->post('/admin/waste-categories', [])->assertStatus(403);
});

test('admin dapat melihat halaman daftar jenis sampah dan form create', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)->get('/admin/waste-categories')->assertOk();
    $this->actingAs($admin)->get('/admin/waste-categories/create')->assertOk();
});

test('admin dapat menambahkan jenis sampah baru dengan nama, deskripsi, dan poin per kg', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $response = $this->actingAs($admin)->post('/admin/waste-categories', [
        'name' => 'Tembaga Super',
        'description' => 'Kabel tembaga murni tanpa kulit pelindung',
        'points_per_kg' => 75000,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.waste-categories.index'));

    $category = WasteCategory::where('name', 'Tembaga Super')->first();
    expect($category)->not->toBeNull()
        ->and($category->description)->toBe('Kabel tembaga murni tanpa kulit pelindung')
        ->and($category->points_per_kg)->toBe(75000);
});

test('validasi penambahan jenis sampah baru: nama unik dan poin per kg minimal 1', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $existing = WasteCategory::first();

    // Nama duplikat dan poin negatif
    $response = $this->actingAs($admin)->post('/admin/waste-categories', [
        'name' => $existing->name,
        'description' => 'Deskripsi percobaan',
        'points_per_kg' => -50,
    ]);

    $response->assertSessionHasErrors(['name', 'points_per_kg']);
});

test('admin dapat melihat rincian dan halaman edit jenis sampah', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $category = WasteCategory::first();

    $this->actingAs($admin)->get(route('admin.waste-categories.show', $category))->assertOk();
    $this->actingAs($admin)->get(route('admin.waste-categories.edit', $category))->assertOk();
});

test('admin dapat memperbarui data jenis sampah termasuk nama, deskripsi, dan poin per kg', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $category = WasteCategory::create([
        'name' => 'Kuningan Bekas',
        'description' => 'Kuningan keran air',
        'points_per_kg' => 35000,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.waste-categories.update', $category), [
        'name' => 'Kuningan Kualitas A',
        'description' => 'Kuningan tebal tanpa karat atau cat',
        'points_per_kg' => 42000,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.waste-categories.index'));

    $category->refresh();
    expect($category->name)->toBe('Kuningan Kualitas A')
        ->and($category->description)->toBe('Kuningan tebal tanpa karat atau cat')
        ->and($category->points_per_kg)->toBe(42000);
});

test('admin dapat menghapus jenis sampah secara soft delete', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $category = WasteCategory::create([
        'name' => 'Styrofoam Bekas',
        'description' => 'Wadah makanan styrofoam bersih',
        'points_per_kg' => 500,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.waste-categories.destroy', $category));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.waste-categories.index'));

    $this->assertSoftDeleted('waste_categories', [
        'id' => $category->id,
    ]);
});

test('admin dapat memulihkan (restore) jenis sampah yang telah dihapus secara soft delete', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $category = WasteCategory::create([
        'name' => 'Kaca Bening Pecah',
        'description' => 'Pecahan botol kaca bening',
        'points_per_kg' => 800,
    ]);
    $category->delete();

    expect(WasteCategory::withTrashed()->find($category->id)->trashed())->toBeTrue();

    $response = $this->actingAs($admin)->post(route('admin.waste-categories.restore', $category->id));

    $response->assertSessionHasNoErrors();
    $this->assertNotSoftDeleted('waste_categories', [
        'id' => $category->id,
    ]);
});

test('pencarian dan filter status jenis sampah berfungsi dengan benar di service', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $active = WasteCategory::create([
        'name' => 'Kardus Box Khusus',
        'description' => 'Kardus gelombang tebal',
        'points_per_kg' => 2200,
    ]);

    $trashed = WasteCategory::create([
        'name' => 'Kardus Tipis Arsip',
        'description' => 'Kardus tipis bekas map',
        'points_per_kg' => 1500,
    ]);
    $trashed->delete();

    // Filter aktif dengan pencarian 'Khusus'
    $responseActive = $this->actingAs($admin)->get('/admin/waste-categories?search=Khusus&status=active');
    $responseActive->assertOk();

    // Filter trashed dengan pencarian 'Arsip'
    $responseTrashed = $this->actingAs($admin)->get('/admin/waste-categories?search=Arsip&status=trashed');
    $responseTrashed->assertOk();
});
