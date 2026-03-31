<?php
$testPath = '/var/www/frontoffice/package/assets/images/uploads/write-test-' . time();
echo "Testing write to: $testPath\n";
echo "Parent exists: " . (is_dir(dirname($testPath)) ? "YES" : "NO") . "\n";
echo "Parent readable: " . (is_readable(dirname($testPath)) ? "YES" : "NO") . "\n";
echo "Parent writable: " . (is_writable(dirname($testPath)) ? "YES" : "NO") . "\n";

if (@mkdir($testPath, 0777, true)) {
    echo "mkdir SUCCESS\n";
    @rmdir($testPath);
} else {
    echo "mkdir FAILED\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}

// Also test file write
$testFile = '/var/www/frontoffice/package/assets/images/uploads/test-' . time() . '.txt';
if (@file_put_contents($testFile, 'test')) {
    echo "File write SUCCESS\n";
    @unlink($testFile);
} else {
    echo "File write FAILED\n";
}
?>
