<?php

function delete_directory($dir_path) {
    if (!is_dir($dir_path)) {
        return false;
    }

    $items = scandir($dir_path);
    if ($items === false) {
        return false; 
    }

    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $path = $dir_path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            if (!delete_directory($path)) {
                return false;
            }
        } else {
            if (!unlink($path)) {
                return false;
            }
        }
    }

    return rmdir($dir_path);
}

/*
// Example Usage:

// Create a dummy directory and some files for testing
// IMPORTANT: Be very careful with the path you provide to delete_directory.
// It's recommended to use absolute paths or very specific relative paths.

$my_directory_to_delete = 'test_dir';

if (!is_dir($my_directory_to_delete)) {
    mkdir($my_directory_to_delete, 0777, true);
}
file_put_contents($my_directory_to_delete . '/file1.txt', 'test content');
if (!is_dir($my_directory_to_delete . '/subdir')) {
    mkdir($my_directory_to_delete . '/subdir', 0777, true);
}
file_put_contents($my_directory_to_delete . '/subdir/file2.txt', 'more test content');

echo "Attempting to delete: " . $my_directory_to_delete . "\n";

if (delete_directory($my_directory_to_delete)) {
    echo "Directory deleted successfully.\n";
} else {
    echo "Failed to delete directory.\n";
}

// Verify it's gone (optional)
if (!file_exists($my_directory_to_delete)) {
    echo "Verification: Directory no longer exists.\n";
} else {
    echo "Verification: Directory still exists! (Check permissions or errors)\n";
}
*/
?>
