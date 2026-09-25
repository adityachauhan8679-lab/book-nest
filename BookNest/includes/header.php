<?php
/**
 * BookNest - Global Header Include
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'BookNest - Find your next great read';
$activeNav = $activeNav ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="BookNest is a modern online bookstore featuring smart categorization, verified customer reviews, and physical book scanning.">
  
  <!-- Google Web Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
