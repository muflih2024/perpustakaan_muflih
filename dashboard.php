<?php
// Set secure session parameters if running in Vercel
if (getenv('VERCEL') === '1' && session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_secure', 'On');
    ini_set('session.cookie_httponly', 'On');
    ini_set('session.cookie_samesite', 'None');
}

require_once 'config/koneksi.php';

// Pada Vercel, kita buat konten demo
if (is_vercel_env()) {
    // Kita skip check_login untuk demo di Vercel
    
    // Demo data
    $username = "Demo User";
    $role = "admin";
    $user_id = 1;
    
    // Demo messages
    $error_message = isset($_GET['error']) ? sanitize($_GET['error']) : '';
    $success_message = isset($_GET['success']) ? sanitize($_GET['success']) : '';
    
    // Skip database query - we'll use demo data
    $limit = 12;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page); 
    $offset = 0;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
} else {
    // Normal flow for local environment
    check_login();
    
    $username = sanitize($_SESSION['username']);
    $role = sanitize($_SESSION['role']);
    $user_id = $_SESSION['user_id']; // Get user_id for checking borrowed books
    
    $error_message = '';
    if (isset($_GET['error'])) {
        $error_message = sanitize($_GET['error']);
    }
    
    $success_message = '';
    if (isset($_GET['success'])) {
        $success_message = sanitize($_GET['success']);
    }
    
    // Load books for both admin and user
    $limit = 12; // Show more books on dashboard
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    $offset = ($page - 1) * $limit;
    
    $search = isset($_GET['search']) ? trim(mysqli_real_escape_string($koneksi, $_GET['search'])) : '';
}

$sql_count = "SELECT COUNT(*) as total FROM buku";
if (!empty($search)) {
    $sql_count .= " WHERE judul LIKE ?";
}

$total_books = 0;
if ($stmt_count = mysqli_prepare($koneksi, $sql_count)) {
    if (!empty($search)) {
        $search_param = "%{$search}%";
        mysqli_stmt_bind_param($stmt_count, "s", $search_param);
    }
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    if ($row_count = mysqli_fetch_assoc($result_count)) {
        $total_books = $row_count['total'];
    }
    mysqli_stmt_close($stmt_count);
}

$total_pages = ceil($total_books / $limit);
$page = min($page, max(1, $total_pages));
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM buku";
if (!empty($search)) {
    $sql .= " WHERE judul LIKE ?";
}
$sql .= " ORDER BY judul ASC LIMIT ? OFFSET ?";

$books = [];
if ($stmt = mysqli_prepare($koneksi, $sql)) {
    if (!empty($search)) {
        $search_param = "%{$search}%";
        mysqli_stmt_bind_param($stmt, "sii", $search_param, $limit, $offset);
    } else {
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $books = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan Muflih</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">    <style>        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            min-height: 100vh;
            padding: 15px;
            position: fixed;
            left: 0;
            top: 60px; /* Height of top navbar */
            height: calc(100vh - 60px); /* Adjust for top navbar */
            box-shadow: 3px 0 10px rgba(0,0,0,0.15);
            z-index: 100;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background-color: #495057;
        }
        .content {
            margin-left: 250px; /* Width of the sidebar */
            padding: 80px 20px 20px 20px; /* Top padding increased to account for fixed navbar */
            width: calc(100% - 250px);
        }
        .top-navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            height: 60px;
            z-index: 1030;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0 20px;
        }        .search-container {
            max-width: 400px;
            width: 100%;
            margin: 0 15px;
        }
        
        @media (max-width: 992px) {
            .top-navbar {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            .search-container {
                margin: 10px 0;
                max-width: 100%;
            }
            .content {
                margin-left: 0;
                width: 100%;
                padding-top: 150px;
            }
            .sidebar {
                top: 150px;
                height: calc(100vh - 150px);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                top: 0;
                height: auto;
                min-height: auto;
            }
            .content {
                margin-left: 0;
                width: 100%;
                padding-top: 60px;
            }
        }.book-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            background-color: white;
            height: 100%;
            border: none;
            display: flex;
            flex-direction: column;
        }
        .book-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.12);
        }
        .book-img {
            height: 250px;
            object-fit: cover;
            width: 100%;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }/* Special styling for single book view */
        .col-md-8 .book-img {
            height: 380px;
            object-fit: contain;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 15px;
        }
        .col-md-8 .book-title {
            font-size: 1.5rem;
            margin: 10px 0;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
        .col-md-8 .book-author {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .col-md-8 .book-info {
            padding: 25px;
            text-align: center;
        }
        .col-md-8 .btn-action {
            margin-top: 15px;
            font-size: 16px;
            padding: 10px 20px;
        }
        .book-info {
            padding: 15px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .book-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 16px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .book-author {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .btn-action {
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
            margin-top: auto;
        }
    </style>
</head>
<body>    <!-- Top navbar -->
    <nav class="top-navbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="bi bi-book-half me-2" style="font-size: 1.4rem; color: #007bff;"></i>
            <span class="navbar-brand mb-0 h1">Perpustakaan Muflih</span>
        </div>
        
        <div class="search-container mx-auto">
            <form action="dashboard.php" method="get" class="mb-0">
                <div class="input-group shadow-sm">
                    <input type="text" class="form-control border-primary-subtle" placeholder="Cari judul buku..." name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="d-flex align-items-center">
            <span class="me-3">Selamat Datang, <?php echo $username; ?></span>
            <span class="badge bg-<?php echo ($role === 'admin' ? 'danger' : 'info'); ?> me-3"><?php echo ucfirst($role); ?></span>
            <a href="<?php echo BASE_URL; ?>auth/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <nav class="sidebar p-3">
        <h4 class="text-center mb-4">Perpus Muflih</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door-fill me-2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pages/buku/list_buku.php"><i class="bi bi-book-fill me-2"></i> Daftar Buku</a>
            </li>
            <?php if ($role === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="pages/buku/tambah_buku.php"><i class="bi bi-plus-circle-fill me-2"></i> Tambah Buku</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pages/user/list_user.php"><i class="bi bi-people-fill me-2"></i> Manajemen User</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pages/user/tambah_user.php"><i class="bi bi-person-plus-fill me-2"></i> Tambah User</a>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="pages/peminjaman/pinjam_buku.php"><i class="bi bi-journal-arrow-down me-2"></i> Pinjam Buku</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pages/peminjaman/daftar_pinjaman.php"><i class="bi bi-journal-bookmark-fill me-2"></i> Buku Dipinjam</a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <div class="content">
            
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-collection me-2"></i>Perpustakaan Muflih</h2>
                    <?php if ($role === 'admin'): ?>
                        <a href="pages/buku/tambah_buku.php" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Buku
                        </a>
                    <?php endif; ?>
                </div>
                <hr class="mb-4">

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>                <?php endif; ?>

                <h3 class="mb-4">
                    <?php if (!empty($search)): ?>
                        <i class="bi bi-search me-2"></i>Hasil Pencarian: "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        <i class="bi bi-collection me-2"></i>Koleksi Buku
                    <?php endif; ?>
                    <span class="badge bg-secondary ms-2"><?php echo count($books); ?> buku</span>
                </h3>
                
                <?php                    // Determine grid columns based on number of books
                    $book_count = count($books);
                    $grid_class = "row g-4 ";
                    $container_class = "";
                    
                    if ($book_count == 1) {
                        $grid_class .= "row-cols-1 justify-content-center";
                        $container_class = "container-sm px-4"; // Container for better centering of single book
                    } elseif ($book_count == 2) {
                        $grid_class .= "row-cols-1 row-cols-sm-2 justify-content-center";
                        $container_class = "container-md"; // Medium container for 2 books
                    } elseif ($book_count <= 4) {
                        $grid_class .= "row-cols-1 row-cols-sm-2 row-cols-md-4 justify-content-center";
                    } else {
                        $grid_class .= "row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5";
                    }
                    ?>
                <div class="<?php echo $container_class; ?>">
                    <div class="<?php echo $grid_class; ?>">
                    <?php 
                    if (count($books) > 0) {
                        foreach ($books as $book) {
                            // Check if book is currently borrowed by this user (for user role only)
                            $buku_id = $book['id'];
                            $is_borrowed = false;
                            
                            if ($role === 'user') {
                                $query_check_borrowed = "SELECT * FROM peminjaman 
                                                        WHERE user_id = ? AND buku_id = ? AND status = 'dipinjam'";
                                if ($stmt_check = mysqli_prepare($koneksi, $query_check_borrowed)) {
                                    mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $buku_id);
                                    mysqli_stmt_execute($stmt_check);
                                    mysqli_stmt_store_result($stmt_check);
                                    $is_borrowed = mysqli_stmt_num_rows($stmt_check) > 0;
                                    mysqli_stmt_close($stmt_check);
                                }
                            }// Get image path or use default image
                            $gambar = !empty($book['gambar']) ? "assets/book_images/{$book['gambar']}" : "assets/book_images/contoh.png";
                            ?>                            <?php
                            // Determine column width based on number of books
                            $col_class = 'col mb-4';
                            if (count($books) == 1) {
                                $col_class = 'col-12 col-md-8 col-lg-6 mb-4'; // Centralized single book with appropriate width
                            } elseif (count($books) == 2) {
                                $col_class = 'col-12 col-sm-6 mb-4'; // Balanced width for 2 books
                            } elseif (count($books) <= 4) {
                                $col_class = 'col-12 col-sm-6 col-md-6 col-lg-3 mb-4';
                            }
                            ?>
                            <div class="<?php echo $col_class; ?>">
                                <div class="book-card">
                                    <div class="position-relative">
                                        <img src="<?php echo $gambar; ?>" class="book-img" alt="<?php echo htmlspecialchars($book['judul']); ?>">
                                    </div>
                                    <div class="book-info">
                                        <div>
                                            <h5 class="book-title"><?php echo htmlspecialchars($book['judul']); ?></h5>                                            <p class="book-author"><?php echo htmlspecialchars($book['pengarang']); ?></p>
                                            <?php if (count($books) == 1): ?>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($book['penerbit']); ?>, <?php echo htmlspecialchars($book['tahun_terbit']); ?></p>
                                            <p class="mb-2">Genre: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($book['genre']); ?></span></p>
                                            <p class="badge <?php echo $book['stok'] > 0 ? 'bg-success' : 'bg-danger'; ?> mb-3">
                                                Stok: <?php echo $book['stok']; ?> buku
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                          <?php if ($role === 'user'): ?>
                                            <div>
                                                <?php if ($is_borrowed): ?>
                                                    <form method="post" action="pages/peminjaman/kembalikan_buku.php">
                                                        <input type="hidden" name="buku_id" value="<?php echo $book['id']; ?>">
                                                        <button type="submit" class="btn <?php echo (count($books) == 1) ? 'btn-primary' : 'btn-sm btn-primary'; ?> btn-action w-100">
                                                            <i class="bi bi-arrow-repeat"></i> Kembalikan
                                                        </button>
                                                    </form>
                                                <?php elseif ($book['stok'] > 0): ?>
                                                    <form method="post" action="pages/peminjaman/proses_pinjam.php">
                                                        <input type="hidden" name="buku_id" value="<?php echo $book['id']; ?>">
                                                        <button type="submit" class="btn <?php echo (count($books) == 1) ? 'btn-success' : 'btn-sm btn-success'; ?> btn-action w-100">
                                                            <i class="bi bi-journal-bookmark"></i> Pinjam
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn <?php echo (count($books) == 1) ? 'btn-secondary' : 'btn-sm btn-secondary'; ?> btn-action w-100" disabled>
                                                        <i class="bi bi-x-circle"></i> Tidak Tersedia
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($role === 'admin'): ?>
                                            <div class="<?php echo (count($books) == 1) ? 'd-grid gap-2 mt-3' : ''; ?>">
                                                <a href="pages/buku/edit_buku.php?id=<?php echo $book['id']; ?>" class="btn <?php echo (count($books) == 1) ? 'btn-warning' : 'btn-sm btn-warning'; ?> btn-action w-100">
                                                    <i class="bi bi-pencil"></i> Edit Buku
                                                </a>
                                                <?php if (count($books) == 1): ?>
                                                <a href="pages/buku/hapus_buku.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-danger mt-2" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                                                    <i class="bi bi-trash"></i> Hapus Buku
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }                    } else {
                        ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="bi bi-book-half text-muted" style="font-size: 5rem;"></i>
                                </div>
                                <h4 class="text-muted mb-3">Tidak ada buku ditemukan</h4>
                                <?php if (!empty($search)): ?>
                                    <p class="text-muted mb-4">Pencarian untuk "<?php echo htmlspecialchars($search); ?>" tidak menghasilkan buku apapun.</p>
                                    <a href="dashboard.php" class="btn btn-primary">Kembali ke semua buku</a>
                                <?php else: ?>
                                    <p class="text-muted mb-4">Belum ada buku yang ditambahkan ke perpustakaan.</p>
                                    <?php if ($role === 'admin'): ?>
                                        <a href="pages/buku/tambah_buku.php" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Buku
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>