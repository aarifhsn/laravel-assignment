# BanguBank

**BanguBank** is a simple banking application built as part of an assignment. The application supports two types of users: **Admin** and **Customer**. It features functionalities such as user registration, login, and basic banking operations like deposits, withdrawals, and transfers. The project uses an MVC structure with support for both file and MySQL database storage.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)

## Features

- **User Authentication**: Registration and login for both Admin and Customer.
- **Admin Panel**: Manage users, view all transactions.
- **Customer Panel**: Perform banking operations like deposits, withdrawals, and transfers.

## Installation

To set up this project locally, follow these steps:

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL (if using database storage)
- Web Server (Apache, Nginx, etc.)
- Must have permission to access all files as chown(basically for linux users)

### Steps

1. **Clone the repository**:

   ```bash
   git clone https://github.com/aarifhsn/laravel-assignment.git
   ```

2. **Navigate to the project directory:**

   ```bash
   cd assignment5/bangubank
   ```

3. **Database Creation**

   After installation the project, a database called bankofbangu and tables will be created automatically.

4. **Install dependencies**:

   ```bash
   composer install
   ```

5. **Access the application:**

   Open your browser and go to:

   ```bash
   http://localhost/your-directory/laravel-assignment/assignment5/bangubank
   ```

   check the directory where you installed the file.

## Configuration

- navigate to your-directory/laravel-assignment/assignment5/bangubank/app/config/config.php
- Check the $storageMethod = 'database';
- Update database to file, if you want to use file storage. default is database.

6. **Create Admin**
   ```bash
   php create-admin.php
   ```
