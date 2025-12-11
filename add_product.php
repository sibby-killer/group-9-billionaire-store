<?php
require 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        
        // S3 Upload Function
        $s3_url = uploadToS3($file_tmp, $file_name, $bucket_name, $region, $access_key, $secret_key);
        
        if ($s3_url) {
            // Insert into Database
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $description, $price, $s3_url])) {
                $message = "Product added successfully!";
            } else {
                $message = "Database error.";
            }
        } else {
            $message = "Failed to upload image to S3.";
        }
    } else {
        $message = "Please select an image.";
    }
}

function uploadToS3($file_path, $key, $bucket, $region, $access_key, $secret_key) {
    $host_name = "$bucket.s3.amazonaws.com";
    $url = "https://$host_name/$key";
    $content = file_get_contents($file_path);
    $content_type = mime_content_type($file_path);
    
    // AWS Signature V4
    $service = 's3';
    $timestamp = gmdate('Ymd\THis\Z');
    $date_stamp = gmdate('Ymd');
    
    // 1. Canonical Request
    $canonical_uri = "/$key";
    $canonical_querystring = "";
    $canonical_headers = "host:$host_name\nx-amz-content-sha256:" . hash('sha256', $content) . "\nx-amz-date:$timestamp\n";
    $signed_headers = "host;x-amz-content-sha256;x-amz-date";
    $payload_hash = hash('sha256', $content);
    
    $canonical_request = "PUT\n$canonical_uri\n$canonical_querystring\n$canonical_headers\n$signed_headers\n$payload_hash";
    
    // 2. String to Sign
    $algorithm = "AWS4-HMAC-SHA256";
    $credential_scope = "$date_stamp/$region/$service/aws4_request";
    $string_to_sign = "$algorithm\n$timestamp\n$credential_scope\n" . hash('sha256', $canonical_request);
    
    // 3. Calculate Signature
    $kSecret = "AWS4" . $secret_key;
    $kDate = hash_hmac('sha256', $date_stamp, $kSecret, true);
    $kRegion = hash_hmac('sha256', $region, $kDate, true);
    $kService = hash_hmac('sha256', $service, $kRegion, true);
    $kSigning = hash_hmac('sha256', "aws4_request", $kService, true);
    $signature = hash_hmac('sha256', $string_to_sign, $kSigning);
    
    // 4. Authorization Header
    $authorization = "$algorithm Credential=$access_key/$credential_scope, SignedHeaders=$signed_headers, Signature=$signature";
    
    // Execute Curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $authorization",
        "x-amz-date: $timestamp",
        "x-amz-content-sha256: $payload_hash",
        "Content-Type: $content_type"
    ]);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return $url;
    } else {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Group 9 Global Market</title>
    <style>
        body {
            background-color: #121212;
            color: #d4af37;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 500px;
            border: 1px solid #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
            border-bottom: 1px solid #d4af37;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #aaa;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            background-color: #2c2c2c;
            border: 1px solid #444;
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #d4af37;
            outline: none;
        }
        button {
            width: 100%;
            padding: 15px;
            background-color: #d4af37;
            color: #121212;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            text-transform: uppercase;
        }
        button:hover {
            background-color: #b5952f;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link:hover {
            color: #d4af37;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Product</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit">Upload Product</button>
        </form>
        <a href="index.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
