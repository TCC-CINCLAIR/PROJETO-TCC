<?php
$conn = new PDO ("pgsql:host = aws-1-us-east-2.pooler.supabase.com;dbname=postgres","postgres.oiqkryvqyoxjyaujrtcl", "cinclair@2026");
    $conn -> setAttribute (
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
?>