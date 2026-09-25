<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        // Waktu update, kategori yang lagi diedit harus dikecualikan dari cek unique.
        // Kalau enggak, simpan tanpa ganti nama malah ditolak karena namanya sendiri.
        $unique = Rule::unique('categories', 'name')->ignore($this->route('category'));

        return [
            'name' => [$wajib, 'string', 'max:100', $unique],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori dengan nama itu sudah ada.',
        ];
    }
}
