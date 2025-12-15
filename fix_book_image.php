<?php
require_once __DIR__ . '/config/database.php';

// Connect to database
$db = (new Database())->getConnection();

// Find the book by title (using LIKE to handle potential encoding issues)
$query = "SELECT id, titulo, portada FROM libros WHERE titulo LIKE '%Quimica%' OR titulo LIKE '%Química%' LIMIT 1";
$stmt = $db->query($query);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if ($book) {
    echo "Found book:<br>";
    echo "ID: " . htmlspecialchars($book['id']) . "<br>";
    echo "Title: " . htmlspecialchars($book['titulo']) . "<br>";
    echo "Current portada value: " . htmlspecialchars($book['portada']) . "<br>";
    
    // The actual filename on disk (with special characters)
    $actualFilename = "Química General, orgánica y biológica. Estructuras de la vida.png";
    
    // Check if the file exists in portadas directory
    $imagePath = __DIR__ . '/img/portadas/' . $actualFilename;
    
    if (file_exists($imagePath)) {
        echo "Image found at: " . htmlspecialchars($imagePath) . "<br>";
        
        // Update the database with the correct filename
        $updateQuery = "UPDATE libros SET portada = :portada WHERE id = :id";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute([
            ':portada' => $actualFilename,
            ':id' => $book['id']
        ]);
        
        echo "Database updated with correct filename.<br>";
        
        // Test the image URL
        $testUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/biblioteca-proyectofinal/img/portadas/' . rawurlencode($actualFilename);
        echo "Test image URL: <a href='" . htmlspecialchars($testUrl) . "' target='_blank'>" . htmlspecialchars($testUrl) . "</a><br>";
        echo "<img src='" . htmlspecialchars($testUrl) . "' style='max-width: 200px;'><br>";
        
    } else {
        echo "Image not found at: " . htmlspecialchars($imagePath) . "<br>";
        echo "Please make sure the image exists in the portadas directory.<br>";
        
        // Show contents of portadas directory
        $portadasDir = __DIR__ . '/img/portadas/';
        if (is_dir($portadasDir)) {
            echo "Files in portadas directory:<br>";
            $files = scandir($portadasDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo htmlspecialchars($file) . "<br>";
                }
            }
        }
    }
    
} else {
    echo "No book found with 'Quimica' in the title.";
    
    // Show all books for reference
    $query = "SELECT id, titulo, portada FROM libros LIMIT 10";
    $stmt = $db->query($query);
    echo "<br><br>First 10 books in database:<br>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo htmlspecialchars($row['id'] . ": " . $row['titulo'] . " (portada: " . $row['portada'] . ")<br>");
    }
}
?>
