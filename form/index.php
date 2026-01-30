<?php
require_once 'functions.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;

$result = getAllRecords($page, $perPage);
$records = $result['records'];
$totalPages = $result['pages'];
$totalRecords = $result['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Records - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-view {
            background: #3498db;
            color: white;
        }
        
        .btn-edit {
            background: #f39c12;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #34495e;
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #3498db;
            border-radius: 3px;
        }
        
        .pagination .current {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
        }
        
        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PERSONAL RECORDS MANAGEMENT</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div id="message" class="success" style="display: block; background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($_GET['message'] ?? 'Operation successful!'); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div id="message" class="error-msg" style="display: block; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                Error: <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalRecords; ?></div>
                <div class="stat-label">Total Records</div>
            </div>
        </div>

        <div class="dashboard-header">
            <a href="create.php" class="btn btn-success">+ Add New Record</a>
        </div>

        <?php if (count($records) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Contact</th>
                        <th>Civil Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo $record['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($record['last_name'] . ', ' . $record['first_name']); ?></strong>
                                <?php if ($record['middle_name']): ?>
                                    <br><small><?php echo htmlspecialchars($record['middle_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($record['dob'])); ?></td>
                            <td>
                                <?php echo htmlspecialchars($record['mobile']); ?><br>
                                <small><?php echo htmlspecialchars($record['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($record['civil_status']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['created_at'])); ?></td>
                            <td class="actions">
                                <a href="view.php?id=<?php echo $record['id']; ?>" class="btn btn-view">View</a>
                                <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="delete.php?id=<?php echo $record['id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this record? This action cannot be undone.')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>">← Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No records found</h3>
                <p>Click "Add New Record" to get started.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const msg = document.getElementById('message');
            if (msg) msg.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>