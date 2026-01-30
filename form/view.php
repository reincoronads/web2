<?php
require_once 'functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$record = getRecordById($id);

if (!$record) {
    header("Location: index.php?error=Record not found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Record - <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .detail-group {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            width: 200px;
            font-weight: 600;
            color: #34495e;
        }
        
        .detail-value {
            flex: 1;
            color: #2c3e50;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-info {
            background: #3498db;
            color: white;
        }
        
        .section-title {
            color: #34495e;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .dependents-list {
            display: grid;
            gap: 15px;
        }
        
        .dependent-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .dependent-type {
            font-size: 12px;
            text-transform: uppercase;
            color: #3498db;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .btn-group {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background: #f39c12;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .same-address-notice {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">← Back to List</a>
        
        <h1>Personal Record Details</h1>
        
        <!-- Personal Information -->
        <div class="detail-group">
            <h2 class="section-title">A. PERSONAL DATA</h2>
            
            <div class="detail-row">
                <div class="detail-label">Full Name</div>
                <div class="detail-value">
                    <strong>
                        <?php echo htmlspecialchars($record['last_name'] . ', ' . $record['first_name']); ?>
                        <?php if ($record['middle_name']) echo htmlspecialchars(' ' . $record['middle_name']); ?>
                        <?php if ($record['suffix']) echo htmlspecialchars(', ' . $record['suffix']); ?>
                    </strong>
                    <span class="badge badge-info" style="margin-left: 10px;"><?php echo $record['sex']; ?></span>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Date of Birth</div>
                <div class="detail-value"><?php echo date('F d, Y', strtotime($record['dob'])); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Place of Birth</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($record['pob_city_municipality'] . ', ' . $record['pob_province'] . ', ' . $record['pob_country']); ?>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Home Address</div>
                <div class="detail-value">
                    <?php echo nl2br(htmlspecialchars($record['home_address'])); ?>
                    <?php if ($record['birth_same_as_home']): ?>
                        <div class="same-address-notice">
                            ℹ️ Same as Place of Birth (auto-filled)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Civil Status</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($record['civil_status']); ?>
                    <?php if ($record['civil_status'] == 'Others' && $record['civil_status_other']): ?>
                        (<?php echo htmlspecialchars($record['civil_status_other']); ?>)
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Nationality</div>
                <div class="detail-value"><?php echo htmlspecialchars($record['nationality']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Religion</div>
                <div class="detail-value"><?php echo $record['religion'] ? htmlspecialchars($record['religion']) : '<em>Not specified</em>'; ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">TIN</div>
                <div class="detail-value"><?php echo $record['tin'] ? htmlspecialchars($record['tin']) : '<em>Not provided</em>'; ?></div>
            </div>
        </div>
        
        <div class="detail-group">
            <h3 style="margin-top: 0;">Contact Information</h3>
            <div class="detail-row">
                <div class="detail-label">Mobile</div>
                <div class="detail-value"><?php echo htmlspecialchars($record['mobile']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($record['email']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Telephone</div>
                <div class="detail-value"><?php echo $record['telephone'] ? htmlspecialchars($record['telephone']) : '<em>Not provided</em>'; ?></div>
            </div>
        </div>
        
        <div class="detail-group">
            <h3 style="margin-top: 0;">Parents Information</h3>
            <div class="detail-row">
                <div class="detail-label">Father</div>
                <div class="detail-value">
                    <?php if ($record['father_last_name'] || $record['father_first_name']): ?>
                        <?php echo htmlspecialchars(trim($record['father_first_name'] . ' ' . $record['father_middle_name'] . ' ' . $record['father_last_name'] . ' ' . $record['father_suffix'])); ?>
                        <?php if ($record['father_dob']): ?>
                            <br><small>DOB: <?php echo date('M d, Y', strtotime($record['father_dob'])); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <em>Not provided</em>
                    <?php endif; ?>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Mother (Maiden Name)</div>
                <div class="detail-value">
                    <?php if ($record['mother_last_name'] || $record['mother_first_name']): ?>
                        <?php echo htmlspecialchars(trim($record['mother_first_name'] . ' ' . $record['mother_middle_name'] . ' ' . $record['mother_last_name'] . ' ' . $record['mother_suffix'])); ?>
                        <?php if ($record['mother_dob']): ?>
                            <br><small>DOB: <?php echo date('M d, Y', strtotime($record['mother_dob'])); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <em>Not provided</em>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Dependents Section -->
        <h2 class="section-title">B. DEPENDENT(S)/BENEFICIARY/IES</h2>
        
        <?php if ($record['spouse'] || count($record['children']) > 0 || count($record['beneficiaries']) > 0): ?>
            <div class="dependents-list">
                <?php if ($record['spouse']): ?>
                    <div class="dependent-card">
                        <div class="dependent-type">Spouse</div>
                        <div style="font-weight: 600;">
                            <?php echo htmlspecialchars($record['spouse']['first_name'] . ' ' . $record['spouse']['middle_name'] . ' ' . $record['spouse']['last_name']); ?>
                            <?php if ($record['spouse']['suffix']): ?>
                                <?php echo htmlspecialchars(', ' . $record['spouse']['suffix']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($record['children'] as $child): ?>
                    <div class="dependent-card">
                        <div class="dependent-type">Child</div>
                        <div style="font-weight: 600;">
                            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['middle_name'] . ' ' . $child['last_name']); ?>
                            <?php if ($child['suffix']): ?>
                                <?php echo htmlspecialchars(', ' . $child['suffix']); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($child['dob']): ?>
                            <div style="font-size: 14px; color: #666;">DOB: <?php echo date('M d, Y', strtotime($child['dob'])); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($record['beneficiaries'] as $ben): ?>
                    <div class="dependent-card">
                        <div class="dependent-type">Beneficiary</div>
                        <div style="font-weight: 600;">
                            <?php echo htmlspecialchars($ben['first_name'] . ' ' . $ben['middle_name'] . ' ' . $ben['last_name']); ?>
                        </div>
                        <div style="font-size: 14px; color: #666;">
                            Relationship: <?php echo htmlspecialchars($ben['relationship']); ?>
                            <?php if ($ben['dob']): ?> | DOB: <?php echo date('M d, Y', strtotime($ben['dob'])); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">No dependents or beneficiaries recorded.</p>
        <?php endif; ?>
        
        <div class="btn-group">
            <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn-edit">✏️ Edit Record</a>
            <a href="delete.php?id=<?php echo $record['id']; ?>" 
               class="btn-delete" 
               onclick="return confirm('Are you sure you want to delete this record?')">🗑️ Delete Record</a>
        </div>
    </div>
</body>
</html>