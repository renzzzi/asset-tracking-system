<?php
// config/constants.php

define('APP_NAME', 'AssetTrack');
define('PAGINATION_LIMIT', 20);
define('BASE_URL', 'http://localhost/asset-tracking');

// Application-wide fixed definitions
const ASSET_STATUSES = [
    'active',
    'under_repair',
    'disposed',
    'lost'
];