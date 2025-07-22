# TraselFramework

A simple PHP MVC framework with routing, controllers, views, and database migrations.

## Requirements

- PHP 7.4+
- Composer
- PostgreSQL

## Setup

1. **Clone the repository**

   ```sh
   git clone https://github.com/yourusername/TraselFramework.git
   cd TraselFramework
   ```

2. **Install dependencies**

   ```sh
   composer install
   ```

3. **Configure environment variables**

   - Copy `.env-exemple` to `.env` in the `app/` directory and update the values as needed:

     ```sh
     cp app/.env-exemple app/.env
     ```

   - Edit `app/.env` with your database credentials.

4. **Run database migrations**

   - To create a new migration:

     ```sh
     composer make:migration MigrationName
     ```

   - To apply migrations:

     ```sh
     composer migrate
     ```

   - To rollback the last batch of migrations:

     ```sh
     composer migrate:rollback
     ```

5. **Start the development server**

   ```sh
   php -S localhost:8000 -t app
   ```

   Visit [http://localhost:8000](http://localhost:8000) in your browser.

## Project Structure

- `app/Controllers/` - Controller classes
- `app/Helpers/` - Core helpers (Database, Routes, View)
- `app/Models/` - Models (empty by default)
- `app/Routes/` - Route definitions (`web.php`, `api.php`)
- `app/Views/` - View templates
- `app/database/migrations/` - Migration files
- `app/index.php` - Entry point
- `vendor/` - Composer dependencies

## Usage

- Define routes in [`app/Routes/web.php`](app/Routes/web.php) and [`app/Routes/api.php`](app/Routes/api.php).
- Create controllers in [`app/Controllers/`](app/Controllers/).
- Add views in [`app/Views/`](app/Views/).

## ORM

   - To create an Model:

     ```sh
     composer make:model ModelName
     ```

## License

This project is licensed under the [Apache 2.0 License](LICENSE)