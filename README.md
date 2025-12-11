Group 9 Global Market: Production Deployment Guide
Project: Group 9 Global Market
Infrastructure: AWS EC2 (Web Server), S3 (Image Storage), RDS (Database)
Stack: PHP, PostgreSQL, Apache

🛠 Phase 1: Infrastructure Setup (AWS Console)
Before touching the terminal, ensure these resources are ready.

1. The Server (EC2)
OS: Amazon Linux 2023.
Security Group (Firewall):
SSH (Port 22): Allowed from "My IP" (For you to manage it).
HTTP (Port 80): Allowed from 0.0.0.0/0 (Anywhere) -> Crucial for friends to view the site.
2. The Database (RDS)
Engine: PostgreSQL (Free Tier).
Connectivity: Connected to the EC2 Instance.
Action: Save the Endpoint URL and Master Password.
3. The Storage (S3)
Settings: Uncheck "Block All Public Access".
Permissions: Add this Bucket Policy so images are visible to the public:
JSON

{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicRead",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/*"
        }
    ]
}
💻 Phase 2: Server Configuration (Terminal)
Connect to your EC2 instance via SSH. Copy and run these commands.

Step 1: Install Software Stack
We need to turn the empty Linux computer into a Web Server.

Bash

# 1. Update the operating system to get the latest security patches
sudo dnf update -y

# 2. Install the required tools:
# - httpd: The Apache Web Server
# - php: The language our site is written in
# - php-pgsql: The plugin that lets PHP talk to our RDS Database
# - git: To download our code from GitHub
sudo dnf install httpd php php-pgsql git -y

# 3. Start the Apache Web Server immediately
sudo systemctl start httpd

# 4. Configure Apache to turn on automatically if the server reboots
sudo systemctl enable httpd
Step 2: Configure Permissions
We need to give our user (ec2-user) permission to modify the website files.

Bash

# 1. Add our user 'ec2-user' to the 'apache' server group
sudo usermod -a -G apache ec2-user

# 2. Change the ownership of the website folder (/var/www) to this group
sudo chown -R ec2-user:apache /var/www

# 3. Set permissions so that any new files created inherit the group rights
# (This prevents 'Permission Denied' errors later)
sudo chmod 2775 /var/www

# 4. Apply these permissions to all existing sub-folders
find /var/www -type d -exec chmod 2775 {} \;

# 5. Apply these permissions to all existing files
find /var/www -type f -exec chmod 0664 {} \;
🚀 Phase 3: Deploying the Code
Step 1: Pull from GitHub
We will replace the default server page with our Group 9 application.

Bash

# 1. Navigate to the public web directory (The "Root" of the website)
cd /var/www/html

# 2. Remove the default 'It Works!' file if it exists
rm -f index.html

# 3. Clone the repository. 
# IMPORTANT: The '.' at the end tells Git to clone HERE, not in a subfolder.
# REPLACE [YOUR_URL] with your actual GitHub HTTPS URL.
git clone https://github.com/YOUR_USERNAME/group9-market.git .
Step 2: Connect the Secrets
We didn't upload our passwords to GitHub (for security). We must enter them now manually.

Bash

# 1. Open the configuration file (might be named config.php or db_connect.php)
nano db_connect.php
Action inside Nano:

Replace DB_HOST with your RDS Endpoint.
Replace DB_PASS with your RDS Password.
Replace S3_KEY and S3_SECRET with your IAM Keys.
Save: Ctrl+O, Enter. Exit: Ctrl+X.
Step 3: Final Restart
Bash

# Restart Apache to ensure PHP loads all the new configurations
sudo systemctl restart httpd
🗄 Phase 4: Database Initialization
The code is there, but the database tables don't exist yet. We need to create them.

Bash

# 1. Connect to the AWS Database from the terminal
# Replace the URL below with your RDS Endpoint
psql --host=alfred-db.xxxx.us-east-1.rds.amazonaws.com --username=postgres --dbname=postgres

# (It will ask for your Password. Type it and press Enter.)
Once you see the postgres=> prompt, run this SQL:

SQL

CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    image_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Exit the database tool: Type \q and press Enter.
🌍 Phase 5: Go Live!
How to share with friends:
Go to the AWS Console (EC2 Dashboard).
Copy the Public IPv4 Address (e.g., 54.22.33.11).
Send this link to your group: http://54.22.33.11
(Note: Do not use https://, use http://)
