<?php
/**
 * Application configuration.
 */

declare(strict_types=1);

// Restaurant / brand details (edit to customise the site).
const APP_NAME      = 'Tasty Bites';
const APP_TAGLINE   = 'Fresh food, delivered fast.';
const CURRENCY      = '$';
const DELIVERY_FEE  = 2.50;
const TAX_RATE      = 0.08; // 8% sales tax.

// Absolute path to the SQLite database file.
define('DB_PATH', __DIR__ . '/../data/restaurant.sqlite');

// Base URL path (leave empty if served from the document root).
const BASE_URL = '';

// Order status workflow.
const ORDER_STATUSES = ['pending', 'preparing', 'out_for_delivery', 'completed', 'cancelled'];

// Start a session for cart + auth state.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
