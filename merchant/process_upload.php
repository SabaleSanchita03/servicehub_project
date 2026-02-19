<?php
require 'config/db.php';

if (isset($_POST['submit'])) {
    $merchant_id = $_POST['merchant_id'];
    $file = $_FILES['profile_image'];

    // 1. File Properties
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // 2. Extract extension and validate
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 2000000) { // 2MB Limit
                
                // 3. Generate unique name to avoid overwriting files
                $newFileName = "merchant_" . $merchant_id . "_" . uniqid('', true) . "." . $fileExt;
                $fileDestination = 'assets/images/' . $newFileName;

                // 4. Move file to folder
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    
                    // 5. Update Database
                    $sql = "UPDATE merchants SET profile_image = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$newFileName, $merchant_id])) {
                        header("Location: index.php?upload=success");
                    } else {
                        echo "Database update failed.";
                    }
                } else {
                    echo "Failed to move uploaded file. Check folder permissions.";
                }
            } else {
                echo "File is too large! Max 2MB.";
            }
        } else {
            echo "Error uploading your file.";
        }
    } else {
        echo "Invalid file type. Only JPG, PNG, and WebP allowed.";
    }
}
?>