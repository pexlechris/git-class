<?php

// 1. Include file_operations.php
require_once 'file_operations.php';

echo "--- Test Script for delete_directory --- \n\n";

// 2. Define a test directory name
$test_dir_path = 'temp_test_dir_for_deletion';

// 3. Setup Phase
echo "--- Setup Phase ---\n";

// If the test directory already exists, attempt to delete it first
if (file_exists($test_dir_path)) {
    echo "Notice: Test directory '$test_dir_path' already exists. Attempting to remove it before test...\n";
    if (delete_directory($test_dir_path)) {
        echo "Pre-existing test directory removed successfully.\n";
    } else {
        echo "Error: Could not remove pre-existing test directory. Please check permissions or remove it manually and try again.\n";
        exit(1); // Exit if cleanup fails
    }
}

// Create the test directory
echo "Creating test directory: $test_dir_path\n";
if (!mkdir($test_dir_path, 0777, true)) {
    echo "Setup FAILED: Could not create main test directory '$test_dir_path'.\n";
    exit(1);
}

// Create a subdirectory inside it
$nested_dir_path = $test_dir_path . '/nested_dir';
echo "Creating nested directory: $nested_dir_path\n";
if (!mkdir($nested_dir_path, 0777, true)) {
    echo "Setup FAILED: Could not create nested directory '$nested_dir_path'.\n";
    // Attempt to clean up main directory before exiting
    delete_directory($test_dir_path);
    exit(1);
}

// Create a few files in the main test directory
$file1_path = $test_dir_path . '/file1.txt';
$file2_path = $test_dir_path . '/file2.txt';
echo "Creating file: $file1_path\n";
if (file_put_contents($file1_path, "Test content for file1") === false) {
    echo "Setup FAILED: Could not create file '$file1_path'.\n";
    delete_directory($test_dir_path);
    exit(1);
}
echo "Creating file: $file2_path\n";
if (file_put_contents($file2_path, "Test content for file2") === false) {
    echo "Setup FAILED: Could not create file '$file2_path'.\n";
    delete_directory($test_dir_path);
    exit(1);
}

// Create a file inside the nested directory
$file3_path = $nested_dir_path . '/file3.txt';
echo "Creating file: $file3_path\n";
if (file_put_contents($file3_path, "Test content for file3 in nested dir") === false) {
    echo "Setup FAILED: Could not create file '$file3_path'.\n";
    delete_directory($test_dir_path);
    exit(1);
}

// After creation, check if the main test directory and one of the files exist
if (is_dir($test_dir_path) && file_exists($file1_path) && file_exists($file3_path)) {
    echo "Setup SUCCESSFUL: Test directory and files created.\n";
} else {
    echo "Setup FAILED: Post-creation check failed. Directory or files missing.\n";
    // Attempt to clean up main directory before exiting
    if(file_exists($test_dir_path)) delete_directory($test_dir_path);
    exit(1);
}
echo "\n";

// 4. Execution Phase
echo "--- Execution Phase ---\n";
echo "Calling delete_directory('$test_dir_path')...\n";
$deletion_result = delete_directory($test_dir_path);

if ($deletion_result) {
    echo "Execution: delete_directory reported SUCCESS.\n";
} else {
    echo "Execution: delete_directory reported FAILURE.\n";
}
echo "\n";

// 5. Verification Phase
echo "--- Verification Phase ---\n";
if (!file_exists($test_dir_path)) {
    echo "Test PASSED: Directory '$test_dir_path' successfully deleted.\n";
} else {
    echo "Test FAILED: Directory '$test_dir_path' still exists.\n";
    // Attempt to clean up if test failed
    echo "Attempting to clean up '$test_dir_path' again...\n";
    if(delete_directory($test_dir_path)){
        echo "Cleanup successful.\n";
    } else {
        echo "Cleanup failed. Please remove '$test_dir_path' manually.\n";
    }
}

echo "\n--- Test Script Finished ---\n";

?>
