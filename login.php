<?php
require_once 'config/koneksi.php';

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
        $stmt = null;
        $password_verified = false;

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username_db, $db_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        $is_hashed = preg_match('/^\$2[axy]\$/', $db_password);

                        if ($is_hashed) {
                            if (password_verify($password, $db_password)) {
                                $password_verified = true;
                            }
                        } else {
                            if ($password === $db_password) {
                                $password_verified = true;
                                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $sql_update_hash = "UPDATE users SET password = ? WHERE id = ?";
                                $stmt_update = null;
                                if ($stmt_update = mysqli_prepare($koneksi, $sql_update_hash)) {
                                    mysqli_stmt_bind_param($stmt_update, "si", $new_hashed_password, $id);
                                    if (!mysqli_stmt_execute($stmt_update)) {
                                        error_log("Gagal update hash password untuk user ID: " . $id . " Error: " . mysqli_stmt_error($stmt_update));
                                    }
                                    mysqli_stmt_close($stmt_update);
                                } else {
                                     error_log("Gagal prepare statement update hash password untuk user ID: " . $id . " Error: " . mysqli_error($koneksi));
                                }
                            }
                        }

                        if ($password_verified) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $username_db;
                            $_SESSION['role'] = $role;
                            $_SESSION['loggedin'] = true;

                            mysqli_stmt_close($stmt);
                            mysqli_close($koneksi);
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
            if ($stmt) {
                 mysqli_stmt_close($stmt);
            }
        } else {
             $error = "Oops! Terjadi kesalahan database saat persiapan statement. Silakan coba lagi nanti.";
        }
        
        if (!$password_verified) { 
             mysqli_close($koneksi); 
        }
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
    <style>
        /* Background and day-night elements */
        .background-layer {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
            overflow: hidden;
            z-index: -1;
        }

        .day {
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
        }

        .night {
            background: linear-gradient(to bottom, #000428, #004e92);
        }

        /* Sun and moon */
        .sun {
            position: absolute;
            top: 10%;
            left: 10%;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, #FFD700, #FFA500);
            border-radius: 50%;
            box-shadow: 0 0 50px rgba(255, 223, 0, 0.8);
            z-index: 1;
            transition: all 2s ease-in-out;
        }

        .moon {
            position: absolute;
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, #FFF, #BBB);
            border-radius: 50%;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.8);
            z-index: 1;
            transition: all 2s ease-in-out;
        }

        /* Stars */
        .star {
            position: absolute;
            width: 5px;
            height: 5px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            animation: twinkle 2s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Clouds */
        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            box-shadow: 0 0 20px 10px rgba(255, 255, 255, 0.7);
            opacity: 0.8;
            animation: moveCloud 60s linear infinite;
        }

        .cloud.c1 { width: 150px; height: 50px; top: 10%; left: -100px; animation-duration: 50s; }
        .cloud.c2 { width: 200px; height: 70px; top: 25%; left: -150px; animation-duration: 70s; animation-delay: -10s; }
        .cloud.c3 { width: 120px; height: 40px; top: 50%; left: -80px; animation-duration: 45s; animation-delay: -20s; }
        .cloud.c4 { width: 180px; height: 60px; top: 70%; left: -120px; animation-duration: 65s; animation-delay: -30s; }
        .cloud.c5 { width: 100px; height: 35px; top: 85%; left: -50px; animation-duration: 40s; animation-delay: -5s; }

        .cloud::before, .cloud::after {
            content: '';
            position: absolute;
            background: inherit;
            border-radius: 50%;
            box-shadow: inherit;
            opacity: inherit;
        }
        .cloud::before {
            width: 60%; height: 120%;
            top: -40%; left: 10%;
        }
        .cloud::after {
            width: 70%; height: 100%;
            top: -20%; right: 5%;
        }
        
        @keyframes moveCloud {
            from { transform: translateX(0); }
            to { transform: translateX(calc(100vw + 300px)); }
        }

        /* Book character */
        .book-character {
            position: absolute;
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 180px;
            perspective: 1000px;
            z-index: 10;
            transition: transform 0.2s ease-out;
        }

        .book-body {
            width: 100%;
            height: 100%;
            background-color: #a0522d;
            border: 3px solid #5c300a;
            border-radius: 5px 10px 10px 5px;
            position: relative;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
        }

        .book-cover-line {
            position: absolute;
            left: 5px;
            top: 0;
            bottom: 0;
            width: 15px;
            background-color: #5c300a;
            border-radius: 5px 0 0 5px;
        }
        
        .book-title {
            position: absolute;
            top: 20px;
            left: 30px;
            right: 10px;
            height: 20px;
            background-color: #e0cfa8;
            border: 1px solid #5c300a;
            border-radius: 3px;
        }
        
        .book-title-2 {
            position: absolute;
            top: 50px;
            left: 30px;
            right: 25px;
            height: 10px;
            background-color: #e0cfa8;
            border: 1px solid #5c300a;
            border-radius: 3px;
        }

        /* Book eyes */
        .book-eyes {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 25px;
            z-index: 1;
        }

        .eye {
            width: 25px;
            height: 30px;
            background-color: white;
            border-radius: 50%;
            border: 2px solid #5c300a;
            position: relative;
            overflow: hidden;
            transition: height 0.1s ease-in-out, transform 0.1s ease-in-out;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
        }

        .pupil {
            width: 12px;
            height: 12px;
            background-color: #333;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: transform 0.1s linear;
        }

        .eye.blink {
            height: 3px;
            transform: scaleY(0.1);
        }
    </style>
</head>
<body>
    <div class="background-layer">
        <div class="sun" id="sun"></div>
        <div class="moon" id="moon" style="display: none;"></div>
        <div class="cloud c1"></div>
        <div class="cloud c2"></div>
        <div class="cloud c3"></div>
        <div class="cloud c4"></div>
        <div class="cloud c5"></div>
        <div class="star" style="top: 20%; left: 30%; display: none;"></div>
        <div class="star" style="top: 40%; left: 50%; display: none;"></div>
        <div class="star" style="top: 60%; left: 70%; display: none;"></div>
    </div>

    <div class="container-fluid vh-100 p-0 d-flex align-items-center justify-content-center">
        <div class="row h-100 g-0 w-100">
            <div class="col-md-6 text-white d-flex flex-column justify-content-center p-4 position-relative overflow-hidden">
                <div class="position-relative" style="z-index: 5">
                    <h1>Welcome to Perpustakaan Muflih</h1>
                    <p>Sistem informasi perpustakaan untuk pengelolaan buku dan peminjaman yang efisien dan mudah digunakan.</p>
                </div>

                <div class="book-character" id="bookCharacter" style="z-index: 10;">
                    <div class="book-body">
                        <div class="book-cover-line"></div>
                        <div class="book-title"></div>
                        <div class="book-title-2"></div>
                        <div class="book-eyes">
                            <div class="eye">
                                <div class="pupil" id="leftPupil"></div>
                            </div>
                            <div class="eye">
                                <div class="pupil" id="rightPupil"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                <div class="w-75">
                    <div class="card shadow-sm rounded-3 border-0">
                        <div class="card-body p-4 p-md-5">
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
                                        <span class="input-group-text bg-white border-end-0 text-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                            </svg>
                                        </span>
                                        <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Username" required autofocus>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                                            </svg>
                                        </span>
                                        <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" placeholder="Password" required>
                                        <span class="input-group-text bg-white border-start-0" id="togglePassword" style="cursor: pointer;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-4 text-end">
                                    <small><a href="#" class="text-decoration-none text-secondary">Forgot password?</a></small>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary text-white py-2 rounded-pill fw-semibold" type="submit">LOGIN</button>
                                </div>

                                <p class="mt-4 mb-0 text-white-50 text-center small">&copy; Perpustakaan Muflih <?php echo date("Y"); ?></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap.js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leftPupil = document.getElementById('leftPupil');
            const rightPupil = document.getElementById('rightPupil');
            const bookCharacter = document.getElementById('bookCharacter');
            const eyes = document.querySelectorAll('.eye');

            const leftPanel = document.querySelector('.col-md-6.text-white');
            let panelRect = leftPanel.getBoundingClientRect();

            window.addEventListener('resize', () => {
                panelRect = leftPanel.getBoundingClientRect();
            });

            leftPanel.addEventListener('mousemove', function(event) {
                const mouseX = event.clientX - panelRect.left;
                const mouseY = event.clientY - panelRect.top;

                const normX = (mouseX / panelRect.width) * 2 - 1;
                const normY = (mouseY / panelRect.height) * 2 - 1;

                const maxPupilMove = 5;
                const pupilX = normX * maxPupilMove;
                const pupilY = normY * maxPupilMove;

                leftPupil.style.transform = `translate(calc(-50% + ${pupilX}px), calc(-50% + ${pupilY}px))`;
                rightPupil.style.transform = `translate(calc(-50% + ${pupilX}px), calc(-50% + ${pupilY}px))`;

                const maxTilt = 8;
                const bookTilt = normX * maxTilt * -1;

                const maxVerticalMove = 5;
                const bookMoveY = normY * maxVerticalMove * -0.5;

                bookCharacter.style.transform = `translateX(-50%) translateY(${bookMoveY}px) rotateY(${bookTilt}deg)`;
            });

            leftPanel.addEventListener('mouseleave', () => {
                 leftPupil.style.transform = 'translate(-50%, -50%)';
                 rightPupil.style.transform = 'translate(-50%, -50%)';
                 bookCharacter.style.transform = 'translateX(-50%) translateY(0px) rotateY(0deg)';
            });

            function blinkEyes() {
                const blinkInterval = Math.random() * 5000 + 2000;

                setTimeout(() => {
                    eyes.forEach(eye => eye.classList.add('blink'));

                    setTimeout(() => {
                        eyes.forEach(eye => eye.classList.remove('blink'));
                        blinkEyes();
                    }, 150);
                }, blinkInterval);
            }

            setTimeout(blinkEyes, 1000);

            bookCharacter.style.animation = 'breathing 5s ease-in-out infinite';

            if (document.styleSheets.length > 0) {
                const styleSheet = document.styleSheets[0];
                try {
                    styleSheet.insertRule(`
                        @keyframes breathing {
                            0%, 100% { transform: translateX(-50%) scale(1); }
                            50% { transform: translateX(-50%) scale(1.03); }
                        }
                    `, styleSheet.cssRules.length);
                } catch (e) {
                    console.error("Could not insert CSS rule for breathing animation:", e);
                }
            } else {
                 console.warn("No stylesheets found to insert breathing animation rule.");
            }

             const inputs = document.querySelectorAll('#username, #password');
             inputs.forEach(input => {
                 input.addEventListener('focus', () => {
                     eyes.forEach(eye => eye.style.transform = 'scale(1.1)');
                 });
                 input.addEventListener('blur', () => {
                     eyes.forEach(eye => eye.style.transform = 'scale(1)');
                 });
             });
        });

        // Password toggle visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = togglePassword.querySelector('svg');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // toggle the eye / eye slash icon
            if (type === 'password') {
                eyeIcon.innerHTML = `<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>`; // Eye icon SVG path
                eyeIcon.setAttribute('viewBox', '0 0 16 16');
            } else {
                eyeIcon.innerHTML = `<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.94 5.94 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>`; // Eye slash icon SVG path
                eyeIcon.setAttribute('viewBox', '0 0 16 16');
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backgroundLayer = document.querySelector('.background-layer');
            const sun = document.getElementById('sun');
            const moon = document.getElementById('moon');
            const stars = document.querySelectorAll('.star');

            function toggleDayNight() {
                if (backgroundLayer.classList.contains('day')) {
                    backgroundLayer.classList.replace('day', 'night');
                    sun.style.display = 'none';
                    moon.style.display = 'block';
                    stars.forEach(star => star.style.display = 'block');
                } else {
                    backgroundLayer.classList.replace('night', 'day');
                    sun.style.display = 'block';
                    moon.style.display = 'none';
                    stars.forEach(star => star.style.display = 'none');
                }
            }

            // Initialize with appropriate class
            backgroundLayer.classList.add('day');
            
            setInterval(toggleDayNight, 10000); // Change every 10 seconds
        });
    </script>
</body>
</html>