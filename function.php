<?php
function isActive($page) {
    return basename($_SERVER['REQUEST_URI'], ".php") == $page ? 'active' : '';
}
function generateUniqueFileName($extension) {

    $timestamp = date('Y-m-d_H-i-s'); 
    $randomNumber = rand(1000, 9999);
    
    $uniqueFileName = "{$timestamp}_{$randomNumber}.{$extension}";

    return $uniqueFileName;
}
function displayStars($number) {
    $number = max(1, min(5, $number)); 
    return str_repeat('⭐', $number);
}
function randomNumberString() {
    $numbers = '';
    for ($i = 0; $i < 5; $i++) {
        $numbers .= rand(0, 9);
    }
    return $numbers;
}

// Thêm vào function.php
function show404() {
    header("HTTP/1.0 404 Not Found");
    include "pages/404.php";
    exit();
}
?>