<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing(['customer', 'driver', 'admin']);

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'userData' => $user,
            'basemapApiKey' => config('osm.basemap_api_key'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Pemetaan alias dari revalue.sql jika parameter dalam bahasa Indonesia
        if (isset($validated['nomor_telepon']) && ! isset($validated['phone_number'])) {
            $validated['phone_number'] = $validated['nomor_telepon'];
        }
        if (isset($validated['alamat']) && ! isset($validated['address'])) {
            $validated['address'] = $validated['alamat'];
        }

        // Pengolahan berkas foto profil / avatar
        $photoFile = $request->file('photo') ?? $request->file('foto');
        if ($request->boolean('remove_photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = null;
        } elseif ($photoFile) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $path = $photoFile->store('avatars', 'public');
            $validated['photo'] = $path;
        }

        // Toggle ketersediaan Driver jika user ber-role driver
        if ($user->isDriver() && $request->has('is_available') && $user->driver) {
            $user->driver->update([
                'is_available' => $request->boolean('is_available'),
            ]);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
