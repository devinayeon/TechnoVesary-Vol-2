<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class CompletePickupRequest extends FormRequest
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
            'details' => ['required', 'array', 'min:1'],
            'details.*.id' => ['required', 'integer'],
            'details.*.weight' => ['required', 'numeric', 'min:0.1', 'max:9999'],
            'pickup_proof_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
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
            'details.required' => 'Berat setiap jenis sampah wajib diisi.',
            'details.*.weight.required' => 'Berat sampah wajib diisi.',
            'details.*.weight.min' => 'Berat sampah minimal 0,1 kg.',
            'pickup_proof_photo.required' => 'Foto bukti penjemputan wajib diunggah.',
            'pickup_proof_photo.max' => 'Ukuran foto bukti maksimal 4 MB.',
        ];
    }
}
