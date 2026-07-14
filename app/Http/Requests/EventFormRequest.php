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
        return auth()->check() && auth()->user()->role === 'admin';
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
            'tikets.*.stok' => 'required|integer|min:0',
        ];

        if ($this->isMethod('post')) {
            $rules['gambar'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
            $rules['tanggal_waktu'] .= '|after:now';
        } else {
            $rules['gambar'] = 'nullable|image|mimes:jpg,jpeg,png|max:2048';
            
            $event = $this->route('event');
            if ($event && $this->input('tanggal_waktu') && $this->input('tanggal_waktu') !== $event->tanggal_waktu->format('Y-m-d H:i:s') && $this->input('tanggal_waktu') !== $event->tanggal_waktu->format('Y-m-d\TH:i')) {
                $rules['tanggal_waktu'] .= '|after:now';
            }
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul event wajib diisi.',
            'judul.string' => 'Judul event harus berupa teks.',
            'judul.max' => 'Judul event maksimal 255 karakter.',
            'deskripsi.required' => 'Deskripsi event wajib diisi.',
            'lokasi.required' => 'Lokasi event wajib diisi.',
            'lokasi.string' => 'Lokasi event harus berupa teks.',
            'lokasi.max' => 'Lokasi event maksimal 255 karakter.',
            'kategori_id.required' => 'Kategori event wajib diisi.',
            'kategori_id.exists' => 'Kategori event tidak valid.',
            'tanggal_waktu.required' => 'Tanggal dan waktu event wajib diisi.',
            'tanggal_waktu.date' => 'Tanggal dan waktu event harus berupa tanggal yang valid.',
            'tanggal_waktu.after' => 'Tanggal dan waktu event harus setelah waktu sekarang.',
            'gambar.required' => 'Gambar poster event wajib diunggah.',
            'gambar.image' => 'Berkas harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus berupa jpg, jpeg, atau png.',
            'gambar.max' => 'Ukuran gambar maksimal 2048 KB.',
            'tikets.required' => 'Minimal harus ada 1 tiket yang dibuat.',
            'tikets.array' => 'Format tiket harus berupa array.',
            'tikets.min' => 'Minimal harus ada 1 tiket yang dibuat.',
            'tikets.*.tipe.required' => 'Tipe tiket wajib diisi.',
            'tikets.*.tipe.in' => 'Tipe tiket harus reguler atau premium.',
            'tikets.*.harga.required' => 'Harga tiket wajib diisi.',
            'tikets.*.harga.numeric' => 'Harga tiket harus berupa angka.',
            'tikets.*.harga.min' => 'Harga tiket minimal 0.',
            'tikets.*.stok.required' => 'Stok tiket wajib diisi.',
            'tikets.*.stok.integer' => 'Stok tiket harus berupa angka bulat.',
            'tikets.*.stok.min' => 'Stok tiket minimal 0.',
            'tikets.*.id.exists' => 'ID tiket tidak valid.',
        ];
    }
}
