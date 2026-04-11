document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;

    const fields = [
        { id: 'nom', min: 3, errorId: 'nom-error', pattern: /^[a-zA-Z\s]{3,}$/ },
        { id: 'adresse', min: 5, errorId: 'adresse-error' },
        { id: 'telephone', errorId: 'telephone-error', pattern: /^\d{8}$/ },
        { id: 'email', errorId: 'email-error', pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ }
    ];

    function validateField(field) {
        const input = document.getElementById(field.id);
        const errorDiv = document.getElementById(field.errorId);
        if (!input || !errorDiv) return true;

        let isValid = true;
        const value = input.value.trim();

        if (field.min && value.length < field.min) {
            isValid = false;
        } else if (field.pattern && !field.pattern.test(value)) {
            isValid = false;
        }

        if (!isValid) {
            input.classList.add('is-invalid');
            errorDiv.style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
        }

        return isValid;
    }

    // Real-time validation
    fields.forEach(field => {
        const input = document.getElementById(field.id);
        if (input) {
            input.addEventListener('input', () => validateField(field));
        }
    });

    // Form submission validation
    form.addEventListener('submit', function(event) {
        let isFormValid = true;
        fields.forEach(field => {
            if (!validateField(field)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire avant de soumettre.');
        }
    });
});
