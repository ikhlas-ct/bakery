<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Bahasa Indonesia
    |--------------------------------------------------------------------------
    |
    | Berikut adalah terjemahan untuk pesan validasi default Laravel.
    | Beberapa aturan mungkin memiliki beberapa versi seperti ukuran (size).
    | Jangan ragu untuk menyesuaikan pesan ini sesuai kebutuhan aplikasi Anda.
    |
    */

    'accepted'             => 'Isian :attribute harus diterima.',
    'active_url'           => 'Isian :attribute bukan URL yang valid.',
    'after'                => 'Isian :attribute harus tanggal setelah :date.',
    'after_or_equal'       => 'Isian :attribute harus tanggal setelah atau sama dengan :date.',
    'alpha'                => 'Isian :attribute hanya boleh berisi huruf.',
    'alpha_dash'           => 'Isian :attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'            => 'Isian :attribute hanya boleh berisi huruf dan angka.',
    'array'                => 'Isian :attribute harus berupa array.',
    'before'               => 'Isian :attribute harus tanggal sebelum :date.',
    'before_or_equal'      => 'Isian :attribute harus tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'numeric' => 'Isian :attribute harus antara :min dan :max.',
        'file'    => 'Isian :attribute harus antara :min dan :max kilobita.',
        'string'  => 'Isian :attribute harus antara :min dan :max karakter.',
        'array'   => 'Isian :attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean'              => 'Isian :attribute harus berupa true atau false.',
    'confirmed'            => 'Konfirmasi :attribute tidak cocok.',
    'date'                 => 'Isian :attribute bukan tanggal yang valid.',
    'date_equals'          => 'Isian :attribute harus tanggal yang sama dengan :date.',
    'date_format'          => 'Isian :attribute tidak sesuai dengan format :format.',
    'different'            => 'Isian :attribute dan :other harus berbeda.',
    'digits'               => 'Isian :attribute harus :digits digit.',
    'digits_between'       => 'Isian :attribute harus antara :min dan :max digit.',
    'dimensions'           => 'Isian :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct'             => 'Isian :attribute memiliki nilai yang duplikat.',
    'email'                => 'Isian :attribute harus alamat email yang valid.',
    'ends_with'            => 'Isian :attribute harus diakhiri dengan salah satu dari: :values.',
    'exists'               => 'Isian :attribute yang dipilih tidak valid.',
    'file'                 => 'Isian :attribute harus berupa file.',
    'filled'               => 'Isian :attribute harus memiliki nilai.',
    'gt'                   => [
        'numeric' => 'Isian :attribute harus lebih besar dari :value.',
        'file'    => 'Isian :attribute harus lebih besar dari :value kilobita.',
        'string'  => 'Isian :attribute harus lebih besar dari :value karakter.',
        'array'   => 'Isian :attribute harus memiliki lebih dari :value item.',
    ],
    'gte'                  => [
        'numeric' => 'Isian :attribute harus lebih besar dari atau sama dengan :value.',
        'file'    => 'Isian :attribute harus lebih besar dari atau sama dengan :value kilobita.',
        'string'  => 'Isian :attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array'   => 'Isian :attribute harus memiliki :value item atau lebih.',
    ],
    'image'                => 'Isian :attribute harus berupa gambar.',
    'in'                   => 'Isian :attribute yang dipilih tidak valid.',
    'in_array'             => 'Isian :attribute tidak ada dalam :other.',
    'integer'              => 'Isian :attribute harus berupa bilangan bulat.',
    'ip'                   => 'Isian :attribute harus alamat IP yang valid.',
    'ipv4'                 => 'Isian :attribute harus alamat IPv4 yang valid.',
    'ipv6'                 => 'Isian :attribute harus alamat IPv6 yang valid.',
    'json'                 => 'Isian :attribute harus berupa string JSON yang valid.',
    'lt'                   => [
        'numeric' => 'Isian :attribute harus lebih kecil dari :value.',
        'file'    => 'Isian :attribute harus lebih kecil dari :value kilobita.',
        'string'  => 'Isian :attribute harus lebih kecil dari :value karakter.',
        'array'   => 'Isian :attribute harus memiliki kurang dari :value item.',
    ],
    'lte'                  => [
        'numeric' => 'Isian :attribute harus lebih kecil dari atau sama dengan :value.',
        'file'    => 'Isian :attribute harus lebih kecil dari atau sama dengan :value kilobita.',
        'string'  => 'Isian :attribute harus lebih kecil dari atau sama dengan :value karakter.',
        'array'   => 'Isian :attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'max'                  => [
        'numeric' => 'Isian :attribute tidak boleh lebih besar dari :max.',
        'file'    => 'Isian :attribute tidak boleh lebih besar dari :max kilobita.',
        'string'  => 'Isian :attribute tidak boleh lebih besar dari :max karakter.',
        'array'   => 'Isian :attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes'                => 'Isian :attribute harus berupa file berjenis: :values.',
    'mimetypes'            => 'Isian :attribute harus berupa file berjenis: :values.',
    'min'                  => [
        'numeric' => 'Isian :attribute harus minimal :min.',
        'file'    => 'Isian :attribute harus minimal :min kilobita.',
        'string'  => 'Isian :attribute harus minimal :min karakter.',
        'array'   => 'Isian :attribute harus memiliki minimal :min item.',
    ],
    'not_in'               => 'Isian :attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format isian :attribute tidak valid.',
    'numeric'              => 'Isian :attribute harus berupa angka.',
    'password'             => 'Password salah.',
    'present'              => 'Isian :attribute harus ada.',
    'regex'                => 'Format isian :attribute tidak valid.',
    'required'             => 'Kolom :attribute wajib diisi.',
    'required_if'          => 'Kolom :attribute wajib diisi ketika :other adalah :value.',
    'required_unless'      => 'Kolom :attribute wajib diisi kecuali :other ada dalam :values.',
    'required_with'        => 'Kolom :attribute wajib diisi ketika :values tersedia.',
    'required_with_all'    => 'Kolom :attribute wajib diisi ketika :values tersedia.',
    'required_without'     => 'Kolom :attribute wajib diisi ketika :values tidak tersedia.',
    'required_without_all' => 'Kolom :attribute wajib diisi ketika tidak ada :values yang tersedia.',
    'same'                 => 'Isian :attribute dan :other harus sama.',
    'size'                 => [
        'numeric' => 'Isian :attribute harus berukuran :size.',
        'file'    => 'Isian :attribute harus berukuran :size kilobita.',
        'string'  => 'Isian :attribute harus berukuran :size karakter.',
        'array'   => 'Isian :attribute harus mengandung :size item.',
    ],
    'starts_with'          => 'Isian :attribute harus diawali dengan salah satu dari: :values.',
    'string'               => 'Isian :attribute harus berupa string.',
    'timezone'             => 'Isian :attribute harus zona waktu yang valid.',
    'unique'               => 'Isian :attribute sudah ada sebelumnya.',
    'uploaded'             => 'Isian :attribute gagal diunggah.',
    'url'                  => 'Format isian :attribute tidak valid.',
    'uuid'                 => 'Isian :attribute harus UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Baris berikut digunakan untuk menukar placeholder ":attribute" dengan
    | teks yang lebih mudah dimengerti oleh pengguna seperti "Alamat Email"
    | daripada "email". Ini membantu membuat pesan kita lebih ekspresif.
    |
    */

    'attributes' => [
        'name'                  => 'nama',
        'username'              => 'username',
        'email'                 => 'alamat email',
        'first_name'            => 'nama depan',
        'last_name'             => 'nama belakang',
        'password'              => 'password',
        'password_confirmation' => 'konfirmasi password',
        'city'                  => 'kota',
        'country'               => 'negara',
        'address'               => 'alamat',
        'phone'                 => 'telepon',
        'mobile'                => 'handphone',
        'age'                   => 'umur',
        'sex'                   => 'jenis kelamin',
        'gender'                => 'jenis kelamin',
        'day'                   => 'hari',
        'month'                 => 'bulan',
        'year'                  => 'tahun',
        'hour'                  => 'jam',
        'minute'                => 'menit',
        'second'                => 'detik',
        'title'                 => 'judul',
        'content'               => 'konten',
        'description'           => 'deskripsi',
        'excerpt'               => 'kutipan',
        'date'                  => 'tanggal',
        'time'                  => 'waktu',
        'available'             => 'tersedia',
        'size'                  => 'ukuran',
        'current_password'      => 'password lama',
        'new_password'          => 'password baru',
        'nik'                   => 'NIK',
        'kk'                    => 'Nomor KK',
        'no_hp'                 => 'Nomor HP',
        'foto_profil'           => 'Foto Profil',
        'scan_ktp'              => 'Scan KTP',
        'scan_kk'               => 'Scan KK',
    ],
];
