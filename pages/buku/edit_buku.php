<?php
require_once '../../config/koneksi.php';
check_login('admin');

$role = $_SESSION['role'];
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$judul = $pengarang = $penerbit = $tahun_terbit = $genre = $stok = $gambar = '';
$errors = [];

if ($book_id <= 0) {
    header("Location: list_buku.php?error=ID buku tidak valid.");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM buku WHERE id = ?";
    if ($stmt = mysqli_prepare($koneksi, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $book_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($book = mysqli_fetch_assoc($result)) {
                $judul = $book['judul'];
                $pengarang = $book['pengarang'];
                $penerbit = $book['penerbit'];
                $tahun_terbit = $book['tahun_terbit'];
                $genre = $book['genre'];
                $stok = $book['stok'];
                $gambar = $book['gambar'] ?? '';
            } else {
                header("Location: list_buku.php?error=Buku tidak ditemukan.");
                exit();
            }
        } else {
            header("Location: list_buku.php?error=Gagal mengambil data buku.");
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        header("Location: list_buku.php?error=" . mysqli_error($koneksi));
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = trim($_POST['tahun_terbit']);
    $genre = trim($_POST['genre']);
    $stok = trim($_POST['stok']);
    $current_book_id = (int)$_POST['book_id'];
    $current_gambar = $_POST['current_gambar'] ?? '';

    if ($current_book_id !== $book_id) {
        $errors[] = "ID buku tidak cocok.";
    }

    if (empty($judul)) $errors[] = "Judul wajib diisi.";
    if (empty($pengarang)) $errors[] = "Pengarang wajib diisi.";
    if (empty($penerbit)) $errors[] = "Penerbit wajib diisi.";
    if (empty($tahun_terbit)) {
        $errors[] = "Tahun terbit wajib diisi.";
    } elseif (!preg_match('/^\d{4}$/', $tahun_terbit)) {
        $errors[] = "Format tahun terbit harus 4 digit angka (YYYY).";
    }
    if (empty($genre)) $errors[] = "Genre wajib diisi.";
    if ($stok === '') {
        $errors[] = "Stok wajib diisi.";
    } elseif (!filter_var($stok, FILTER_VALIDATE_INT) || $stok < 0) {
        $errors[] = "Stok harus berupa angka non-negatif.";
    }
      // Handle image upload or deletion
    $gambar = $current_gambar;
    
    // Check if delete image option is selected
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        // Delete old image if exists and not the default image
        if (!empty($current_gambar)) {
            $upload_dir = '../../assets/book_images/';
            $old_image_path = $upload_dir . $current_gambar;
            if (file_exists($old_image_path) && $current_gambar !== 'contoh.png') {
                @unlink($old_image_path);
            }
        }
        // Set gambar to null to use the default image
        $gambar = null;
    } 
    // Check if a new image is uploaded
    else if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $_FILES['gambar']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "File harus berformat JPG, JPEG, atau PNG.";
        } else {
            $file_size = $_FILES['gambar']['size'];
            if ($file_size > 2097152) { // 2MB
                $errors[] = "Ukuran file tidak boleh lebih dari 2MB.";
            } else {
                // Generate unique filename to avoid overwriting
                $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $file_name = 'book_' . uniqid() . '_' . time() . '.' . $file_extension;
                $upload_dir = '../../assets/book_images/';
                
                // Ensure directory exists
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $upload_path = $upload_dir . $file_name;
                
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                    $errors[] = "Gagal mengupload gambar.";
                } else {
                    // Delete old image if exists
                    if (!empty($current_gambar)) {
                        $old_image_path = $upload_dir . $current_gambar;
                        if (file_exists($old_image_path) && $current_gambar !== 'contoh.png') {
                            @unlink($old_image_path);
                        }
                    }
                    $gambar = $file_name;
                }
            }
        }    }
    
    if (empty($errors)) {
        // If user deleted the image, we need to update with NULL value
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            $sql = "UPDATE buku SET judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, genre = ?, stok = ?, gambar = NULL WHERE id = ?";
            if ($stmt = mysqli_prepare($koneksi, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssssssi", $judul, $pengarang, $penerbit, $tahun_terbit, $genre, $stok, $book_id);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    mysqli_close($koneksi);
                    header("Location: list_buku.php?success=Buku berhasil diperbarui.");
                    exit();
                } else {
                    $errors[] = "Gagal memperbarui buku: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Gagal menyiapkan statement: " . mysqli_error($koneksi);
            }
        } else {
            $sql = "UPDATE buku SET judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, genre = ?, stok = ?, gambar = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($koneksi, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssssi", $judul, $pengarang, $penerbit, $tahun_terbit, $genre, $stok, $gambar, $book_id);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    mysqli_close($koneksi);
                    header("Location: list_buku.php?success=Buku berhasil diperbarui.");
                    exit();
                } else {
                    $errors[] = "Gagal memperbarui buku: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Gagal menyiapkan statement: " . mysqli_error($koneksi);
            }
        }
    }
    mysqli_close($koneksi);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan Muflih</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
     <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            min-height: 100vh;
            padding: 15px;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background-color: #495057;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .img-preview {
            max-height: 200px;
            max-width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <h4 class="text-center mb-4">Perpus Muflih</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../../dashboard.php"><i class="bi bi-house-door-fill me-2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="list_buku.php"><i class="bi bi-book-fill me-2"></i> Daftar Buku</a>
            </li>
            <?php if ($role === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="tambah_buku.php"><i class="bi bi-plus-circle-fill me-2"></i> Tambah Buku</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../user/list_user.php"><i class="bi bi-people-fill me-2"></i> Manajemen User</a>
            </li>
             <li class="nav-item">
                <a class="nav-link" href="../user/tambah_user.php"><i class="bi bi-person-plus-fill me-2"></i> Tambah User</a>
            </li>
            <?php endif; ?>
             <li class="nav-item mt-auto">
                <a class="nav-link" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <div class="content">
        <div class="container-fluid">
            <h2>Edit Buku (ID: <?php echo sanitize($book_id); ?>)</h2>
            <hr>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $book_id; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="book_id" value="<?php echo sanitize($book_id); ?>">
                <input type="hidden" name="current_gambar" value="<?php echo sanitize($gambar); ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control" id="judul" name="judul" value="<?php echo sanitize($judul); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="pengarang" class="form-label">Pengarang</label>
                            <input type="text" class="form-control" id="pengarang" name="pengarang" value="<?php echo sanitize($pengarang); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="penerbit" class="form-label">Penerbit</label>
                            <input type="text" class="form-control" id="penerbit" name="penerbit" value="<?php echo sanitize($penerbit); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" placeholder="YYYY" pattern="\d{4}" value="<?php echo sanitize($tahun_terbit); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="genre" class="form-label">Genre</label>
                            <input type="text" class="form-control" id="genre" name="genre" value="<?php echo sanitize($genre); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="stok" name="stok" min="0" value="<?php echo sanitize($stok); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Gambar Sampul</h5>
                            </div>
                            <div class="card-body">                                <div class="mb-3 text-center">
                                    <img id="coverPreview" src="<?php echo !empty($gambar) ? '../../assets/book_images/'.$gambar : '../../assets/book_images/contoh.png'; ?>" class="img-fluid img-preview rounded shadow-sm" alt="Preview Sampul">
                                </div>
                                <div class="mb-3">
                                    <label for="gambar" class="form-label">Ubah Gambar</label>
                                    <input type="file" class="form-control" id="gambar" name="gambar" accept=".jpg,.jpeg,.png">
                                    <div class="form-text">Format: JPG, JPEG, PNG. Maks 2MB.</div>
                                </div>
                                <?php if(!empty($gambar)): ?>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="delete_image" name="delete_image" value="1">
                                    <label class="form-check-label" for="delete_image">Hapus gambar saat ini</label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                    <a href="list_buku.php" class="btn btn-secondary"><i class="bi bi-x-lg me-2"></i>Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('gambar');
            const previewImage = document.getElementById('coverPreview');
            const deleteCheckbox = document.getElementById('delete_image');
            const currentImagePath = '<?php echo !empty($gambar) ? "../../assets/book_images/{$gambar}" : "../../assets/book_images/contoh.png"; ?>';
            const defaultImagePath = '../../assets/book_images/contoh.png';
            
            // Update image preview when file input changes
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        // Uncheck delete checkbox if user selected a new image
                        if(deleteCheckbox) {
                            deleteCheckbox.checked = false;
                        }
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    previewImage.src = currentImagePath;
                }
            });
            
            // Handle delete checkbox 
            if(deleteCheckbox) {
                deleteCheckbox.addEventListener('change', function() {
                    if(this.checked) {
                        // Show default image when delete is checked
                        previewImage.src = defaultImagePath;
                        // Clear the file input
                        imageInput.value = '';
                    } else {
                        // Restore current image if unchecked
                        previewImage.src = currentImagePath;
                    }
                });
            }
        });
    </script>
</body>
</html>
