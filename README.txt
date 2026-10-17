AUTOMIGZ — PHP version
======================

FILES
  index.php   the website (was index.html)
  api.php     backend: sign up, log in, log out, book, list bookings, cancel
  lib.php     shared code + the car list/prices (edit cars here)
  app.js      front-end
  style.css   styles
  images/     logo.png, hero-banner.png + your car pictures (fortuner.png, ...)
  data/       created automatically on first run (SQLite database)

HOW TO RUN (pick one)
  A) XAMPP: copy this whole folder to  C:\xampp\htdocs\automigz
            start Apache, open  http://localhost/automigz/
  B) PHP only: open a terminal in this folder and run
            php -S localhost:8000
            then open  http://localhost:8000/
  NOTE: VS Code "Live Server" (127.0.0.1:5500) can NOT run PHP.

DATABASE (MySQL / phpMyAdmin)
  1. Open the XAMPP Control Panel and click Start next to BOTH Apache and MySQL.
  2. Settings are in config.php (XAMPP defaults: user root, empty password,
     database "automigz"). Change them only if you set a MySQL password.
  3. Open http://localhost/automigz/  and sign up. The database "automigz" and its
     tables (users, bookings) are created automatically on first use.
  4. See your data: http://localhost/phpmyadmin  ->  automigz  ->  users / bookings.
  5. Problems? Open  http://localhost/automigz/check.php  (delete it afterwards).
  database.sql is optional - only if you want to import the tables by hand.

REQUIREMENTS
  PHP 8.0 or newer with the pdo_mysql extension (on by default in XAMPP).
