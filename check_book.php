<?php
require_once __DIR__ . '/config/database.php';

$db = (new Database())->getConnection();

// Let's find the book by its title
$query = "SELECT id, titulo, portada FROM libros WHERE titulo LIKE '%Quimica%' OR titulo LIKE '%Química%'";
$stmt = $db->query($query);
$book = $stmt->fetch();

if ($book) {
    echo "Book found!<br>";
    echo "ID: " . htmlspecialchars($book['id']) . "<br>";
    echo "Title: " . htmlspecialchars($book['titulo']) . "<br>";
    echo "Portada value: " . htmlspecialchars($book['portada']) . "<br>";
    
    // Check if file exists
    $imagePath = __DIR__ . '/img/portadas/' . $book['portada'];
    echo "Image path: $imagePath<br>";
    echo "File exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "<br>";
    
    // Display the image if it exists
    if (file_exists($imagePath)) {
        echo '<img src="img/portadas/' . htmlspecialchars($book['portada']) . '" style="max-width: 200px;">';
    }
} else {
    echo "No book found with 'Quimica' in the title.";
}

// Also show the first few books to check the data
$query = "SELECT id, titulo, portada FROM libros LIMIT 5";
$stmt = $db->query($query);

echo "<h2>First 5 books in database:</h2>";
echo "<pre>";
foreach ($stmt->fetchAll() as $book) {
    print_r($book);
}
echo "</pre>";
?>
