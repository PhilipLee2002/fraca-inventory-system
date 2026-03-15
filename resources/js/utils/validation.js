/**
 * Display validation errors on form fields
 * @param {Object} errors - Object containing field names as keys and error messages as values
 * @param {HTMLFormElement} form - The form element
 */
export function displayValidationErrors(errors, form) {
    // Clear previous errors
    clearValidationErrors(form);

    // Display new errors
    Object.keys(errors).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('is-invalid');
            
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                const errorMessages = Array.isArray(errors[fieldName]) 
                    ? errors[fieldName] 
                    : [errors[fieldName]];
                feedback.textContent = errorMessages.join(', ');
            }
        }
    });
}

/**
 * Clear all validation errors from a form
 * @param {HTMLFormElement} form - The form element
 */
export function clearValidationErrors(form) {
    const invalidFields = form.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });

    const feedbacks = form.querySelectorAll('.invalid-feedback');
    feedbacks.forEach(feedback => {
        feedback.textContent = '';
    });
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean}
 */
export function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate required fields in a form
 * @param {HTMLFormElement} form - The form element
 * @returns {boolean}
 */
export function validateRequiredFields(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = 'This field is required';
            }
            isValid = false;
        }
    });

    return isValid;
}
