<?php
require_once 'config/koneksi.php';

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username_db, $db_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verify password using password_verify if passwords are hashed
                        // For now, using plain text comparison as in the original code
                        if ($password === $db_password) { // Consider using password_hash() and password_verify()
                            session_regenerate_id(true); // Regenerate session ID for security
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $username_db;
                            $_SESSION['role'] = $role;
                            $_SESSION['loggedin'] = true; // Add a logged-in flag

                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $error = "Password yang Anda masukkan salah.";
                        }
                    }
                } else {
                    $error = "Username tidak ditemukan.";
                }
            } else {
                $error = "Oops! Terjadi kesalahan saat eksekusi query. Silakan coba lagi nanti.";
            }
            mysqli_stmt_close($stmt);
        } else {
             $error = "Oops! Terjadi kesalahan database saat persiapan statement. Silakan coba lagi nanti.";
        }
        mysqli_close($koneksi); 
    }
}

if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Muflih</title>
    <link href="assets/bootstrap.css/css/theme.css" rel="stylesheet">
    <link href="assets/book-animation.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid vh-100 p-0">
        <div class="row h-100 g-0">
            <!-- Left Section -->
            <div class="col-md-6 bg-primary bg-gradient text-white d-flex flex-column justify-content-center p-4 position-relative overflow-hidden">
                <div class="position-relative" style="z-index: 5">
                    <h1>Welcome to Perpustakaan</h1>
                    <p>Sistem informasi perpustakaan untuk pengelolaan buku dan peminjaman yang efisien dan mudah digunakan.</p>
                </div>
                
                <!-- Book character -->
                <div class="book-character" id="bookCharacter">
                    <div class="book-body">
                        <div class="book-cover-line"></div>
                        <div class="book-title"></div>
                        <div class="book-title-2"></div>
                    </div>
                    <div class="book-eyes">
                        <div class="eye">
                            <div class="pupil" id="leftPupil"></div>
                        </div>
                        <div class="eye">
                            <div class="pupil" id="rightPupil"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Decorative shapes using Bootstrap positioning -->
                <div class="position-absolute bottom-0 start-0 w-100 h-50 opacity-25">
                    <div class="position-absolute rounded-pill bg-info" style="width: 200px; height: 80px; bottom: 20%; left: -50px; transform: rotate(30deg);"></div>
                    <div class="position-absolute rounded-pill bg-info" style="width: 150px; height: 60px; bottom: 40%; left: 40%; transform: rotate(30deg);"></div>
                    <div class="position-absolute rounded-pill bg-info" style="width: 100px; height: 40px; bottom: 15%; left: 30%; transform: rotate(30deg);"></div>
                    <div class="position-absolute rounded-pill bg-info" style="width: 250px; height: 70px; bottom: 5%; left: 10%; transform: rotate(30deg);"></div>
                </div>
            </div>
            
            <!-- Right Section -->
            <div class="col-md-6 bg-white d-flex align-items-center justify-content-center">
                <div class="w-75">
                    <div class="text-center mb-4">
                        <img class="mb-3" src="assets/logosmk.png" alt="Logo SMK" width="80" height="auto">
                        <h2 class="fw-semibold text-primary mb-4">USER LOGIN</h2>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6f42c1" class="bi bi-person" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Username" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6f42c1" class="bi bi-lock" viewBox="0 0 16 16">
                                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                                    </svg>
                                </span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="mb-3 text-end">
                            <small><a href="#" class="text-decoration-none text-secondary">Forgot password?</a></small>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-primary text-white py-2 rounded-pill" type="submit">LOGIN</button>
                        </div>
                        
                        <p class="mt-5 mb-3 text-body-secondary text-center">&copy; Perpustakaan Muflih <?php echo date("Y"); ?></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap.js/bootstrap.bundle.min.js"></script>
    <!-- Book character animation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leftPupil = document.getElementById('leftPupil');
            const rightPupil = document.getElementById('rightPupil');
            const bookCharacter = document.getElementById('bookCharacter');
            
            // Track mouse position
            document.addEventListener('mousemove', function(event) {
                // Calculate mouse position relative to viewport
                const mouseX = event.clientX;
                const mouseY = event.clientY;
                
                // Get the viewport dimensions
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;
                
                // Calculate the normalized position (-1 to 1)
                const normX = (mouseX / windowWidth) * 2 - 1;
                const normY = (mouseY / windowHeight) * 2 - 1;
                
                // Limit pupil movement (max 3px in any direction)
                const maxPupilMove = 3;
                const pupilX = normX * maxPupilMove;
                const pupilY = normY * maxPupilMove;
                
                // Apply to pupils with a slight delay for natural movement
                setTimeout(() => {
                    leftPupil.style.transform = `translate(${pupilX}px, ${pupilY}px)`;
                    rightPupil.style.transform = `translate(${pupilX}px, ${pupilY}px)`;
                }, 50);
                
                // Slight tilt of the book character (max 5 degrees)
                const tiltDegree = 5;
                const bookTiltX = normX * tiltDegree;
                const bookTiltY = normY * tiltDegree / 2;
                
                // Apply slight movement to the book character but keep it in place
                bookCharacter.style.transform = `translateX(-50%) rotate(${bookTiltX}deg)`;
            });
            
            // Animated blink effect every few seconds
            function blinkEyes() {
                const eyes = document.querySelectorAll('.eye');
                
                // Random blink interval between 2-6 seconds
                const blinkInterval = Math.floor(Math.random() * 4000) + 2000;
                
                setTimeout(() => {
                    // Apply blink animation
                    eyes.forEach(eye => {
                        eye.style.height = '1px';
                        eye.style.transition = 'height 0.1s';
                    });
                    
                    // Open eyes after 150ms
                    setTimeout(() => {
                        eyes.forEach(eye => {
                            eye.style.height = '20px';
                        });
                        
                        // Schedule next blink
                        blinkEyes();
                    }, 150);
                }, blinkInterval);
            }
            
            // Start blinking
            blinkEyes();
        });
    </script>
</body>
</html>