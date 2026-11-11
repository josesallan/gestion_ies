<?php
// public/test_import.php

header('Content-Type: text/html; charset=utf-8');

echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; font-family: monospace; border-radius: 8px;'>";

require_once __DIR__ . '/../scripts/import_penalara_xml.php';

echo "</pre>";