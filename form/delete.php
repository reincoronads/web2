<?php
// delete.php - Delete handler with proper error handling
require_once 'functions.php';

// Ensure no output before headers (no whitespace before <?php above)
ob_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=No ID specified");
    exit();
}

$id = intval($_GET['id']);

if ($id <= 0) {
    header("Location: index.php?error=Invalid ID");
    exit();
}

try {
    $pdo = getDB();
    
    // Check if record exists first
    $checkStmt = $pdo->prepare("SELECT id FROM personal_data WHERE id = ?");
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() === 0) {
        header("Location: index.php?error=Record not found");
        exit();
    }
    
    // Delete record (dependents will cascade if foreign key is set up correctly)
    $stmt = $pdo->prepare("DELETE FROM personal_data WHERE id = ?");
    $success = $stmt->execute([$id]);
    
    if ($success && $stmt->rowCount() > 0) {
        header("Location: index.php?success=1&message=Record deleted successfully");
    } else {
        header("Location: index.php?error=Record could not be deleted");
    }
    
} catch (PDOException $e) {
    // Handle specific database errors
    $errorMsg = "Database error: " . $e->getMessage();
    
    // Check for foreign key constraint errors
    if (strpos($e->getMessage(), 'foreign key') !== false || strpos($e->getMessage(), '1451') !== false) {
        $errorMsg = "Cannot delete record because it has related data. Check database constraints.";
    }
    
    header("Location: index.php?error=" . urlencode($errorMsg));
} catch (Exception $e) {
    header("Location: index.php?error=" . urlencode($e->getMessage()));
}

exit();
?>