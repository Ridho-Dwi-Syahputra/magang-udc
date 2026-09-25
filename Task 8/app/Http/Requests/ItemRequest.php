<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'category_id' => [$wajib, 'integer', 'exists:categories,id'],
            'name' => [$wajib, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak ada.',
            'name.required' => 'Nama barang wajib diisi.',
            'stock.min' => 'Stok tidak boleh minus.',
            'price.min' => 'Harga tidak boleh minus.',
        ];
    }
}
