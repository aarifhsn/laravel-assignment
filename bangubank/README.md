# BanguBank

**BanguBank** is a simple banking application built as part of an assignment. The application supports two types of users: **Admin** and **Customer**. It features functionalities such as user registration, login, and basic banking operations like deposits, withdrawals, and transfers. The project uses an MVC structure with support for both file and MySQL database storage.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Folder Structure](#folder-structure)

## Features

- **User Authentication**: Registration and login for both Admin and Customer.
- **Admin Panel**: Manage users, view all transactions.
- **Customer Panel**: Perform banking operations like deposits, withdrawals, and transfers.

## Installation

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL (if using database storage)
- Web Server (Apache, Nginx, etc.)

### Steps

1. **Clone the repository**:

   ```bash
   git clone https://github.com/aarifhsn/laravel-assignment.git
   cd laravel-assignment/bangubank

   ```

2. **Install dependencies**:

   ```bash
   composer install
   ```

3. **Create Admin**
   ```bash
   php create-admin.php
   ```
