<?php
/**
 * ------------------------------------------------------------
 * Currency API Scraper
 * ------------------------------------------------------------
 * Author: Mehdi Allahverdi
 * Source Version: 1.2
 * Developed by: Lernida
 *
 * Website: https://lernida.ir
 * Telegram: https://t.me/lernida
 *
 * Description:
 * Lightweight PHP API for fetching currency prices from tgju.org.
 * Includes caching system, rate limiting, and automatic storage
 * initialization for production environments.
 *
 * Features:
 * - Automatic storage creation
 * - File based cache system
 * - IP based rate limiting
 * - Multi-currency support
 * - Clean JSON API response
 *
 * License: MIT (recommended for open source)
 * ------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');

/**
 * ------------------------------------------------------------
 * Storage Configuration
 * ------------------------------------------------------------
 * STORAGE_PATH : directory used to store cache and rate limit data
 * CACHE_FILE   : currency cache storage
 * RATE_FILE    : rate limit tracking storage
 */

define('STORAGE_PATH', __DIR__ . '/storage');
define('CACHE_FILE', STORAGE_PATH . '/cache.json');
define('RATE_FILE', STORAGE_PATH . '/rate_limit.json');

/**
 * Cache lifetime (seconds)
 * API will reuse cached data until TTL expires
 */
define('CACHE_TTL', 60);

/**
 * Rate limiting configuration
 * RATE_LIMIT  : max requests allowed
 * RATE_WINDOW : time window in seconds
 */
define('RATE_LIMIT', 30);
define('RATE_WINDOW', 60);


/**
 * ------------------------------------------------------------
 * Initialize Storage
 * ------------------------------------------------------------
 * Creates required directories and JSON files if they do not
 * exist. This allows the API to run without manual setup.
 */
function initStorage()
{
    if (!is_dir(STORAGE_PATH)) {
        mkdir(STORAGE_PATH, 0777, true);
    }

    if (!file_exists(CACHE_FILE)) {
        file_put_contents(CACHE_FILE, json_encode([]));
    }

    if (!file_exists(RATE_FILE)) {
        file_put_contents(RATE_FILE, json_encode([]));
    }
}


/**
 * ------------------------------------------------------------
 * JSON Response Helper
 * ------------------------------------------------------------
 * Sends a standard API JSON response and terminates execution.
 *
 * @param bool   $success
 * @param string $message
 * @param mixed  $data
 * @param int    $code HTTP status code
 */
function jsonResponse($success, $message, $data = null, $code = 200)
{
    http_response_code($code);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    exit;
}


/**
 * ------------------------------------------------------------
 * Read JSON File
 * ------------------------------------------------------------
 * Safely reads a JSON file and returns its contents as array.
 */
function readJson($file)
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);

    return $data ?: [];
}


/**
 * ------------------------------------------------------------
 * Write JSON File
 * ------------------------------------------------------------
 * Writes array data into a JSON file with pretty formatting.
 */
function writeJson($file, $data)
{
    file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}


/**
 * ------------------------------------------------------------
 * Rate Limiter
 * ------------------------------------------------------------
 * Limits number of requests per IP address within a time window.
 * Prevents abuse or excessive API usage.
 */
function checkRateLimit()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $data = readJson(RATE_FILE);

    $now = time();

    if (!isset($data[$ip])) {

        $data[$ip] = [
            "count" => 1,
            "start" => $now
        ];

    } else {

        if ($now - $data[$ip]['start'] > RATE_WINDOW) {

            $data[$ip] = [
                "count" => 1,
                "start" => $now
            ];

        } else {

            $data[$ip]['count']++;

            if ($data[$ip]['count'] > RATE_LIMIT) {
                jsonResponse(false, "Rate limit exceeded", null, 429);
            }

        }

    }

    writeJson(RATE_FILE, $data);
}


/**
 * ------------------------------------------------------------
 * Fetch Currency From Source
 * ------------------------------------------------------------
 * Scrapes currency information from tgju.org.
 *
 * @param string $slug Currency identifier (example: dollar)
 * @return array|null
 */
function fetchCurrency($slug)
{
    libxml_use_internal_errors(true);

    $html = @file_get_contents("https://www.tgju.org/currency");

    if (!$html) {
        return null;
    }

    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $query = '//tr[@data-market-nameslug="'.$slug.'"]';

    $name   = $xpath->query($query.'/th')->item(0)->textContent ?? null;
    $price  = $xpath->query($query.'/td')->item(0)->textContent ?? null;
    $change = $xpath->query($query.'/td')->item(1)->textContent ?? null;
    $min    = $xpath->query($query.'/td')->item(2)->textContent ?? null;
    $max    = $xpath->query($query.'/td')->item(3)->textContent ?? null;
    $date   = $xpath->query($query.'/td')->item(4)->textContent ?? null;

    if (!$name) {
        return null;
    }

    return [
        "name" => trim($name),
        "price" => trim($price),
        "change" => trim($change),
        "min" => trim($min),
        "max" => trim($max),
        "date" => trim($date),
        "updated_at" => date("Y-m-d H:i:s")
    ];
}


/**
 * ------------------------------------------------------------
 * Cache Layer
 * ------------------------------------------------------------
 * Checks cache before scraping the source website.
 * Reduces external requests and improves performance.
 */
function getCurrency($slug)
{
    $cache = readJson(CACHE_FILE);

    if (isset($cache[$slug])) {

        $age = time() - $cache[$slug]['timestamp'];

        if ($age < CACHE_TTL) {
            return $cache[$slug]['data'];
        }
    }

    $data = fetchCurrency($slug);

    if (!$data) {
        return null;
    }

    $cache[$slug] = [
        "timestamp" => time(),
        "data" => $data
    ];

    writeJson(CACHE_FILE, $cache);

    return $data;
}


/**
 * ------------------------------------------------------------
 * Application Boot
 * ------------------------------------------------------------
 */

initStorage();
checkRateLimit();


/**
 * ------------------------------------------------------------
 * Input Handling
 * Example:
 * ?n=dollar
 * ?n=dollar,eur,derham
 * ------------------------------------------------------------
 */

$param = $_GET['n'] ?? null;

if (!$param) {
    jsonResponse(false, "Currency parameter missing");
}

/**
 * Sanitize input to avoid injection or invalid characters
 */
$slugs = array_filter(array_map(function ($v) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $v);
}, explode(',', $param)));

$result = [];

/**
 * Process each requested currency
 */
foreach ($slugs as $slug) {

    $data = getCurrency($slug);

    if ($data) {
        $result[$slug] = $data;
    } else {
        $result[$slug] = "Not found";
    }

}

/**
 * Final API response
 */
jsonResponse(true, "OK", $result);
