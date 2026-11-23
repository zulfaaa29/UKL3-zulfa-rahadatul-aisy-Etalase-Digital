<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
require_once 'includes/helpers.php';

check_login();

// Penghapusan hanya lewat POST + token CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM karya WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $karya = $stmt->fetch();

    if ($karya) {
        // Siswa hanya boleh menghapus karya miliknya; admin & super admin boleh semua
        if (!is_staff() && $karya['user_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            die("Akses Ditolak!");
        }

        $del = $pdo->prepare("DELETE FROM karya WHERE id = :id");
        $del->execute([':id' => $id]);
        delete_karya_image(UPLOAD_DIR, $karya['gambar']);
        set_flash('success', 'Karya berhasil dihapus.');
    }
}

header('Location: index.php');
exit();
