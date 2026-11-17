<?php

namespace App\Http\Requests\Admin;

use App\Models\WasteCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreWasteCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:'.WasteCategory::class.',name'],
            'description' => ['nullable', 'string'],
            'points_per_kg' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama jenis sampah',
            'description' => 'Deskripsi jenis sampah',
            'points_per_kg' => 'Nilai poin per kilogram',
        ];
    }
}
