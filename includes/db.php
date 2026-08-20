<?php
/**
 * Seychelles International Cargo LLC - Database Connection & Initialization
 * Uses PDO SQLite for zero-configuration, ultra-fast persistence.
 */

define('DB_PATH', __DIR__ . '/../data/database.sqlite');

function get_db_connection() {
    static $db = null;
    if ($db === null) {
        $data_dir = __DIR__ . '/../data';
        if (!file_exists($data_dir)) {
            mkdir($data_dir, 0755, true);
            // Protect data directory with htaccess
            file_put_contents($data_dir . '/.htaccess', "Deny from all\n");
        }
        
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Enable WAL mode for high concurrency
            $db->exec('PRAGMA journal_mode = WAL;');
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }
    return $db;
}

/**
 * Initialize Database Schema Automatically
 */
function init_database() {
    $db = get_db_connection();
    
    // Users Table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
    
    // Quotes Table
    $db->exec("CREATE TABLE IF NOT EXISTS quotes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        contact TEXT NOT NULL,
        origin TEXT NOT NULL,
        destination TEXT NOT NULL,
        departure_date TEXT,
        status TEXT DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
    
    // Enquiries Table
    $db->exec("CREATE TABLE IF NOT EXISTS enquiries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        firstname TEXT NOT NULL,
        lastname TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        place TEXT,
        message TEXT NOT NULL,
        status TEXT DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Contact Submissions Table
    $db->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        firstname TEXT NOT NULL,
        lastname TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        message TEXT NOT NULL,
        status TEXT DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Settings Table
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        setting_value TEXT NOT NULL
    );");

    // Vessel Schedules Table
    $db->exec("CREATE TABLE IF NOT EXISTS vessel_schedules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vessel_name TEXT NOT NULL,
        voyage_no TEXT NOT NULL,
        destination TEXT NOT NULL,
        etd_date TEXT NOT NULL,
        eta_date TEXT NOT NULL,
        cutoff_date TEXT NOT NULL,
        status TEXT DEFAULT 'Booking Open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // Seed default admin user if none exists
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $default_user = 'admin';
        $default_pass = password_hash('Admin@Seychelles2026!', PASSWORD_DEFAULT);
        $insert = $db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $insert->execute([$default_user, $default_pass]);
    }

    // Seed default vessel schedules if none exist
    $v_stmt = $db->query("SELECT COUNT(*) FROM vessel_schedules");
    if ($v_stmt->fetchColumn() == 0) {
        $seed_schedules = [
            ['Seychelles Express V.204', 'SY2026-08', 'Seychelles', '2026-08-25', '2026-09-02', '2026-08-23', 'Booking Open'],
            ['Mauritius Trader V.108', 'MT2026-12', 'Mauritius', '2026-08-28', '2026-09-07', '2026-08-26', 'Booking Open'],
            ['Zanzibar Voyager V.045', 'ZV2026-03', 'Zanzibar', '2026-08-30', '2026-09-08', '2026-08-27', 'Booking Open'],
            ['Comoros Carrier V.012', 'CC2026-05', 'Comoros', '2026-09-02', '2026-09-12', '2026-08-30', 'Booking Open']
        ];
        $v_insert = $db->prepare("INSERT INTO vessel_schedules (vessel_name, voyage_no, destination, etd_date, eta_date, cutoff_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($seed_schedules as $s) {
            $v_insert->execute($s);
        }
    }
}

// Auto-initialize on include
init_database();
