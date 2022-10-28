
# Todo Application

Note: This application is built with the assumption of having frontend stack such as reactjs, angularjs or vuejs with social login library to obtain the accessToken. Hence, this laravel application is only handling for token validation with respective social platform through API interface.

1. Navigate to the project root folder and run the following commands in order:

> composer install
> cp .env.example .env

2. Update database credential in **ENV** file

3. In the project root folder, run the following command in order:
> php artisan key:generate
> php artisan migrate
> php artisan passport:install

4. Goto config/services.php, find the following syntax and edit the client_id and client_secret to your own.

   'github' => [  
   'client_id' => env('GITHUB_CLIENT_ID'),  
   'client_secret' => env('GITHUB_CLIENT_SECRET'),  
   'redirect' => 'http://example.com/callback-url',  
   ],

5. After user obtained social access token from frontend stack, send the social access token to URL below. Additionally, the URL below will:
- [x] User will be created if not exist
- [x] The {platform} parameter could be facebook/github/apple and etc based on config/services.php file.
- [x] Validate social access token
- [x] Issue Bearer token to the user
> domain/api/auth/social/{platform}/callback


Structure:
app/Http/Repositories - SQL queries are handling in this folders
app/Http/Controllers - Common application logic
routes/api.php - URL pattern
