// ── AJAX Patient Search ──
const searchInput  = document.getElementById('patientSearch');
const dropdown     = document.getElementById('patientDropdown');
const hiddenInput  = document.getElementById('id_patient');
const badge        = document.getElementById('selectedBadge');
const selectedName = document.getElementById('selectedName');

let searchTimeout = null; // debounce timer

searchInput.addEventListener('input', function() {
    const q = this.value.trim();

    // Clear dropdown if query too short
    if (q.length < 2) {
        dropdown.classList.remove('open');
        dropdown.innerHTML = '';
        return;
    }

    // Debounce: wait 300ms after user stops typing before firing request
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dropdown.innerHTML = '<div class="no-result"><i class="fa-solid fa-spinner fa-spin"></i> Recherche...</div>';
        dropdown.classList.add('open');

        fetch('search_patients.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    dropdown.innerHTML = '<div class="no-result"><i class="fa-solid fa-face-sad-tear"></i> Aucun patient trouvé</div>';
                } else {
                    dropdown.innerHTML = data.map(p =>
                        `<div class="p-opt" onclick="selectPatient(${p.id_user}, '${p.nom.replace(/'/g, "\\'")}')">
                            <i class="fa-solid fa-user" style="color:var(--primary);font-size:0.85rem;"></i>
                            <span class="p-name">${p.nom}</span>
                            <span class="p-id-badge">ID ${p.id_user}</span>
                        </div>`
                    ).join('');
                }
                dropdown.classList.add('open');
            })
            .catch(() => {
                dropdown.innerHTML = '<div class="no-result">Erreur de recherche.</div>';
            });
    }, 300);
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.patient-search-wrap')) {
        dropdown.classList.remove('open');
    }
});

function selectPatient(id, nom) {
    hiddenInput.value = id;
    selectedName.textContent = nom;
    badge.classList.add('show');
    searchInput.style.display = 'none';
    dropdown.classList.remove('open');
    document.getElementById('err_patient').style.display = 'none';
}

function clearPatient() {
    hiddenInput.value = 0;
    searchInput.value = '';
    searchInput.style.display = '';
    badge.classList.remove('show');
    dropdown.innerHTML = '';
    searchInput.focus();
}
