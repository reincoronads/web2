// js/validation.js

document.addEventListener('DOMContentLoaded', function() {
    // Toggle Home Address visibility
    const sameAsHomeCheckbox = document.getElementById('same_as_home');
    const homeAddressGroup = document.getElementById('home_address_group');
    const homeAddressInput = document.getElementById('home_address');

    if (sameAsHomeCheckbox) {
        sameAsHomeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                homeAddressGroup.classList.add('hidden');
                homeAddressInput.value = '';
                homeAddressInput.removeAttribute('required');
            } else {
                homeAddressGroup.classList.remove('hidden');
                homeAddressInput.setAttribute('required', 'required');
            }
        });
    }

    // Check for URL parameters
    checkUrlParameters();
});

/**
 * Add new child entry
 */
function addChild() {
    const container = document.getElementById('children_container');
    const newEntry = document.createElement('div');
    newEntry.className = 'child-entry';
    newEntry.innerHTML = `
        <div class="row">
            <div class="col">
                <input type="text" name="child_last_name[]" placeholder="Last Name">
            </div>
            <div class="col">
                <input type="text" name="child_first_name[]" placeholder="First Name">
            </div>
            <div class="col">
                <input type="text" name="child_middle_name[]" placeholder="Middle Name">
            </div>
            <div class="col" style="flex: 0.5;">
                <input type="text" name="child_suffix[]" placeholder="Suffix">
            </div>
            <div class="col">
                <input type="date" name="child_dob[]" placeholder="Date of Birth">
            </div>
        </div>
        <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
    `;
    container.appendChild(newEntry);
}

/**
 * Add new beneficiary entry
 */
function addBeneficiary() {
    const container = document.getElementById('beneficiaries_container');
    const newEntry = document.createElement('div');
    newEntry.className = 'beneficiary-entry';
    newEntry.innerHTML = `
        <div class="row">
            <div class="col">
                <input type="text" name="beneficiary_last_name[]" placeholder="Last Name">
            </div>
            <div class="col">
                <input type="text" name="beneficiary_first_name[]" placeholder="First Name">
            </div>
            <div class="col">
                <input type="text" name="beneficiary_middle_name[]" placeholder="Middle Name">
            </div>
            <div class="col" style="flex: 0.5;">
                <input type="text" name="beneficiary_suffix[]" placeholder="Suffix">
            </div>
            <div class="col">
                <input type="text" name="beneficiary_relationship[]" placeholder="Relationship">
            </div>
            <div class="col">
                <input type="date" name="beneficiary_dob[]" placeholder="Date of Birth">
            </div>
        </div>
        <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
    `;
    container.appendChild(newEntry);
}

/**
 * Form Validation
 */
function validateForm() {
    let isValid = true;
    
    // Reset errors
    document.querySelectorAll('.error').forEach(el => el.style.display = 'none');
    document.querySelectorAll('input, select, textarea').forEach(el => el.classList.remove('input-error'));
    
    // Helper function
    function showError(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        if (field && error) {
            field.classList.add('input-error');
            error.style.display = 'block';
            isValid = false;
        }
    }
    
    // Validate required text fields
    const requiredFields = [
        { id: 'last_name', error: 'error-last_name' },
        { id: 'first_name', error: 'error-first_name' },
        { id: 'dob', error: 'error-dob' },
        { id: 'nationality', error: 'error-nationality' },
        { id: 'pob_city_municipality', error: 'error-pob_city_municipality' },
        { id: 'pob_province', error: 'error-pob_province' }
    ];
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element && element.value.trim() === '') {
            showError(field.id, field.error);
        }
    });
    
    // Validate Radio buttons (Sex)
    if (!document.querySelector('input[name="sex"]:checked')) {
        const sexError = document.getElementById('error-sex');
        if (sexError) sexError.style.display = 'block';
        isValid = false;
    }
    
    // Validate Civil Status
    if (!document.querySelector('input[name="civil_status"]:checked')) {
        const csError = document.getElementById('error-civil_status');
        if (csError) csError.style.display = 'block';
        isValid = false;
    }
    
    // Validate Home Address (if checkbox not checked)
    const sameAsHome = document.getElementById('same_as_home').checked;
    const homeAddress = document.getElementById('home_address');
    if (!sameAsHome && homeAddress && homeAddress.value.trim() === '') {
        showError('home_address', 'error-home_address');
    }
    
    // Validate Mobile
    const mobile = document.getElementById('mobile');
    if (mobile) {
        const mobileValue = mobile.value.trim();
        if (mobileValue === '') {
            showError('mobile', 'error-mobile');
        } else if (!/^[0-9\s\-\+\(\)]+$/.test(mobileValue)) {
            document.getElementById('error-mobile').textContent = 'Please enter a valid mobile number';
            showError('mobile', 'error-mobile');
        }
    }
    
    // Validate Email
    const email = document.getElementById('email');
    if (email) {
        const emailValue = email.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const emailError = document.getElementById('error-email');
        
        if (emailValue === '') {
            emailError.textContent = 'Email is required';
            showError('email', 'error-email');
        } else if (!emailRegex.test(emailValue)) {
            emailError.textContent = 'Please enter a valid email address';
            showError('email', 'error-email');
        }
    }
    
    return isValid;
}

/**
 * Check URL parameters for messages
 */
function checkUrlParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = document.getElementById('message');
    
    if (!message) return;
    
    if (urlParams.has('success')) {
        message.textContent = 'Data saved successfully!';
        message.className = 'success';
        message.style.display = 'block';
        setTimeout(() => { message.style.display = 'none'; }, 5000);
    } else if (urlParams.has('error')) {
        message.textContent = 'Error: ' + decodeURIComponent(urlParams.get('error'));
        message.className = 'error-msg';
        message.style.display = 'block';
    }
}