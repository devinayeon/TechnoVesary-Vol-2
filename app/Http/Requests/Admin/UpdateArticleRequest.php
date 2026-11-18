<?php

namespace App\Http\Requests\Admin;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
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
        $targetArticle = $this->route('article');
        $targetArticleId = $targetArticle instanceof Article ? $targetArticle->id : $targetArticle;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Article::class, 'slug')->ignore($targetArticleId),
            ],
            'category_id' => ['required', 'integer', 'exists:'.ArticleCategory::class.',id'],
            'content' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
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
            'remove_photo' => 'Hapus foto thumbnail',
        ];
    }
}
