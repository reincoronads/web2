<?php
// Start session
session_start();

// Include database config
require_once 'config.php';

// Initialize variables
$errors = [];
$formData = [];
$successMessage = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // List of form fields
    $fields = ['name', 'dob', 'sex', 'civil_status', 'nationality', 
               'place_of_birth', 'home_address', 'mobile_number', 'email'];
    
    // Sanitize input data
    foreach ($fields as $field) {
        $formData[$field] = trim($_POST[$field] ?? '');
    }
    
    // Check if "same as home" checkbox is checked
    $sameAsHome = isset($_POST['same_as_home']);
    
    // If checkbox is checked, copy place of birth to home address
    if ($sameAsHome) {
        $formData['home_address'] = $formData['place_of_birth'];
    }
    
    // Validation rules
    if (empty($formData['name'])) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($formData['dob'])) {
        $errors['dob'] = 'Date of Birth is required';
    }
    
    if (empty($formData['sex'])) {
        $errors['sex'] = 'Sex is required';
    }
    
    if (empty($formData['civil_status'])) {
        $errors['civil_status'] = 'Civil Status is required';
    }
    
    if (empty($formData['nationality'])) {
        $errors['nationality'] = 'Nationality is required';
    }
    
    if (empty($formData['place_of_birth'])) {
        $errors['place_of_birth'] = 'Place of Birth is required';
    }
    
    if (!$sameAsHome && empty($formData['home_address'])) {
        $errors['home_address'] = 'Home Address is required';
    }
    
    if (empty($formData['mobile_number'])) {
        $errors['mobile_number'] = 'Mobile Number is required';
    }
    
    if (empty($formData['email'])) {
        $errors['email'] = 'Email Address is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    // If no validation errors, process form
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Check if email already exists
        $email = mysqli_real_escape_string($conn, $formData['email']);
        $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email already exists in our database';
        } else {
            // Escape all data for SQL
            $name = mysqli_real_escape_string($conn, $formData['name']);
            $dob = mysqli_real_escape_string($conn, $formData['dob']);
            $sex = mysqli_real_escape_string($conn, $formData['sex']);
            $civil_status = mysqli_real_escape_string($conn, $formData['civil_status']);
            $nationality = mysqli_real_escape_string($conn, $formData['nationality']);
            $place_of_birth = mysqli_real_escape_string($conn, $formData['place_of_birth']);
            $home_address = mysqli_real_escape_string($conn, $formData['home_address']);
            $mobile_number = mysqli_real_escape_string($conn, $formData['mobile_number']);
            
            // Insert into database
            $sql = "INSERT INTO users (name, dob, sex, civil_status, nationality, 
                                       place_of_birth, home_address, mobile_number, email) 
                    VALUES ('$name', '$dob', '$sex', '$civil_status', '$nationality',
                            '$place_of_birth', '$home_address', '$mobile_number', '$email')";
            
            if (mysqli_query($conn, $sql)) {
                $successMessage = "Form submitted successfully!";
                // Clear form data
                foreach ($fields as $field) {
                    $formData[$field] = '';
                }
            } else {
                $errors['database'] = "Error: " . mysqli_error($conn);
            }
        }
        
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>User Registration Form</h1>
        
        <?php if ($successMessage): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['database'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($errors['database']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registrationForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" 
                           placeholder="Enter your full name">
                    <?php if (isset($errors['name'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="dob" class="required">Date of Birth</label>
                    <input type="date" id="dob" name="dob" 
                           value="<?php echo htmlspecialchars($formData['dob'] ?? ''); ?>">
                    <?php if (isset($errors['dob'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['dob']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sex" class="required">Sex</label>
                    <select id="sex" name="sex">
                        <option value="">Select Sex</option>
                        <option value="Male" <?php echo (($formData['sex'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (($formData['sex'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (($formData['sex'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <?php if (isset($errors['sex'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['sex']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="civil_status" class="required">Civil Status</label>
                    <select id="civil_status" name="civil_status">
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?php echo (($formData['civil_status'] ?? '') == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (($formData['civil_status'] ?? '') == 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo (($formData['civil_status'] ?? '') == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Widowed" <?php echo (($formData['civil_status'] ?? '') == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                    <?php if (isset($errors['civil_status'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['civil_status']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="nationality" class="required">Nationality</label>
                <input type="text" id="nationality" name="nationality" 
                       value="<?php echo htmlspecialchars($formData['nationality'] ?? ''); ?>" 
                       placeholder="Enter your nationality">
                <?php if (isset($errors['nationality'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['nationality']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="place_of_birth" class="required">Place of Birth</label>
                <textarea id="place_of_birth" name="place_of_birth" rows="3" 
                          placeholder="Enter complete place of birth (City, Province, Country)"><?php echo htmlspecialchars($formData['place_of_birth'] ?? ''); ?></textarea>
                <?php if (isset($errors['place_of_birth'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['place_of_birth']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="same_as_home" name="same_as_home" 
                       onclick="toggleHomeAddress()" 
                       <?php echo (isset($_POST['same_as_home']) || isset($formData['same_as_home'])) ? 'checked' : ''; ?>>
                <label for="same_as_home">The same with Home Address</label>
            </div>
            
            <div class="form-group" id="home_address_group">
                <label for="home_address" class="required">Home Address</label>
                <textarea id="home_address" name="home_address" rows="3" 
                          placeholder="Enter complete home address"><?php echo htmlspecialchars($formData['home_address'] ?? ''); ?></textarea>
                <?php if (isset($errors['home_address'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['home_address']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="mobile_number" class="required">Mobile/Cellphone Number</label>
                    <input type="tel" id="mobile_number" name="mobile_number" 
                           value="<?php echo htmlspecialchars($formData['mobile_number'] ?? ''); ?>" 
                           placeholder="e.g., 09171234567 or +639171234567">
                    <?php if (isset($errors['mobile_number'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['mobile_number']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="email" class="required">E-mail Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                           placeholder="Enter your email address">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Submit Form</button>
        </form>
    </div>
    
    <script src="script.js"></script>
</body>
</html>