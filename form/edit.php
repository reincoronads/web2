<?php
require_once 'functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$record = getRecordById($id);

if (!$record) {
    header("Location: index.php?error=Record not found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        $pdo->beginTransaction();
        
        // Validation
        $required_fields = [
            'last_name' => 'Last Name',
            'first_name' => 'First Name',
            'dob' => 'Date of Birth',
            'sex' => 'Sex',
            'civil_status' => 'Civil Status',
            'nationality' => 'Nationality',
            'pob_city_municipality' => 'Place of Birth (City/Municipality)',
            'pob_province' => 'Place of Birth (Province)',
            'mobile' => 'Mobile Number',
            'email' => 'Email Address'
        ];

        $errors = validateRequiredFields($required_fields, $_POST);
        if (!empty($errors)) {
            throw new Exception(implode(" ", $errors));
        }

        // Handle Home Address
        $birth_same_as_home = isset($_POST['same_as_home']) && $_POST['same_as_home'] == '1' ? 1 : 0;
        
        if ($birth_same_as_home) {
            $home_address = formatAddressFromPOB(
                sanitize($_POST['pob_city_municipality']),
                sanitize($_POST['pob_province']),
                sanitize($_POST['pob_country'] ?? 'Philippines')
            );
        } else {
            if (empty($_POST['home_address'])) {
                throw new Exception("Home Address is required when not same as Place of Birth");
            }
            $home_address = sanitize($_POST['home_address']);
        }

        $data = [
            'last_name' => sanitize($_POST['last_name']),
            'first_name' => sanitize($_POST['first_name']),
            'middle_name' => sanitize($_POST['middle_name'] ?? ''),
            'suffix' => sanitize($_POST['suffix'] ?? ''),
            'dob' => sanitize($_POST['dob'], 'date'),
            'sex' => sanitize($_POST['sex']),
            'civil_status' => sanitize($_POST['civil_status']),
            'civil_status_other' => sanitize($_POST['civil_status_other'] ?? ''),
            'tin' => sanitize($_POST['tin'] ?? ''),
            'nationality' => sanitize($_POST['nationality']),
            'religion' => sanitize($_POST['religion'] ?? ''),
            'pob_city_municipality' => sanitize($_POST['pob_city_municipality']),
            'pob_province' => sanitize($_POST['pob_province']),
            'pob_country' => sanitize($_POST['pob_country'] ?? 'Philippines'),
            'birth_same_as_home' => $birth_same_as_home,
            'home_address' => $home_address,
            'mobile' => sanitize($_POST['mobile']),
            'email' => sanitize($_POST['email']),
            'telephone' => sanitize($_POST['telephone'] ?? ''),
            'father_last_name' => sanitize($_POST['father_last_name'] ?? ''),
            'father_first_name' => sanitize($_POST['father_first_name'] ?? ''),
            'father_middle_name' => sanitize($_POST['father_middle_name'] ?? ''),
            'father_suffix' => sanitize($_POST['father_suffix'] ?? ''),
            'father_dob' => !empty($_POST['father_dob']) ? sanitize($_POST['father_dob'], 'date') : null,
            'mother_last_name' => sanitize($_POST['mother_last_name'] ?? ''),
            'mother_first_name' => sanitize($_POST['mother_first_name'] ?? ''),
            'mother_middle_name' => sanitize($_POST['mother_middle_name'] ?? ''),
            'mother_suffix' => sanitize($_POST['mother_suffix'] ?? ''),
            'mother_dob' => !empty($_POST['mother_dob']) ? sanitize($_POST['mother_dob'], 'date') : null
        ];

        updatePersonalData($pdo, $id, $data);
        
        // Update Spouse (delete old, insert new)
        deleteDependents($pdo, $id, 'spouse');
        if (!empty($_POST['spouse_last_name']) || !empty($_POST['spouse_first_name'])) {
            insertDependent($pdo, $id, 'spouse', [
                'last_name' => $_POST['spouse_last_name'] ?? '',
                'first_name' => $_POST['spouse_first_name'] ?? '',
                'middle_name' => $_POST['spouse_middle_name'] ?? '',
                'suffix' => $_POST['spouse_suffix'] ?? ''
            ]);
        }
        
        // Update Children
        deleteDependents($pdo, $id, 'child');
        if (!empty($_POST['child_last_name']) && is_array($_POST['child_last_name'])) {
            for ($i = 0; $i < count($_POST['child_last_name']); $i++) {
                if (!empty($_POST['child_last_name'][$i]) || !empty($_POST['child_first_name'][$i])) {
                    insertDependent($pdo, $id, 'child', [
                        'last_name' => $_POST['child_last_name'][$i],
                        'first_name' => $_POST['child_first_name'][$i],
                        'middle_name' => $_POST['child_middle_name'][$i] ?? '',
                        'suffix' => $_POST['child_suffix'][$i] ?? '',
                        'dob' => $_POST['child_dob'][$i] ?? null
                    ]);
                }
            }
        }
        
        // Update Beneficiaries
        deleteDependents($pdo, $id, 'beneficiary');
        if (!empty($_POST['beneficiary_last_name']) && is_array($_POST['beneficiary_last_name'])) {
            for ($i = 0; $i < count($_POST['beneficiary_last_name']); $i++) {
                if (!empty($_POST['beneficiary_last_name'][$i]) || !empty($_POST['beneficiary_first_name'][$i])) {
                    insertDependent($pdo, $id, 'beneficiary', [
                        'last_name' => $_POST['beneficiary_last_name'][$i],
                        'first_name' => $_POST['beneficiary_first_name'][$i],
                        'middle_name' => $_POST['beneficiary_middle_name'][$i] ?? '',
                        'suffix' => $_POST['beneficiary_suffix'][$i] ?? '',
                        'relationship' => $_POST['beneficiary_relationship'][$i] ?? '',
                        'dob' => $_POST['beneficiary_dob'][$i] ?? null
                    ]);
                }
            }
        }

        $pdo->commit();
        header("Location: index.php?success=1&message=Record updated successfully");
        exit();
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record - <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .edit-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">← Back to List</a>
        <a href="view.php?id=<?php echo $id; ?>" style="float: right; color: #3498db;">View Record →</a>
        
        <h1>EDIT PERSONAL RECORD</h1>
        
        <?php if (isset($error)): ?>
            <div style="display: block; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                Error: <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="edit-notice">
            <strong>Editing Record #<?php echo $id; ?></strong>
        </div>
        
        <form id="personalDataForm" method="POST" novalidate>
            
            <h2>A. PERSONAL DATA</h2>
            
            <div class="form-group">
                <label class="required">NAME</label>
                <div class="row">
                    <div class="col">
                        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($record['last_name']); ?>" placeholder="Last Name">
                        <span class="error" id="error-last_name">Last name is required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($record['first_name']); ?>" placeholder="First Name">
                        <span class="error" id="error-first_name">First name is required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="middle_name" value="<?php echo htmlspecialchars($record['middle_name']); ?>" placeholder="Middle Name">
                    </div>
                    <div class="col" style="flex: 0.5;">
                        <input type="text" name="suffix" value="<?php echo htmlspecialchars($record['suffix']); ?>" placeholder="Suffix">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="required">DATE OF BIRTH</label>
                        <input type="date" name="dob" id="dob" value="<?php echo $record['dob']; ?>">
                        <span class="error" id="error-dob">Date of birth is required</span>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required">SEX</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="sex" id="sex_male" value="Male" <?php echo ($record['sex'] == 'Male') ? 'checked' : ''; ?>>
                                <label for="sex_male" style="margin: 0; font-weight: normal;">Male</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="sex" id="sex_female" value="Female" <?php echo ($record['sex'] == 'Female') ? 'checked' : ''; ?>>
                                <label for="sex_female" style="margin: 0; font-weight: normal;">Female</label>
                            </div>
                        </div>
                        <span class="error" id="error-sex">Please select sex</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="required">CIVIL STATUS</label>
                <div class="civil-status-options">
                    <?php $statuses = ['Single', 'Married', 'Widowed', 'Legally Separated', 'Others']; ?>
                    <?php foreach ($statuses as $status): ?>
                        <div class="radio-item">
                            <input type="radio" name="civil_status" value="<?php echo $status; ?>" id="cs_<?php echo strtolower(str_replace(' ', '_', $status)); ?>" 
                                <?php echo ($record['civil_status'] == $status) ? 'checked' : ''; ?>>
                            <label for="cs_<?php echo strtolower(str_replace(' ', '_', $status)); ?>" style="margin: 0; font-weight: normal;">
                                <?php echo $status; ?>
                                <?php if ($status == 'Others'): ?>
                                    <input type="text" name="civil_status_other" value="<?php echo htmlspecialchars($record['civil_status_other']); ?>" style="width: 150px; margin-left: 5px;" placeholder="Specify">
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <span class="error" id="error-civil_status">Please select civil status</span>
            </div>

            <div class="row">
                <div class="col">
                    <label>TIN</label>
                    <input type="text" name="tin" value="<?php echo htmlspecialchars($record['tin']); ?>">
                </div>
                <div class="col">
                    <label class="required">NATIONALITY</label>
                    <input type="text" name="nationality" id="nationality" value="<?php echo htmlspecialchars($record['nationality']); ?>">
                    <span class="error" id="error-nationality">Nationality is required</span>
                </div>
                <div class="col">
                    <label>RELIGION</label>
                    <input type="text" name="religion" value="<?php echo htmlspecialchars($record['religion']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="required">PLACE OF BIRTH</label>
                <div class="row">
                    <div class="col">
                        <input type="text" name="pob_city_municipality" id="pob_city_municipality" value="<?php echo htmlspecialchars($record['pob_city_municipality']); ?>" placeholder="City/Municipality">
                        <span class="error" id="error-pob_city_municipality">Required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="pob_province" id="pob_province" value="<?php echo htmlspecialchars($record['pob_province']); ?>" placeholder="Province">
                        <span class="error" id="error-pob_province">Required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="pob_country" value="<?php echo htmlspecialchars($record['pob_country']); ?>" placeholder="Country">
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="same_as_home" name="same_as_home" value="1" <?php echo $record['birth_same_as_home'] ? 'checked' : ''; ?>>
                    <label for="same_as_home" style="margin: 0; font-weight: normal;">The same with Home Address</label>
                </div>
            </div>

            <div class="form-group" id="home_address_group" style="<?php echo $record['birth_same_as_home'] ? 'display: none;' : ''; ?>">
                <label class="required">HOME ADDRESS</label>
                <textarea name="home_address" id="home_address" rows="3"><?php echo htmlspecialchars($record['home_address']); ?></textarea>
                <span class="error" id="error-home_address">Home address is required</span>
            </div>

            <div class="row">
                <div class="col">
                    <label class="required">MOBILE</label>
                    <input type="tel" name="mobile" id="mobile" value="<?php echo htmlspecialchars($record['mobile']); ?>">
                    <span class="error" id="error-mobile">Required</span>
                </div>
                <div class="col">
                    <label class="required">EMAIL</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($record['email']); ?>">
                    <span class="error" id="error-email">Valid email required</span>
                </div>
                <div class="col">
                    <label>TELEPHONE</label>
                    <input type="tel" name="telephone" value="<?php echo htmlspecialchars($record['telephone']); ?>">
                </div>
            </div>

            <h3>FATHER</h3>
            <div class="row">
                <div class="col"><input type="text" name="father_last_name" value="<?php echo htmlspecialchars($record['father_last_name']); ?>" placeholder="Last Name"></div>
                <div class="col"><input type="text" name="father_first_name" value="<?php echo htmlspecialchars($record['father_first_name']); ?>" placeholder="First Name"></div>
                <div class="col"><input type="text" name="father_middle_name" value="<?php echo htmlspecialchars($record['father_middle_name']); ?>" placeholder="Middle Name"></div>
                <div class="col" style="flex: 0.5;"><input type="text" name="father_suffix" value="<?php echo htmlspecialchars($record['father_suffix']); ?>" placeholder="Suffix"></div>
                <div class="col"><input type="date" name="father_dob" value="<?php echo $record['father_dob']; ?>"></div>
            </div>

            <h3>MOTHER'S MAIDEN NAME</h3>
            <div class="row">
                <div class="col"><input type="text" name="mother_last_name" value="<?php echo htmlspecialchars($record['mother_last_name']); ?>" placeholder="Last Name"></div>
                <div class="col"><input type="text" name="mother_first_name" value="<?php echo htmlspecialchars($record['mother_first_name']); ?>" placeholder="First Name"></div>
                <div class="col"><input type="text" name="mother_middle_name" value="<?php echo htmlspecialchars($record['mother_middle_name']); ?>" placeholder="Middle Name"></div>
                <div class="col" style="flex: 0.5;"><input type="text" name="mother_suffix" value="<?php echo htmlspecialchars($record['mother_suffix']); ?>" placeholder="Suffix"></div>
                <div class="col"><input type="date" name="mother_dob" value="<?php echo $record['mother_dob']; ?>"></div>
            </div>

            <h2>B. DEPENDENTS/BENEFICIARIES</h2>
            
            <h3>SPOUSE</h3>
            <div class="row">
                <div class="col"><input type="text" name="spouse_last_name" value="<?php echo htmlspecialchars($record['spouse']['last_name'] ?? ''); ?>" placeholder="Last Name"></div>
                <div class="col"><input type="text" name="spouse_first_name" value="<?php echo htmlspecialchars($record['spouse']['first_name'] ?? ''); ?>" placeholder="First Name"></div>
                <div class="col"><input type="text" name="spouse_middle_name" value="<?php echo htmlspecialchars($record['spouse']['middle_name'] ?? ''); ?>" placeholder="Middle Name"></div>
                <div class="col" style="flex: 0.5;"><input type="text" name="spouse_suffix" value="<?php echo htmlspecialchars($record['spouse']['suffix'] ?? ''); ?>" placeholder="Suffix"></div>
            </div>

            <h3>CHILDREN</h3>
            <div id="children_container">
                <?php if (count($record['children']) > 0): ?>
                    <?php foreach ($record['children'] as $child): ?>
                        <div class="child-entry">
                            <div class="row">
                                <div class="col"><input type="text" name="child_last_name[]" value="<?php echo htmlspecialchars($child['last_name']); ?>" placeholder="Last Name"></div>
                                <div class="col"><input type="text" name="child_first_name[]" value="<?php echo htmlspecialchars($child['first_name']); ?>" placeholder="First Name"></div>
                                <div class="col"><input type="text" name="child_middle_name[]" value="<?php echo htmlspecialchars($child['middle_name']); ?>" placeholder="Middle Name"></div>
                                <div class="col" style="flex: 0.5;"><input type="text" name="child_suffix[]" value="<?php echo htmlspecialchars($child['suffix']); ?>" placeholder="Suffix"></div>
                                <div class="col"><input type="date" name="child_dob[]" value="<?php echo $child['dob']; ?>"></div>
                            </div>
                            <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="child-entry">
                        <div class="row">
                            <div class="col"><input type="text" name="child_last_name[]" placeholder="Last Name"></div>
                            <div class="col"><input type="text" name="child_first_name[]" placeholder="First Name"></div>
                            <div class="col"><input type="text" name="child_middle_name[]" placeholder="Middle Name"></div>
                            <div class="col" style="flex: 0.5;"><input type="text" name="child_suffix[]" placeholder="Suffix"></div>
                            <div class="col"><input type="date" name="child_dob[]" placeholder="Date of Birth"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-secondary" onclick="addChild()">+ Add Child</button>

            <h3>OTHER BENEFICIARIES</h3>
            <div id="beneficiaries_container">
                <?php if (count($record['beneficiaries']) > 0): ?>
                    <?php foreach ($record['beneficiaries'] as $ben): ?>
                        <div class="beneficiary-entry">
                            <div class="row">
                                <div class="col"><input type="text" name="beneficiary_last_name[]" value="<?php echo htmlspecialchars($ben['last_name']); ?>" placeholder="Last Name"></div>
                                <div class="col"><input type="text" name="beneficiary_first_name[]" value="<?php echo htmlspecialchars($ben['first_name']); ?>" placeholder="First Name"></div>
                                <div class="col"><input type="text" name="beneficiary_middle_name[]" value="<?php echo htmlspecialchars($ben['middle_name']); ?>" placeholder="Middle Name"></div>
                                <div class="col" style="flex: 0.5;"><input type="text" name="beneficiary_suffix[]" value="<?php echo htmlspecialchars($ben['suffix']); ?>" placeholder="Suffix"></div>
                                <div class="col"><input type="text" name="beneficiary_relationship[]" value="<?php echo htmlspecialchars($ben['relationship']); ?>" placeholder="Relationship"></div>
                                <div class="col"><input type="date" name="beneficiary_dob[]" value="<?php echo $ben['dob']; ?>"></div>
                            </div>
                            <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="beneficiary-entry">
                        <div class="row">
                            <div class="col"><input type="text" name="beneficiary_last_name[]" placeholder="Last Name"></div>
                            <div class="col"><input type="text" name="beneficiary_first_name[]" placeholder="First Name"></div>
                            <div class="col"><input type="text" name="beneficiary_middle_name[]" placeholder="Middle Name"></div>
                            <div class="col" style="flex: 0.5;"><input type="text" name="beneficiary_suffix[]" placeholder="Suffix"></div>
                            <div class="col"><input type="text" name="beneficiary_relationship[]" placeholder="Relationship"></div>
                            <div class="col"><input type="date" name="beneficiary_dob[]" placeholder="Date of Birth"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-secondary" onclick="addBeneficiary()">+ Add Beneficiary</button>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;" onclick="return validateForm()">UPDATE RECORD</button>
                <a href="view.php?id=<?php echo $id; ?>" style="padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Cancel</a>
            </div>
        </form>
    </div>

    <script src="js/validation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('same_as_home');
            const homeGroup = document.getElementById('home_address_group');
            if (checkbox && checkbox.checked) {
                homeGroup.style.display = 'none';
            }
            
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    homeGroup.style.display = 'none';
                    document.getElementById('home_address').value = '';
                } else {
                    homeGroup.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html> 