# Etalase Digital Stella (gabungan)

Gabungan dua proyek:

| Bagian | Sumber |
|---|---|
| Tampilan (Bootstrap 5, navbar, kartu galeri, form, halaman kelola siswa) | `etalase_stella` |
| Backend (PDO + prepared statement, middleware role, tabel `users`/`karya`, login email) | `etalase_stella_lkpd` |
| Popup (detail karya & form tambah admin) | `etalase_stella_lkpd` |
| **Baru:** halaman *Kelola User* khusus Super Admin untuk menambah admin | – |

## Instalasi (XAMPP)
1. Salin folder `etalase_stella` ke `htdocs/`.
2. Buka phpMyAdmin → **Import** → pilih `database.sql` (database `db_etalase_stella`).
3. Cek `config/kuis_db.php` (host/user/password MySQL).
4. Buka `http://localhost/etalase_stella/auth/login.php`.

## Akun default (password: `password123`)
| Email | Role |
|---|---|
| superadmin@stella.sch.id | super_admin |
| admin@stella.sch.id | admin |
| siswa@stella.sch.id | siswa |

Segera ganti password akun default setelah instalasi.

## Hak akses
| Fitur | Siswa | Admin | Super Admin |
|---|:-:|:-:|:-:|
| Lihat galeri, tambah karya | ✔ | ✔ | ✔ |
| Edit/hapus karya | milik sendiri | semua | semua |
| Kelola Siswa (`admin/siswa.php`) | – | ✔ | ✔ |
| Kelola User + tambah admin (`admin/users.php`) | – | – | ✔ |

## Catatan perubahan
- Login memakai **email** (backend lkpd), bukan username.
- Karya memakai tabel `karya` dan kolom `nama_karya` serta `link_karya`; kolom lama tidak dipakai.
- Profil siswa disimpan di tabel `student` dan terhubung ke akun `users` melalui `id_user`.
- `reset.php` dihapus (bisa dipakai siapa saja tanpa login untuk mereset password).
- Hapus/reset sekarang lewat POST + token CSRF; upload gambar divalidasi (JPG/PNG/WEBP, maks. 2MB) baik saat tambah maupun edit.
- Reset password siswa oleh admin menjadi `654321` (ubah di `DEFAULT_RESET_PASSWORD`, `middleware/auth_check.php`).
