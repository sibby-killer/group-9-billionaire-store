# Group 9 Global Market Application

A PHP-based product management dashboard for 'Group 9 Global Market' that integrates with AWS Services (RDS, S3).

## Features
- **Dashboard**: View all products in a responsive grid.
- **Add Product**: Upload product images and details.
- **AWS S3 Integration**: Direct upload to S3 using raw PHP `curl` (No SDK/Composer required).
- **AWS RDS Integration**: Stores product metadata in a PostgreSQL database.
- **Design**: Premium "Real Estate" aesthetic with Dark Mode & Gold accents.

---

## 🚀 AWS Deployment Guide (Step-by-Step)

Follow these steps to deploy the application on AWS EC2 with RDS and S3.

### Prerequisites
- An AWS Account.
- **RDS**: A PostgreSQL database instance running.
- **S3**: A bucket created for storing images.

### Step 1: Launch an EC2 Instance
1.  Go to **EC2 Dashboard** > **Launch Instance**.
2.  **Name**: `Group9-WebServer`.
3.  **OS**: Amazon Linux 2023 (or Ubuntu).
4.  **Instance Type**: `t2.micro` (Free tier eligible).
5.  **Key Pair**: Create or select an existing key pair (to SSH later).
6.  **Security Group**: Allow HTTP (80), HTTPS (443), and SSH (22).
7.  Launch the instance.

### Step 2: Install Web Server & PHP
SSH into your EC2 instance and run the following commands:

```bash
# Update system
sudo yum update -y

# Install Apache (httpd) and PHP
sudo yum install -y httpd php php-pgsql php-mbstring php-xml

# Start Apache and enable it on boot
sudo systemctl start httpd
sudo systemctl enable httpd
```

### Step 3: Configure Permissions
Ensure the web server can read your files.

```bash
# Add ec2-user to the apache group
sudo usermod -a -G apache ec2-user

# Change ownership of the web root
sudo chown -R ec2-user:apache /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec chmod 2775 {} \;
find /var/www -type f -exec chmod 0664 {} \;
```

### Step 4: Deploy the Code
Clone this repository directly into the web root.

```bash
cd /var/www/html
git clone https://github.com/sibby-killer/group-9-billionaire-store.git .
```

### Step 5: Configure Database & S3
1.  **Edit the Configuration File**:
    ```bash
    nano db_connect.php
    ```
2.  **Update Credentials**:
    Replace the placeholder values with your actual AWS keys and endpoints:
    ```php
    $host = 'your-rds-endpoint.us-east-1.rds.amazonaws.com';
    $user = 'postgres';
    $pass = 'your_password';
    $bucket_name = 'your-s3-bucket-name';
    $access_key  = 'AKIA...';
    $secret_key  = '...';
    ```
3.  **Save & Exit**: Press `Ctrl+O`, `Enter`, then `Ctrl+X`.

### Step 6: Initialize the Database
Connect to your RDS instance (you can use a tool like pgAdmin or command line) and run this SQL:

```sql
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 7: Configure AWS Security
1.  **S3 Bucket Policy**: Ensure your S3 bucket allows public read access for images (or use CloudFront).
    *   *Uncheck "Block all public access"* in S3 Permissions.
    *   Add this Bucket Policy (replace `YOUR-BUCKET-NAME`):
    ```json
    {
        "Version": "2012-10-17",
        "Statement": [
            {
                "Sid": "PublicReadGetObject",
                "Effect": "Allow",
                "Principal": "*",
                "Action": "s3:GetObject",
                "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/*"
            }
        ]
    }
    ```
2.  **CORS Configuration** (if needed):
    ```json
    [
        {
            "AllowedHeaders": ["*"],
            "AllowedMethods": ["PUT", "POST", "GET"],
            "AllowedOrigins": ["*"],
            "ExposeHeaders": []
        }
    ]
    ```

### Step 8: Access Your App
Open your browser and visit your EC2 Public IP address:
`http://YOUR-EC2-PUBLIC-IP/`

---

## Local Development
To run locally without Apache:
```bash
php -S localhost:8000
```
Then visit `http://localhost:8000`.
