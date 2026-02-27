<?php
// Define database host (domain or IP address)
$DB_HOST = 'mysql-logindb.alwaysdata.net';

// Define the port used for the MySQL server (default is 3306)
$DB_PORT = '3306';

// Define the name of the database to connect to
$DB_NAME = 'logindb_studentportal';

// Define the username used to authenticate with the database
$DB_USER = 'logindb_user';

// Define the password associated with the database user
$DB_PASSWORD = 'Hope@5@6';

// Create a new PDO (PHP Data Object) instance for database connection
// This connects to MySQL using host, port, dbname, and utf8 charset
$pdo = new PDO(
    "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8",
    $DB_USER,
    $DB_PASSWORD
);

// NOTE:
// - This connection does NOT enable error reporting or strict mode.
// - Consider adding:
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//   to catch database errors during development.
?>
