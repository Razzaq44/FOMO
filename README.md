# FOMO API

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel 11">
    <img src="https://img.shields.io/badge/PostgreSQL-latest-blue?style=for-the-badge&logo=postgresql" alt="PostgreSQL">
</p>

FOMO adalah backend API e-commerce yang dibangun dengan Laravel 11. Proyek ini mencakup fitur manajemen produk, keranjang belanja, dan sistem checkout yang aman dengan dukungan Flash Sale.

## 🚀 Fitur Utama

- **Authentication**: Laravel Sanctum (Bearer Token).
- **Product Catalog**: Pagination, Search, & Price Filtering.
- **Cart System**: Sync items across devices.
- **Checkout**: Race-condition safe with `lockForUpdate`.
- **API Docs**: Automatic documentation via Scramble.

## ⚙️ Instalasi

1. Install dependencies: `composer install`
2. Configure `.env` for PostgreSQL.
3. Run migrations & seeders:
   ```bash
   php artisan migrate --seed
   ```
4. Serve: `php artisan serve`

## 📖 Dokumentasi

Akses dokumentasi API di: `http://localhost:8000/docs/api`

---
© 2026 FOMO Project.
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
