<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class UserController extends Controller
{
    public function __construct(
        protected UserManagementService $userService
    ) {}

    /**
     * Menampilkan daftar pengguna terpaginasi dengan filter & pencarian
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'role', 'status']);
        $users = $this->userService->getPaginatedUsers($filters, 10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => array_map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ], UserRole::cases()),
        ]);
    }

    /**
     * Menampilkan form penambahan pengguna baru
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => array_map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ], UserRole::cases()),
        ]);
    }

    /**
     * Menyimpan pengguna baru dan menginstansiasi record profil spesifiknya
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan rincian data pengguna
     */
    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load(['admin', 'driver', 'customer']),
        ]);
    }

    /**
     * Menampilkan form edit pengguna
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load(['admin', 'driver', 'customer']),
            'roles' => array_map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ], UserRole::cases()),
        ]);
    }

    /**
     * Memperbarui data pengguna, profil role, dan opsional reset kata sandi
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna secara Soft Delete
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        try {
            $this->userService->deleteUser($user, $request->user()->id);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Pengguna berhasil dinonaktifkan.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mengaktifkan kembali pengguna yang berada di status soft-delete
     */
    public function restore(int $id): RedirectResponse
    {
        $this->userService->restoreUser($id);

        return back()->with('success', 'Akun pengguna berhasil diaktifkan kembali.');
    }
}
