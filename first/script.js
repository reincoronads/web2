// Toggle home address visibility
function toggleHomeAddress() {
    const checkbox = document.getElementById('same_as_home');
    const homeAddressGroup = document.getElementById('home_address_group');
    const homeAddressField = document.getElementById('home_address');
    
    if (checkbox.checked) {
        homeAddressGroup.style.display = 'none';
        homeAddressField.value = document.getElementById('place_of_birth').value;
    } else {
        homeAddressGroup.style.display = 'block';
    }
}

// Client-side form validation
function validateForm() {
    let isValid = true;
    
    // Clear previous error highlights
    document.querySelectorAll('.error-highlight').forEach(el => {
        el.classList.remove('error-highlight');
    });
    
    // Remove previous JavaScript errors
    document.querySelectorAll('.js-error').forEach(el => {
        el.remove();
    });
    
    // Validate Name
    const name = document.getElementById('name').value.trim();
    if (name === '' || name.length < 2) {
        showError('name', 'Name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate Date of Birth
    const dob = document.getElementById('dob').value;
    if (dob === '') {
        showError('dob', 'Date of Birth is required');
        isValid = false;
    }
    
    // Validate Sex
    const sex = document.getElementById('sex').value;
    if (sex === '') {
        showError('sex', 'Please select your sex');
        isValid = false;
    }
    
    // Validate Civil Status
    const civilStatus = document.getElementById('civil_status').value;
    if (civilStatus === '') {
        showError('civil_status', 'Please select civil status');
        isValid = false;
    }
    
    // Validate Nationality
    const nationality = document.getElementById('nationality').value.trim();
    if (nationality === '' || nationality.length < 2) {
        showError('nationality', 'Nationality must be at least 2 characters');
        isValid = false;
    }
    
    // Validate Place of Birth
    const placeOfBirth = document.getElementById('place_of_birth').value.trim();
    if (placeOfBirth === '') {
        showError('place_of_birth', 'Place of Birth is required');
        isValid = false;
    }
    
    // Validate Home Address
    const sameAsHome = document.getElementById('same_as_home').checked;
    const homeAddress = document.getElementById('home_address').value.trim();
    const homeAddressGroup = document.getElementById('home_address_group');
    
    if (!sameAsHome && homeAddressGroup.style.display !== 'none' && homeAddress === '') {
        showError('home_address', 'Home Address is required');
        isValid = false;
    }
    
    // Validate Mobile Number
    const mobileNumber = document.getElementById('mobile_number').value.trim();
    const mobileRegex = /^\+?[0-9\s\-\(\)]{10,}$/;
    if (mobileNumber === '') {
        showError('mobile_number', 'Mobile Number is required');
        isValid = false;
    } else if (!mobileRegex.test(mobileNumber)) {
        showError('mobile_number', 'Invalid mobile number format');
        isValid = false;
    }
    
    // Validate Email
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '') {
        showError('email', 'Email Address is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Invalid email format');
        isValid = false;
    }
    
    return isValid;
}

// Show error message for a field
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('error-highlight');
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'js-error';
    errorDiv.textContent = message;
    
    // Insert error after the field
    field.parentNode.insertBefore(errorDiv, field.nextSibling);
}

// Initialize form
function initForm() {
    // Set initial home address visibility
    toggleHomeAddress();
    
    // Copy place of birth to home address when typing
    document.getElementById('place_of_birth').addEventListener('input', function() {
        if (document.getElementById('same_as_home').checked) {
            document.getElementById('home_address').value = this.value;
        }
    });
    
    // Add form submit event listener
    document.getElementById('registrationForm').addEventListener('submit', function(event) {
        if (!validateForm()) {
            event.preventDefault();
            
            // Scroll to first error
            const firstError = document.querySelector('.error-highlight');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initForm);