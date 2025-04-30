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
        $stmt = null; // Initialize stmt to null
        $password_verified = false; // Initialize password_verified earlier

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username_db, $db_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        // $password_verified = false; // Moved initialization up
                        $is_hashed = preg_match('/^\$2[axy]\$/', $db_password); // Check if it looks like a bcrypt hash

                        if ($is_hashed) {
                            // If password in DB is hashed, verify using password_verify
                            if (password_verify($password, $db_password)) {
                                $password_verified = true;
                            }
                        } else {
                            // If password in DB is plain text, compare directly
                            if ($password === $db_password) {
                                $password_verified = true;
                                // IMPORTANT: Hash the plain text password and update the database
                                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $sql_update_hash = "UPDATE users SET password = ? WHERE id = ?";
                                $stmt_update = null; // Initialize update statement
                                if ($stmt_update = mysqli_prepare($koneksi, $sql_update_hash)) {
                                    mysqli_stmt_bind_param($stmt_update, "si", $new_hashed_password, $id);
                                    if (!mysqli_stmt_execute($stmt_update)) {
                                        // Log error or handle - password update failed, but login might still proceed for this time
                                        error_log("Gagal update hash password untuk user ID: " . $id . " Error: " . mysqli_stmt_error($stmt_update));
                                    }
                                    mysqli_stmt_close($stmt_update);
                                } else {
                                     error_log("Gagal prepare statement update hash password untuk user ID: " . $id . " Error: " . mysqli_error($koneksi));
                                }
                            }
                        }

                        if ($password_verified) {
                            session_regenerate_id(true); // Regenerate session ID for security
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $username_db;
                            $_SESSION['role'] = $role;
                            $_SESSION['loggedin'] = true; // Add a logged-in flag

                            // Close statement before redirecting
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
            // Close statement if it was opened
            if ($stmt) { // Check if $stmt was successfully prepared
                 mysqli_stmt_close($stmt);
            }
        } else {
             $error = "Oops! Terjadi kesalahan database saat persiapan statement. Silakan coba lagi nanti.";
        }
        
        // Close connection only if login failed or DB error occurred
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
        /* Book Character Styles */
        .book-character {
            position: absolute;
            bottom: 10%; /* Position it lower */
            left: 50%;
            transform: translateX(-50%);
            width: 150px; /* Increased size */
            height: 180px; /* Increased size */
            perspective: 1000px;
            z-index: 10; /* Ensure it's above decorative shapes */
            transition: transform 0.2s ease-out; /* Smooth transform */
        }

        /* Cloud Styles */
        .background-layer {
            position: fixed; /* Change to fixed to cover viewport */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, #87CEEB, #4682B4); /* Sky blue to Steel blue gradient */
            overflow: hidden; /* Hide parts of clouds outside */
            z-index: -1; /* Send it behind everything */
        }

        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.9); /* Slightly transparent white */
            border-radius: 50%; /* Keep base roundness */
            box-shadow: 0 0 20px 10px rgba(255, 255, 255, 0.7); /* Soft glow */
            opacity: 0.8;
            animation: moveCloud 60s linear infinite; /* Slow movement */
        }

        /* Define multiple cloud shapes and positions */
        /* Adjust width/height back to non-circular for puffiness */
        .cloud.c1 { width: 150px; height: 50px; top: 10%; left: -100px; animation-duration: 50s; }
        .cloud.c2 { width: 200px; height: 70px; top: 25%; left: -150px; animation-duration: 70s; animation-delay: -10s; }
        .cloud.c3 { width: 120px; height: 40px; top: 50%; left: -80px; animation-duration: 45s; animation-delay: -20s; }
        .cloud.c4 { width: 180px; height: 60px; top: 70%; left: -120px; animation-duration: 65s; animation-delay: -30s; }
        .cloud.c5 { width: 100px; height: 35px; top: 85%; left: -50px; animation-duration: 40s; animation-delay: -5s; }

        /* Restore Cloud puffiness using pseudo-elements */
        .cloud::before, .cloud::after {
            content: '';
            position: absolute;
            background: inherit;
            border-radius: 50%;
            box-shadow: inherit;
            opacity: inherit;
        }
        .cloud::before {
            width: 60%; height: 120%; /* Adjust size/shape */
            top: -40%; left: 10%;
        }
        .cloud::after {
            width: 70%; height: 100%; /* Adjust size/shape */
            top: -20%; right: 5%;
        }
        

        /* Cloud movement animation */
        @keyframes moveCloud {
            from { transform: translateX(0); }
            to { transform: translateX(calc(100vw + 300px)); } /* Move across the screen + buffer */
        }


        .book-body {
            width: 100%;
            height: 100%;
            background-color: #a0522d; /* Sienna brown */
            border: 3px solid #5c300a; /* Darker brown border */
            border-radius: 5px 10px 10px 5px; /* Book shape */
            position: relative;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
        }

        .book-cover-line { /* Spine */
            position: absolute;
            left: 5px;
            top: 0;
            bottom: 0;
            width: 15px; /* Spine width */
            background-color: #5c300a; /* Darker brown */
            border-radius: 5px 0 0 5px;
        }
        
        .book-title { /* Decorative element on cover */
            position: absolute;
            top: 20px;
            left: 30px;
            right: 10px;
            height: 20px;
            background-color: #e0cfa8; /* Cream color */
            border: 1px solid #5c300a;
            border-radius: 3px;
        }
        .book-title-2 { /* Another decorative element */
            position: absolute;
            top: 50px;
            left: 30px;
            right: 25px; /* Shorter */
            height: 10px;
            background-color: #e0cfa8; /* Cream color */
            border: 1px solid #5c300a;
            border-radius: 3px;
        }


        .book-eyes {
            position: absolute;
            top: 45%; /* Position eyes lower */
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 25px; /* Space between eyes */
            z-index: 1; /* Eyes on top of body */
        }

        .eye {
            width: 25px; /* Larger eyes */
            height: 30px; /* Oval shape */
            background-color: white;
            border-radius: 50%;
            border: 2px solid #5c300a; /* Border */
            position: relative;
            overflow: hidden;
            transition: height 0.1s ease-in-out, transform 0.1s ease-in-out; /* Smooth blink and movement */
            box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
        }

        .pupil {
            width: 12px; /* Larger pupil */
            height: 12px;
            background-color: #333;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Center pupil */
            transition: transform 0.1s linear; /* Faster pupil movement */
        }

        /* Blinking animation */
        .eye.blink {
            height: 3px; /* Squish vertically */
            transform: scaleY(0.1); /* Scale down */
        }
        
        /* Decorative shapes styling */
        /* Remove decorative shape styles */
        /*
        .decorative-shape {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.15); 
            border-radius: 50%;
            opacity: 0.5;
            filter: blur(5px); 
            z-index: 1; 
        }
        */

    </style>
    <!-- Remove the link to non-existent book-animation.css -->
    <!-- <link href="assets/book-animation.css" rel="stylesheet"> -->
</head>
<body>
    <!-- Background Layer with Clouds - Moved outside the grid -->
    <div class="background-layer">
        <div class="cloud c1"></div>
        <div class="cloud c2"></div>
        <div class="cloud c3"></div>
        <div class="cloud c4"></div>
        <div class="cloud c5"></div>
    </div>

    <div class="container-fluid vh-100 p-0 d-flex align-items-center justify-content-center"> <!-- Added flex utilities for centering -->
        <div class="row h-100 g-0 w-100"> <!-- Ensure row takes full width -->
            <!-- Left Section -->
            <div class="col-md-6 text-white d-flex flex-column justify-content-center p-4 position-relative overflow-hidden">
                <!-- Removed background layer from here -->

                <div class="position-relative" style="z-index: 5"> <!-- Content needs higher z-index -->
                    <h1>Welcome to Perpustakaan</h1>
                    <p>Sistem informasi perpustakaan untuk pengelolaan buku dan peminjaman yang efisien dan mudah digunakan.</p>
                </div>

                <!-- Book character -->
                <div class="book-character" id="bookCharacter" style="z-index: 10;"> <!-- Ensure character is above clouds -->
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

                <!-- Remove Decorative shapes -->
                 
                 
                 
                 
            </div>

            <!-- Right Section -->
            <div class="col-md-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                <div class="w-75">
                    <!-- Card for Login Box -->
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
                                <div class="mb-3"> <!-- Reduced bottom margin -->
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                                            </svg>
                                        </span>
                                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Password" required>
                                    </div>
                                </div>
                                <div class="mb-4 text-end"> <!-- Adjusted margin -->
                                    <small><a href="#" class="text-decoration-none text-secondary">Forgot password?</a></small>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary text-white py-2 rounded-pill fw-semibold" type="submit">LOGIN</button>
                                </div>

                                <p class="mt-4 mb-0 text-white-50 text-center small">&copy; Perpustakaan Muflih <?php echo date("Y"); ?></p>
                            </form>
                        </div> <!-- End card-body -->
                    </div> <!-- End card -->
                </div> <!-- End w-75 -->
            </div> <!-- End col-md-6 -->
        </div>
    </div>

    <script src="assets/bootstrap.js/bootstrap.bundle.min.js"></script>
    <!-- Book character animation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leftPupil = document.getElementById('leftPupil');
            const rightPupil = document.getElementById('rightPupil');
            const bookCharacter = document.getElementById('bookCharacter');
            const eyes = document.querySelectorAll('.eye'); // Select both eyes

            // Get the bounding box of the left panel (where the character is)
            const leftPanel = document.querySelector('.col-md-6.text-white'); // Selector remains the same
            let panelRect = leftPanel.getBoundingClientRect();

            // Update panelRect on resize
            window.addEventListener('resize', () => {
                panelRect = leftPanel.getBoundingClientRect();
            });

            // Track mouse position within the left panel
            leftPanel.addEventListener('mousemove', function(event) {
                // Calculate mouse position relative to the left panel
                const mouseX = event.clientX - panelRect.left;
                const mouseY = event.clientY - panelRect.top;

                // Calculate the normalized position within the panel (-1 to 1, approximately)
                // Center is (0,0)
                const normX = (mouseX / panelRect.width) * 2 - 1;
                const normY = (mouseY / panelRect.height) * 2 - 1;

                // Limit pupil movement (max 5px in any direction)
                const maxPupilMove = 5; // Increased range
                const pupilX = normX * maxPupilMove;
                const pupilY = normY * maxPupilMove;

                // Apply to pupils immediately for responsiveness - Use escaped backticks or single quotes
                leftPupil.style.transform = `translate(calc(-50% + ${pupilX}px), calc(-50% + ${pupilY}px))`;
                rightPupil.style.transform = `translate(calc(-50% + ${pupilX}px), calc(-50% + ${pupilY}px))`;

                // Slight tilt of the book character based on horizontal mouse position
                const maxTilt = 8; // Max tilt degrees
                const bookTilt = normX * maxTilt * -1; // Tilt opposite to mouse direction

                // Apply tilt and slight vertical movement based on vertical mouse position
                const maxVerticalMove = 5; // Max px movement up/down
                const bookMoveY = normY * maxVerticalMove * -0.5; // Move slightly up/down

                bookCharacter.style.transform = `translateX(-50%) translateY(${bookMoveY}px) rotateY(${bookTilt}deg)`;
            });

            // Reset character position when mouse leaves the panel
            leftPanel.addEventListener('mouseleave', () => {
                 leftPupil.style.transform = 'translate(-50%, -50%)';
                 rightPupil.style.transform = 'translate(-50%, -50%)';
                 bookCharacter.style.transform = 'translateX(-50%) translateY(0px) rotateY(0deg)';
            });


            // Animated blink effect
            function blinkEyes() {
                // Random blink interval between 2-7 seconds
                const blinkInterval = Math.random() * 5000 + 2000;

                setTimeout(() => {
                    // Add blink class to make eyes 'close'
                    eyes.forEach(eye => eye.classList.add('blink'));

                    // Remove blink class after 150ms to 'open' eyes
                    setTimeout(() => {
                        eyes.forEach(eye => eye.classList.remove('blink'));
                        // Schedule next blink
                        blinkEyes();
                    }, 150);
                }, blinkInterval);
            }

            // Start blinking after a short delay
            setTimeout(blinkEyes, 1000);

            // Add subtle 'breathing' animation to the book body
            bookCharacter.style.animation = 'breathing 5s ease-in-out infinite';

            // Define breathing animation in CSS (could also be done via JS but CSS is cleaner)
            // Check if styleSheets[0] exists before trying to insertRule
            if (document.styleSheets.length > 0) {
                const styleSheet = document.styleSheets[0];
                // Use try-catch in case inserting the rule fails for some reason (e.g., security restrictions)
                try {
                    styleSheet.insertRule(`
                        @keyframes breathing {
                            0%, 100% { transform: translateX(-50%) scale(1); }
                            50% { transform: translateX(-50%) scale(1.03); } /* Slightly expand */
                        }
                    `, styleSheet.cssRules.length);
                } catch (e) {
                    console.error("Could not insert CSS rule for breathing animation:", e);
                }
            } else {
                 console.warn("No stylesheets found to insert breathing animation rule.");
            }

             // Make eyes react slightly when focusing on input fields
             const inputs = document.querySelectorAll('#username, #password');
             inputs.forEach(input => {
                 input.addEventListener('focus', () => {
                     eyes.forEach(eye => eye.style.transform = 'scale(1.1)'); // Slightly enlarge eyes
                 });
                 input.addEventListener('blur', () => {
                     eyes.forEach(eye => eye.style.transform = 'scale(1)'); // Return to normal size
                 });
             });

        });
    </script>
</body>
</html>