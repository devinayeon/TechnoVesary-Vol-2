<?php

namespace App\Http\Requests\Admin;

use App\Models\ArticleCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleCategoryRequest extends FormRequest
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
        $targetCategory = $this->route('article_category');
        $targetCategoryId = $targetCategory instanceof ArticleCategory ? $targetCategory->id : $targetCategory;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(ArticleCategory::class, 'name')->ignore($targetCategoryId),
            ],
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
            'name' => 'Nama kategori artikel',
        ];
    }
}
