<?php
require 'db.php';
session_start();

// Upload handler for the admin panel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    // Connect to database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get form data
    $title = $_POST['title'];
    $category = $_POST['category'];
    $display_order = $_POST['display_order'];
    
    // File upload handling
    $target_dir = "uploads/";
    
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
    
    // Generate a unique filename
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Check if image file is an actual image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    
    // Check file size (5MB max)
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            
            // Insert file info into database
            $image_path = $target_file; // Store the relative path
            
            $stmt = $conn->prepare("INSERT INTO gallery_images (image_path, title, category, display_order) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $image_path, $title, $category, $display_order);
            
            if ($stmt->execute()) {
                header("Location: gallery_admin.php?message=Image uploaded successfully!");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
            
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Image - Hungry Potter Kim's Tapsilogan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background: linear-gradient(180deg, rgba(255, 207, 207, 1) 0%, rgba(255, 252, 252, 1) 100%);
            color: #333;
        }
        
        h1 {
            color: #980000;
            font-family: "Playfair Display", serif;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        button, .btn {
            background-color: #980000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        
        button:hover, .btn:hover {
            background-color: #6e0606;
        }
        
        .back-link {
            display: block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload New Image</h1>
        
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fileToUpload">Select Image:</label>
                <input type="file" name="fileToUpload" id="fileToUpload" required>
            </div>
            
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="current">Current Month</option>
                    <option value="last_month">Last Month</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="display_order">Display Order:</label>
                <input type="number" id="display_order" name="display_order" min="1" max="12" value="1" required>
            </div>
            
            <button type="submit" name="upload_image">Upload Image</button>
            <a href="gallery_admin.php" class="btn back-link">Back to Admin</a>
        </form>
    </div>
</body>
</html>