<?php
// Copy to config.php on the server. Never commit real credentials.
return [
    'db_host' => 'localhost',
    'db_name' => 'YOUR_DATABASE_NAME',
    'db_user' => 'YOUR_DATABASE_USER',
    'db_pass' => 'YOUR_DATABASE_PASSWORD',
    // Exact HTTPS origin of the app website, e.g. https://realwin.example.
    // Leave empty if the Android app calls the API directly.
    'allowed_origin' => '',
];
