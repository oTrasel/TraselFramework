# TraselFramework

A simple, fast PHP MVC framework featuring routing, controllers, views, database migrations, and middleware support.

---

## Requirements

- PHP 7.4 or higher
- Composer
- PostgreSQL

---

## Getting Started

### 1. Clone the Repository

```sh
git clone https://github.com/yourusername/TraselFramework.git
cd TraselFramework
```

### 2. Install Dependencies

```sh
composer install
```

### 3. Configure Environment Variables

- Copy the example environment file and update your settings:

  ```sh
  cp app/.env-exemple app/.env
  ```

- Edit `app/.env` with your database credentials and other settings.

### 4. Run Database Migrations

- **Create a new migration:**

  ```sh
  composer make:migration MigrationName
  ```

- **Apply migrations:**

  ```sh
  composer migrate
  ```

- **Rollback the last batch of migrations:**

  ```sh
  composer migrate:rollback
  ```

### 5. Start the Development Server

```sh
php -S localhost:8000 -t app
```

Visit [http://localhost:8000](http://localhost:8000) in your browser.

---

## Project Structure

```
app/
├── Controllers/         # Controller classes
├── Helpers/             # Core helpers (Database, Routes, View)
├── Middlewares/         # Middleware classes
├── Models/              # Eloquent-style models
├── Routes/              # Route definitions (web.php, api.php)
├── Views/               # View templates
├── database/            # Migration files
├── .env                 # Environment variables
└── index.php            # Application entry point
vendor/                  # Composer dependencies
```

---

## Usage

- **Define routes:**  
  In [`app/Routes/web.php`](app/Routes/web.php) and [`app/Routes/api.php`](app/Routes/api.php).

- **Create controllers:**  
  In [`app/Controllers/`](app/Controllers/).

- **Add views:**  
  In [`app/Views/`](app/Views/).

---

## ORM

- **Create a model:**

  ```sh
  composer make:model ModelName
  ```

---

## Middlewares

Middlewares allow you to filter or modify HTTP requests before they reach your controllers (e.g., authentication, logging).

### Creating a Middleware

1. Create a new PHP class in `app/Middlewares/`, e.g., `AuthMiddleware.php`:

   ```php
   <?php
   namespace Middlewares;

   class AuthMiddleware
   {
       public function handle($request)
       {
           // Example: Check authentication
           if (!isset($request['user'])) {
               header('Location: /login');
               exit;
           }
           return $request;
       }
   }
   ```

2. Register your middleware in the route definition (e.g., in `app/Routes/web.php`):

   ```php

   Routes::get('example', 'HomeController@index', ['TestMiddleware']);
   ```

- You can add multiple middlewares as an array:

   ```php
   Routes::get('example', 'HomeController@index', ['TestMiddleware', 'ExampleMiddleware']);
   ```

---

## License

This project is licensed under the [Apache 2.0 License](LICENSE)