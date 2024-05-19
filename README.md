
# Insider Champions League Project

This project is a Laravel 11 application that uses Vite for asset management. Follow the instructions below to set up and run the project.

## Prerequisites

Ensure you have the following installed on your machine:

- [PHP 8.2+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Node.js](https://nodejs.org/) and npm (Node Package Manager)

## Installation

1. **Clone the repository:**

    ```sh
    git clone https://github.com/oguz463/insider.git
    cd insider
    ```

2. **Install PHP dependencies:**

    ```sh
    composer install
    ```

3. **Install JavaScript dependencies:**

    ```sh
    npm install
    ```

4. **Copy the `.env` file and set up environment variables:**

    ```sh
    cp .env.example .env
    ```

    Edit the `.env` file to match your database and other environment configurations.

5. **Generate an application key:**

    ```sh
    php artisan key:generate
    ```

6. **Run database migrations:**

    ```sh
    php artisan migrate
    ```

7. **Run database seeders:**

    ```sh
    php artisan db:seed
    ```

## Running the Application

1. **Start the development server:**

    ```sh
    php artisan serve
    ```

    This will start the server at `http://localhost:8000`.

2. **Run Vite:**

    In a separate terminal, run:

    ```sh
    npm run dev
    ```

    This will start Vite and watch your assets for changes.

## Running Tests

1. **Run feature and unit tests:**

    ```sh
    php artisan test
    ```

## Additional Commands

- **Clear application cache:**

    ```sh
    php artisan cache:clear
    ```

- **Clear configuration cache:**

    ```sh
    php artisan config:clear
    ```

- **Clear route cache:**

    ```sh
    php artisan route:clear
    ```

- **Clear view cache:**

    ```sh
    php artisan view:clear
    ```

## Deployment

1. **Build assets for production:**

    ```sh
    npm run build
    ```

2. **Run database migrations in production:**

    ```sh
    php artisan migrate --force
    ```

## Troubleshooting

- If you encounter any issues with permissions, you may need to adjust the permissions of the storage and bootstrap/cache directories:

    ```sh
    sudo chown -R $USER:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
    ```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
