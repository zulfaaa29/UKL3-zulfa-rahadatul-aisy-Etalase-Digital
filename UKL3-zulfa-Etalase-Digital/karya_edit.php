<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
require_once 'includes/helpers.php';

check_login();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM karya WHERE id = :id");
$stmt->execute([':id' => $id]);
$karya = $stmt->fetch();

if (!$karya) {
    die("Data karya tidak ditemukan.");
}

// Siswa hanya boleh mengedit karya miliknya; admin & super admin boleh semua
if (!is_staff() && $karya['user_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    die("Akses Ditolak: Anda tidak dapat mengedit karya milik siswa lain.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nama_karya  = trim($_POST['nama_karya'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $link_karya  = trim($_POST['link_karya'] ?? '');
    $gambar_nama = $karya['gambar'];
    $gambar_baru  = null;

    if ($nama_karya === '' || $deskripsi === '' || $link_karya === '') {
        $error = "Semua kolom wajib diisi!";
    } elseif (!valid_http_url($link_karya)) {
        $error = "Format link karya tidak valid (gunakan format https://...)!";
    } else {
        // Gambar baru bersifat opsional
        $adaFile = isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE;
        if ($adaFile) {
            $gambar_baru = save_karya_image($_FILES['gambar'], UPLOAD_DIR, $error);
            if ($gambar_baru !== null) {
                $gambar_nama = $gambar_baru;
            }
        }

        if ($error === '') {
            try {
                $upd = $pdo->prepare("UPDATE karya
                                      SET nama_karya = :nama, deskripsi = :desk, gambar = :img, link_karya = :link
                                      WHERE id = :id");
                $upd->execute([
                    ':nama' => $nama_karya,
                    ':desk' => $deskripsi,
                    ':img'  => $gambar_nama,
                    ':link' => $link_karya,
                    ':id'   => $id,
                ]);

                if ($gambar_baru !== null) {
                    delete_karya_image(UPLOAD_DIR, $karya['gambar']);
                }
                set_flash('success', 'Karya berhasil diperbarui.');
                header('Location: index.php');
                exit();
            } catch (PDOException $ex) {
                if ($gambar_baru !== null) {
                    delete_karya_image(UPLOAD_DIR, $gambar_baru);
                }
                $error = "Terjadi kesalahan sistem saat menyimpan perubahan.";
            }
        }
    }

    // Tampilkan kembali input terakhir jika gagal
    $karya['nama_karya'] = $nama_karya;
    $karya['deskripsi']  = $deskripsi;
    $karya['link_karya'] = $link_karya;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karya - Stella</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-form p-4">
                <h3 class="fw-bold mb-4"><i class="bi bi-pencil-square text-warning"></i> Edit Karya</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Karya</label>
                        <input type="text" name="nama_karya" class="form-control" maxlength="150" value="<?= e($karya['nama_karya']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Karya</label>
                        <textarea name="deskripsi" class="form-control" rows="4" required><?= e($karya['deskripsi']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Gambar Saat Ini</label>
                        <img src="uploads/<?= e($karya['gambar']) ?>" class="img-thumbnail preview-img mb-2" alt="Gambar saat ini">
                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/png, image/jpeg, image/webp">
                        <small class="text-muted">* Biarkan kosong jika tidak ingin mengganti gambar (maks. 2MB).</small>
                        <div class="preview-box" id="previewBox">
                            <p class="small text-muted mb-1">Pratinjau Gambar Baru:</p>
                            <img id="imagePreview" src="" alt="Preview Gambar">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Link Karya</label>
                        <input type="url" name="link_karya" class="form-control" maxlength="255" value="<?= e($karya['link_karya']) ?>" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save"></i> Update Karya</button>
                        <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('gambar').addEventListener('change', function () {
    var box = document.getElementById('previewBox');
    var img = document.getElementById('imagePreview');
    var file = this.files[0];
    if (!file) { img.removeAttribute('src'); box.style.display = 'none'; return; }
    var reader = new FileReader();
    reader.addEventListener('load', function () { img.src = reader.result; box.style.display = 'block'; });
    reader.readAsDataURL(file);
});
</script>

</body>
</html>
