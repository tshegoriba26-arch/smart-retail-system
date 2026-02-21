<?php
// check_images.php
echo "<h2>Image File Check</h2>";

$images = [
    'iphone15.jpg',
    's24.jpg', 
    'macbook.png',
    'dell.jpg',
    'headphone.jpg',
    'watch.jpg',
    'book.jpg',
    'cotton.jpg',
    'placeholder.jpg'
];

foreach ($images as $image) {
    $path = "images/$image";
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p style='color: green;'>? $image - " . round($size/1024) . " KB</p>";
        echo "<img src='$path' style='max-width: 100px; max-height: 100px; margin: 5px; border: 1px solid #ccc;'>";
    } else {
        echo "<p style='color: red;'>? $image - MISSING</p>";
    }
}

echo "<p><a href='index.php'>Back to Homepage</a></p>";
?>