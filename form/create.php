<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        $pdo->beginTransaction();

        // Required field validation
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

        // Handle Home Address logic
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

        // Prepare data
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

        $personal_data_id = insertPersonalData($pdo, $data);

        // Insert Spouse
        if (!empty($_POST['spouse_last_name']) || !empty($_POST['spouse_first_name'])) {
            insertDependent($pdo, $personal_data_id, 'spouse', [
                'last_name' => $_POST['spouse_last_name'] ?? '',
                'first_name' => $_POST['spouse_first_name'] ?? '',
                'middle_name' => $_POST['spouse_middle_name'] ?? '',
                'suffix' => $_POST['spouse_suffix'] ?? ''
            ]);
        }

        // Insert Children
        if (!empty($_POST['child_last_name']) && is_array($_POST['child_last_name'])) {
            for ($i = 0; $i < count($_POST['child_last_name']); $i++) {
                if (!empty($_POST['child_last_name'][$i]) || !empty($_POST['child_first_name'][$i])) {
                    insertDependent($pdo, $personal_data_id, 'child', [
                        'last_name' => $_POST['child_last_name'][$i],
                        'first_name' => $_POST['child_first_name'][$i],
                        'middle_name' => $_POST['child_middle_name'][$i] ?? '',
                        'suffix' => $_POST['child_suffix'][$i] ?? '',
                        'dob' => $_POST['child_dob'][$i] ?? null
                    ]);
                }
            }
        }

        // Insert Beneficiaries
        if (!empty($_POST['beneficiary_last_name']) && is_array($_POST['beneficiary_last_name'])) {
            for ($i = 0; $i < count($_POST['beneficiary_last_name']); $i++) {
                if (!empty($_POST['beneficiary_last_name'][$i]) || !empty($_POST['beneficiary_first_name'][$i])) {
                    insertDependent($pdo, $personal_data_id, 'beneficiary', [
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
        header("Location: index.php?success=1&message=Record created successfully");
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
    <title>Create Personal Record</title>
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
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">← Back to List</a>
        
        <h1>PERSONAL DATA SHEET</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-msg" style="display: block; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                Error: <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form id="personalDataForm" method="POST" novalidate>
            
            <!-- Section A: Personal Data -->
            <h2>A. PERSONAL DATA</h2>
            
            <div class="form-group">
                <label class="required">NAME</label>
                <div class="row">
                    <div class="col">
                        <input type="text" name="last_name" id="last_name" placeholder="Last Name">
                        <span class="error" id="error-last_name">Last name is required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="first_name" id="first_name" placeholder="First Name">
                        <span class="error" id="error-first_name">First name is required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="middle_name" placeholder="Middle Name">
                    </div>
                    <div class="col" style="flex: 0.5;">
                        <input type="text" name="suffix" placeholder="Suffix">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="required">DATE OF BIRTH (MM/DD/YYYY)</label>
                        <input type="date" name="dob" id="dob">
                        <span class="error" id="error-dob">Date of birth is required</span>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required">SEX</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="sex" id="sex_male" value="Male">
                                <label for="sex_male" style="margin: 0; font-weight: normal;">Male</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="sex" id="sex_female" value="Female">
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
                    <div class="radio-item">
                        <input type="radio" name="civil_status" value="Single" id="cs_single">
                        <label for="cs_single" style="margin: 0; font-weight: normal;">Single</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" name="civil_status" value="Married" id="cs_married">
                        <label for="cs_married" style="margin: 0; font-weight: normal;">Married</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" name="civil_status" value="Widowed" id="cs_widowed">
                        <label for="cs_widowed" style="margin: 0; font-weight: normal;">Widowed</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" name="civil_status" value="Legally Separated" id="cs_separated">
                        <label for="cs_separated" style="margin: 0; font-weight: normal;">Legally Separated</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" name="civil_status" value="Others" id="cs_others">
                        <label for="cs_others" style="margin: 0; font-weight: normal;">Others:</label>
                        <input type="text" name="civil_status_other" style="width: 150px; margin-left: 5px;" placeholder="Specify">
                    </div>
                </div>
                <span class="error" id="error-civil_status">Please select civil status</span>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>TAX IDENTIFICATION NUMBER (IF ANY)</label>
                        <input type="text" name="tin">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required">NATIONALITY</label>
                        <input type="text" name="nationality" id="nationality">
                        <span class="error" id="error-nationality">Nationality is required</span>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>RELIGION</label>
                        <input type="text" name="religion">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="required">PLACE OF BIRTH</label>
                <div class="row">
                    <div class="col">
                        <input type="text" name="pob_city_municipality" id="pob_city_municipality" placeholder="City/Municipality">
                        <span class="error" id="error-pob_city_municipality">City/Municipality is required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="pob_province" id="pob_province" placeholder="Province">
                        <span class="error" id="error-pob_province">Province is required</span>
                    </div>
                    <div class="col">
                        <input type="text" name="pob_country" placeholder="Country" value="Philippines">
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="same_as_home" name="same_as_home" value="1">
                    <label for="same_as_home" style="margin: 0; font-weight: normal; cursor: pointer;">
                        The same with Home Address
                    </label>
                </div>
            </div>

            <div class="form-group" id="home_address_group">
                <label class="required">HOME ADDRESS</label>
                <textarea name="home_address" id="home_address" rows="3" placeholder="Complete Home Address"></textarea>
                <span class="error" id="error-home_address">Home address is required</span>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="required">MOBILE/CELLPHONE NUMBER</label>
                        <input type="tel" name="mobile" id="mobile" placeholder="09XX XXX XXXX">
                        <span class="error" id="error-mobile">Mobile number is required</span>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="required">E-MAIL ADDRESS</label>
                        <input type="email" name="email" id="email" placeholder="example@email.com">
                        <span class="error" id="error-email">Valid email is required</span>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>TELEPHONE NUMBER</label>
                        <input type="tel" name="telephone" placeholder="Country Code + Area Code + Tel. No.">
                    </div>
                </div>
            </div>

            <h3>FATHER</h3>
            <div class="row">
                <div class="col"><input type="text" name="father_last_name" placeholder="Last Name"></div>
                <div class="col"><input type="text" name="father_first_name" placeholder="First Name"></div>
                <div class="col"><input type="text" name="father_middle_name" placeholder="Middle Name"></div>
                <div class="col" style="flex: 0.5;"><input type="text" name="father_suffix" placeholder="Suffix"></div>
                <div class="col"><input type="date" name="father_dob" placeholder="Date of Birth"></div>
            </div>

            <h3>MOTHER'S MAIDEN NAME</h3>
            <div class="row">
                <div class="col"><input type="text" name="mother_last_name" placeholder="Last Name"></div>
                <div class="col"><input type="text" name="mother_first_name" placeholder="First Name"></div>
                <div class="col"><input type="text" name="mother_middle_name" placeholder="Middle Name"></div>
                <div class="col" style="flex: 0.5;"><input type="text" name="mother_suffix" placeholder="Suffix"></div>
                <div class="col"><input type="date" name="mother_dob" placeholder="Date of Birth"></div>
            </div>

            <!-- Section B: Dependents -->
            <h2>B. DEPENDENT(S)/BENEFICIARY/IES</h2>
            
            <h3>SPOUSE</h3>
            <div class="row">
                <div class="col"><input type="text" name="spouse_last_name" placeholder="Last Name"></div>
                <div class="col"><input type="text" name="spouse_first_name" placeholder="First Name"></div>
                <div class="col"><input type="text" name="spouse_middle_name" placeholder="Middle Name"></div>
                <div class="col" style="flex: 0.5;"><input type="text" name="spouse_suffix" placeholder="Suffix"></div>
            </div>

            <h3>CHILD/REN</h3>
            <div id="children_container">
                <div class="child-entry">
                    <div class="row">
                        <div class="col"><input type="text" name="child_last_name[]" placeholder="Last Name"></div>
                        <div class="col"><input type="text" name="child_first_name[]" placeholder="First Name"></div>
                        <div class="col"><input type="text" name="child_middle_name[]" placeholder="Middle Name"></div>
                        <div class="col" style="flex: 0.5;"><input type="text" name="child_suffix[]" placeholder="Suffix"></div>
                        <div class="col"><input type="date" name="child_dob[]" placeholder="Date of Birth"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-secondary" onclick="addChild()">+ Add Another Child</button>

            <h3>OTHER BENEFICIARY/IES</h3>
            <p class="section-note">(If without spouse & child and parents are both deceased)</p>
            <div id="beneficiaries_container">
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
            </div>
            <button type="button" class="btn-secondary" onclick="addBeneficiary()">+ Add Another Beneficiary</button>

            <button type="submit" class="btn-primary" onclick="return validateForm()">SUBMIT FORM</button>
        </form>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>