<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StorePickupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'integer', 'distinct', 'exists:waste_categories,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address.required' => 'Alamat penjemputan wajib diisi.',
            'latitude.required' => 'Titik lokasi pada peta wajib dipilih.',
            'longitude.required' => 'Titik lokasi pada peta wajib dipilih.',
            'photo.required' => 'Foto bukti sampah wajib diunggah.',
            'photo.image' => 'Foto bukti sampah harus berupa gambar.',
            'photo.max' => 'Ukuran foto bukti sampah maksimal 4 MB.',
            'categories.required' => 'Pilih minimal satu jenis sampah.',
            'categories.min' => 'Pilih minimal satu jenis sampah.',
            'categories.*.exists' => 'Jenis sampah yang dipilih tidak tersedia.',
            'categories.*.distinct' => 'Jenis sampah tidak boleh dipilih dua kali.',
        ];
    }
}
