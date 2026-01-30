<?php
// functions.php - Helper and CRUD functions

require_once 'config.php';

/**
 * Sanitize input data
 */
function sanitize($data, $type = 'string') {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    switch($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) ? $data : false;
        case 'date':
            return !empty($data) && strtotime($data) ? $data : null;
        default:
            return $data;
    }
}

/**
 * Get database connection
 */
function getDB() {
    return getDBConnection();
}

/**
 * Get all personal records with pagination (NO SEARCH)
 */
function getAllRecords($page = 1, $perPage = 10) {
    $pdo = getDB();
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM personal_data ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
    
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll();
    
    // Get total count for pagination
    $totalStmt = $pdo->query("SELECT FOUND_ROWS()");
    $total = $totalStmt->fetchColumn();
    
    return ['records' => $records, 'total' => $total, 'pages' => ceil($total / $perPage)];
}

/**
 * Get single record by ID with dependents
 */
function getRecordById($id) {
    $pdo = getDB();
    
    // Get personal data
    $stmt = $pdo->prepare("SELECT * FROM personal_data WHERE id = ?");
    $stmt->execute([$id]);
    $record = $stmt->fetch();
    
    if (!$record) return null;
    
    // Get dependents
    $stmt = $pdo->prepare("SELECT * FROM dependents WHERE personal_data_id = ?");
    $stmt->execute([$id]);
    $dependents = $stmt->fetchAll();
    
    // Organize dependents by type
    $record['spouse'] = null;
    $record['children'] = [];
    $record['beneficiaries'] = [];
    
    foreach ($dependents as $dep) {
        switch($dep['type']) {
            case 'spouse':
                $record['spouse'] = $dep;
                break;
            case 'child':
                $record['children'][] = $dep;
                break;
            case 'beneficiary':
                $record['beneficiaries'][] = $dep;
                break;
        }
    }
    
    return $record;
}

/**
 * Update personal data
 */
function updatePersonalData($pdo, $id, $data) {
    $sql = "UPDATE personal_data SET 
        last_name = :last_name,
        first_name = :first_name,
        middle_name = :middle_name,
        suffix = :suffix,
        dob = :dob,
        sex = :sex,
        civil_status = :civil_status,
        civil_status_other = :civil_status_other,
        tin = :tin,
        nationality = :nationality,
        religion = :religion,
        pob_city_municipality = :pob_city_municipality,
        pob_province = :pob_province,
        pob_country = :pob_country,
        birth_same_as_home = :birth_same_as_home,
        home_address = :home_address,
        mobile = :mobile,
        email = :email,
        telephone = :telephone,
        father_last_name = :father_last_name,
        father_first_name = :father_first_name,
        father_middle_name = :father_middle_name,
        father_suffix = :father_suffix,
        father_dob = :father_dob,
        mother_last_name = :mother_last_name,
        mother_first_name = :mother_first_name,
        mother_middle_name = :mother_middle_name,
        mother_suffix = :mother_suffix,
        mother_dob = :mother_dob
        WHERE id = :id";
    
    $data['id'] = $id;
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data);
}

/**
 * Delete dependents by personal data ID
 */
function deleteDependents($pdo, $personalDataId, $type = null) {
    $sql = "DELETE FROM dependents WHERE personal_data_id = ?";
    $params = [$personalDataId];
    
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Delete entire record
 */
function deleteRecord($id) {
    $pdo = getDB();
    // Due to ON DELETE CASCADE, this will also delete dependents
    $stmt = $pdo->prepare("DELETE FROM personal_data WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Validation functions
 */
function validateRequiredFields($fields, $data) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[] = "$label is required.";
        }
    }
    return $errors;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateMobile($mobile) {
    return preg_match('/^[0-9\s\-\+\(\)]+$/', $mobile);
}

function formatAddressFromPOB($city, $province, $country) {
    return "$city, $province, $country";
}

function insertPersonalData($pdo, $data) {
    $sql = "INSERT INTO personal_data (
        last_name, first_name, middle_name, suffix, dob, sex, civil_status, 
        civil_status_other, tin, nationality, religion, pob_city_municipality, 
        pob_province, pob_country, birth_same_as_home, home_address, mobile, 
        email, telephone, father_last_name, father_first_name, father_middle_name, 
        father_suffix, father_dob, mother_last_name, mother_first_name, 
        mother_middle_name, mother_suffix, mother_dob
    ) VALUES (
        :last_name, :first_name, :middle_name, :suffix, :dob, :sex, :civil_status, 
        :civil_status_other, :tin, :nationality, :religion, :pob_city_municipality, 
        :pob_province, :pob_country, :birth_same_as_home, :home_address, :mobile, 
        :email, :telephone, :father_last_name, :father_first_name, :father_middle_name, 
        :father_suffix, :father_dob, :mother_last_name, :mother_first_name, 
        :mother_middle_name, :mother_suffix, :mother_dob
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

function insertDependent($pdo, $personalDataId, $type, $data) {
    $sql = "INSERT INTO dependents 
        (personal_data_id, type, last_name, first_name, middle_name, suffix, relationship, dob) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $personalDataId,
        $type,
        $data['last_name'] ?? '',
        $data['first_name'] ?? '',
        $data['middle_name'] ?? '',
        $data['suffix'] ?? '',
        $data['relationship'] ?? '',
        $data['dob'] ?? null
    ]);
}
?>