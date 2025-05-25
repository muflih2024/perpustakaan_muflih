# Script to clean up test files before Vercel deployment
# This will remove test files and logs that aren't needed in production

# Create a log file to track what's being deleted
$logFile = "cleanup_log.txt"
"Cleanup started at $(Get-Date)" | Out-File -FilePath $logFile

# Function to safely remove files
function Remove-SafeFile {
    param (
        [string]$FilePath
    )
    if (Test-Path $FilePath) {
        Remove-Item -Path $FilePath -Force
        "Removed: $FilePath" | Out-File -FilePath $logFile -Append
    }
    else {
        "File not found: $FilePath" | Out-File -FilePath $logFile -Append
    }
}

# Test files in forgetpw/
Remove-SafeFile -FilePath "forgetpw\simple_test_email.php"
Remove-SafeFile -FilePath "forgetpw\test_email_detailed.php"
Remove-SafeFile -FilePath "forgetpw\test_email.php"

# Test files in root
Remove-SafeFile -FilePath "test_email_now.php"
Remove-SafeFile -FilePath "test_env.php"
Remove-SafeFile -FilePath "check_time.php"

# Duplicate deployment files (keep PS1 versions)
Remove-SafeFile -FilePath "prepare_vercel.sh"
Remove-SafeFile -FilePath "deploy_to_vercel.sh"

# Clean logs directory - keep the directory but remove contents
if (Test-Path "logs") {
    Get-ChildItem -Path "logs" -Recurse -File | ForEach-Object {
        Remove-Item -Path $_.FullName -Force
        "Removed: $($_.FullName)" | Out-File -FilePath $logFile -Append
    }
    "Cleaned logs directory" | Out-File -FilePath $logFile -Append
}

# Count remaining files for reference
$fileCount = (Get-ChildItem -Recurse -File).Count
"Total files remaining after cleanup: $fileCount" | Out-File -FilePath $logFile -Append

Write-Host "Cleanup complete. Check $logFile for details."
Write-Host "Total files remaining: $fileCount"
