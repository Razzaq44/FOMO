<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOMO API - Documentation & Overview</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1a202c;
            background-color: #ffffff;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.5px;
            margin: 30px 0 15px;
            border-left: 4px solid;
            padding-left: 15px;
        }

        p {
            margin-bottom: 20px;
        }

        pre {
            background: #2d3748;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: #FF2D20;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>FOMO API</h1>
            <a href="/docs/api" class="btn">Buka Dokumentasi API</a>
        </header>

        <section>
            <h2>Quick Start</h2>
            <p>Jalankan perintah berikut untuk menyiapkan lingkungan pengembangan Anda:</p>
            <pre>
# Install dependencies
composer install

# Setup database & dummy data
php artisan migrate --seed
php artisan db:seed --class=ProductSeeder

# Jalankan server
php artisan serve
            </pre>
        </section>
    </div>
</body>

</html>