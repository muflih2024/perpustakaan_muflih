<?php
// This is a token validator utility
require_once '../config/koneksi.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    echo "<h1>Error: No token provided</h1>";
    echo "<p>Please provide a token in the URL parameter, like: check_token.php?token=your_token</p>";
    exit;
}

$token = $_GET['token'];

echo "<h1>Token Validator</h1>";

// Get token details
$sql = "SELECT pr.id, pr.user_id, pr.token, pr.expires_at, pr.created_at, u.username, u.email 
        FROM password_reset pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ?";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {
    $token_data = mysqli_fetch_assoc($result);
    
    // Show token information
    echo "<h2>Token Information:</h2>";
    echo "<ul>";
    echo "<li><strong>Token ID:</strong> {$token_data['id']}</li>";
    echo "<li><strong>User ID:</strong> {$token_data['user_id']}</li>";
    echo "<li><strong>Username:</strong> {$token_data['username']}</li>";
    echo "<li><strong>Email:</strong> {$token_data['email']}</li>";
    echo "<li><strong>Created At:</strong> {$token_data['created_at']}</li>";
    echo "<li><strong>Expires At:</strong> {$token_data['expires_at']}</li>";
    echo "</ul>";
    
    // Check if token is expired
    $expires_timestamp = strtotime($token_data['expires_at']);
    $current_timestamp = time();
    $time_diff = $expires_timestamp - $current_timestamp;
    
    echo "<h2>Token Status:</h2>";
    echo "<ul>";
    echo "<li><strong>Current PHP time:</strong> " . date('Y-m-d H:i:s', $current_timestamp) . "</li>";
    echo "<li><strong>Expiry time:</strong> " . date('Y-m-d H:i:s', $expires_timestamp) . "</li>";
    
    if ($expires_timestamp > $current_timestamp) {
        $minutes = floor($time_diff / 60);
        $seconds = $time_diff % 60;
        echo "<li style='color: green'><strong>Status:</strong> VALID (expires in {$minutes} minutes and {$seconds} seconds)</li>";
    } else {
        $minutes = floor(abs($time_diff) / 60);
        $seconds = abs($time_diff) % 60;
        echo "<li style='color: red'><strong>Status:</strong> EXPIRED ({$minutes} minutes and {$seconds} seconds ago)</li>";
    }
    echo "</ul>";
    
    echo "<h3>MySQL Database Check:</h3>";
    $db_check = mysqli_query($koneksi, "SELECT NOW() as db_time, '{$token_data['expires_at']}' > NOW() as is_valid");
    $db_result = mysqli_fetch_assoc($db_check);
    
    echo "<ul>";
    echo "<li><strong>Database current time:</strong> {$db_result['db_time']}</li>";
    echo "<li><strong>Database considers valid:</strong> " . ($db_result['is_valid'] ? "Yes" : "No") . "</li>";
    echo "</ul>";
    
    echo "<p><a href='../forgetpw/reset.php?token={$token}'>Try using this token</a></p>";
    
} else {
    echo "<h2 style='color: red'>Token not found in database</h2>";
    echo "<p>The provided token does not exist in the database. It may have been used already or never existed.</p>";
}

echo "<p><a href='../forgetpw/index.php'>Back to Password Reset</a></p>";
?>
