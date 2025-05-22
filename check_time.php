<?php
require_once 'config/koneksi.php';

// Check server and database time
echo "<h1>Server and Database Time Check</h1>";

// PHP/Server Time
echo "<h2>PHP Server Time:</h2>";
echo "<p>Current server time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server timezone: " . date_default_timezone_get() . "</p>";

// MySQL Database Time
echo "<h2>MySQL Database Time:</h2>";
$result = mysqli_query($koneksi, "SELECT NOW() as db_time, @@session.time_zone as db_timezone");
$row = mysqli_fetch_assoc($result);
echo "<p>Database time: " . $row['db_time'] . "</p>";
echo "<p>Database timezone: " . $row['db_timezone'] . "</p>";

// Check password_reset token data
echo "<h2>Recent Password Reset Tokens:</h2>";
$tokens = mysqli_query($koneksi, "SELECT user_id, token, expires_at, created_at FROM password_reset ORDER BY created_at DESC LIMIT 5");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>User ID</th><th>Token (first 8 chars)</th><th>Expires At</th><th>Created At</th><th>Status</th></tr>";

while ($token = mysqli_fetch_assoc($tokens)) {
    $expires = strtotime($token['expires_at']);
    $now = time();
    $status = $expires > $now ? "Valid" : "Expired";
    $time_left = $expires - $now;
    
    echo "<tr>";
    echo "<td>" . $token['user_id'] . "</td>";
    echo "<td>" . substr($token['token'], 0, 8) . "...</td>";
    echo "<td>" . $token['expires_at'] . "</td>";
    echo "<td>" . $token['created_at'] . "</td>";
    echo "<td>" . $status . " (" . ($time_left > 0 ? floor($time_left / 60) . " minutes left" : "expired") . ")</td>";
    echo "</tr>";
}
echo "</table>";

// Create test token (for debugging)
echo "<h2>Create Test Token (for debugging)</h2>";
if (isset($_GET['create_test']) && $_GET['create_test'] == 1) {
    $user_id = 1; // Replace with a valid user ID
    $token = bin2hex(random_bytes(32));
    
    // Set expiration to 1 hour from now using PHP time
    $expires_at = date('Y-m-d H:i:s', time() + 3600);
    
    // Delete existing tokens
    mysqli_query($koneksi, "DELETE FROM password_reset WHERE user_id = $user_id");
    
    // Insert new token
    $insert = mysqli_query($koneksi, "INSERT INTO password_reset (user_id, token, expires_at) VALUES ($user_id, '$token', '$expires_at')");
    
    if ($insert) {
        echo "<p style='color: green'>Test token created successfully!</p>";
        echo "<p>Token: <a href='forgetpw/reset.php?token=$token'>$token</a></p>";
        echo "<p>Expires at: $expires_at</p>";
    } else {
        echo "<p style='color: red'>Failed to create test token: " . mysqli_error($koneksi) . "</p>";
    }
} else {
    echo "<p><a href='?create_test=1'>Click here to create a test token</a> (for debugging only)</p>";
}
?>
