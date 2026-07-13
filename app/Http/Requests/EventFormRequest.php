<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'judul' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'deskripsi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'tanggal_waktu' => 'required|date',
            'tikets' => 'required|array|min:1',
            'tikets.*.id' => 'nullable|exists:tikets,id',
            'tikets.*.tipe' => 'required|string|in:reguler,premium',
            'tikets.*.harga' => 'required|numeric|min:0',
            'tikets.*.stok' => 'required|integer|min:1',
        ];

        if ($this->isMethod('post')) {
            $rules['gambar'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            $rules['tanggal_waktu'] .= '|after:now';
        } else {
            $rules['gambar'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        return $rules;
    }
}
