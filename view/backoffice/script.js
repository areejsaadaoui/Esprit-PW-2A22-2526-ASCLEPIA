document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       Définition des champs à valider selon le formulaire présent
       ============================================================ */

    // Champs Pharmacie
    const pharmacieFields = [
        {
            id: 'nom',
            errorId: 'nom-error',
            pattern: /^[a-zA-ZÀ-ÿ\s\-']{3,}$/,
            message: 'Le nom doit contenir au moins 3 caractères alphabétiques.'
        },
        {
            id: 'adresse',
            errorId: 'adresse-error',
            min: 5,
            message: "L'adresse doit contenir au moins 5 caractères."
        },
        {
            id: 'telephone',
            errorId: 'telephone-error',
            pattern: /^\d{8}$/,
            message: 'Le numéro de téléphone doit contenir exactement 8 chiffres.'
        },
        {
            id: 'email',
            errorId: 'email-error',
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: "L'adresse e-mail n'est pas valide."
        }
    ];

    // Champs Médicament
    const medicamentFields = [
        {
            id: 'nom',
            errorId: 'nom-error',
            min: 3,
            message: 'Le nom doit contenir au moins 3 caractères.'
        },
        {
            id: 'categorie',
            errorId: 'categorie-error',
            required: true,
            message: 'Veuillez sélectionner une catégorie.'
        },
        {
            id: 'prix',
            errorId: 'prix-error',
            numMin: 0.001,
            message: 'Le prix doit être supérieur à 0.'
        },
        {
            id: 'stock',
            errorId: 'stock-error',
            numMin: 0,
            isInt: true,
            message: 'Le stock doit être un nombre entier supérieur ou égal à 0.'
        },
        {
            id: 'id_pharmacie',
            errorId: 'id-pharmacie-error',
            required: true,
            message: 'Veuillez sélectionner une pharmacie.'
        }
    ];

    /* ============================================
       Détection du formulaire actif
       ============================================ */
    const form = document.querySelector('form');
    if (!form) return;

    // Détermine si on est sur une page médicament ou pharmacie
    const isMedicament = !!document.getElementById('prix') || !!document.getElementById('stock');
    const fields = isMedicament ? medicamentFields : pharmacieFields;

    /* ============================================
       Fonctions de validation
       ============================================ */
    function validateField(field) {
        const input = document.getElementById(field.id);
        const errorDiv = document.getElementById(field.errorId);
        if (!input || !errorDiv) return true;

        const value = input.value.trim();
        let isValid = true;

        if (field.required) {
            // Champ obligatoire (select vide)
            if (!value) isValid = false;
        } else if (field.isInt) {
            // Nombre entier >= numMin
            const num = parseInt(value, 10);
            if (isNaN(num) || num < field.numMin) isValid = false;
        } else if (field.numMin !== undefined) {
            // Nombre décimal > numMin
            const num = parseFloat(value);
            if (isNaN(num) || num < field.numMin) isValid = false;
        } else if (field.min && value.length < field.min) {
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

    /* ============================================
       Validation en temps réel (saisie champ par champ)
       ============================================ */
    fields.forEach(function (field) {
        const input = document.getElementById(field.id);
        if (input) {
            input.addEventListener('input', function () { validateField(field); });
            input.addEventListener('change', function () { validateField(field); });
        }
    });

    /* ============================================
       Validation à la soumission du formulaire
       ============================================ */
    form.addEventListener('submit', function (event) {
        let isFormValid = true;

        fields.forEach(function (field) {
            if (!validateField(field)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            // Scroll vers la première erreur
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
});
