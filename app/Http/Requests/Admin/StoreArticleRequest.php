<?php

namespace App\Http\Requests\Admin;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:'.Article::class.',slug'],
            'category_id' => ['required', 'integer', 'exists:'.ArticleCategory::class.',id'],
            'content' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
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
            'title' => 'Judul artikel',
            'slug' => 'Slug URL artikel',
            'category_id' => 'Kategori artikel',
            'content' => 'Konten artikel',
            'photo' => 'Foto thumbnail artikel',
        ];
    }
}
