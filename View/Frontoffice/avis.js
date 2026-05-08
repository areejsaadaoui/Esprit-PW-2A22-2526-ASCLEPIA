// =========================
// AVIS: Affichage + validation
// =========================

let currentPage = 1;
const itemsPerPage = 6;
let totalAvis = 0;
let allAvis = [];

// Charger tous les avis
async function loadAllAvis() {
  const list = document.getElementById('avisList');
  if (!list) return;
  list.innerHTML = '<div style="color: var(--text-muted);">Chargement des avis...</div>';

  try {
    const res = await fetch('list_avis.php?limit=1000');
    const data = await res.json();
    
    if (data.success && Array.isArray(data.avis)) {
      allAvis = data.avis;
      totalAvis = allAvis.length;
      
      if (totalAvis === 0) {
        list.innerHTML = '<div style="color: var(--text-muted);">Aucun avis pour le moment.</div>';
        return;
      }
      
      loadAvisPage(1);
    }
  } catch (e) {
    console.error(e);
    list.innerHTML = '<div style="color: #ef4444;">Erreur chargement avis.</div>';
  }
}

// Charger une page d'avis
function loadAvisPage(page) {
  currentPage = page;
  const list = document.getElementById('avisList');
  const startIndex = (page - 1) * itemsPerPage;
  const avisToShow = allAvis.slice(startIndex, startIndex + itemsPerPage);

  list.innerHTML = '';
  avisToShow.forEach(a => {
    const note = Number(a.note || 0);
    const stars = '⭐'.repeat(note) + '☆'.repeat(5 - note);
    
    list.innerHTML += `
      <div class="col-4" style="min-width: 280px;">
        <div class="card" style="border-radius:18px; padding:16px;">
          <div style="font-size: 1.05rem; font-weight: 800; color: #0ea5e9;">${stars} (${note}/5)</div>
          <div style="margin-top: 8px; font-weight: 700;">👤 ${escapeHtml(a.auteur || 'Anonyme')}</div>
          <div style="color:#64748b; font-size:0.85rem;">📅 ${formatDateFr(a.date_avis)}</div>
          <p style="margin-top: 8px;">"${escapeHtml(a.contenu || '')}"</p>
          <div style="display:flex; gap: 8px; margin-top: 16px; justify-content: flex-end;">
            <button onclick="openEditModal(${a.id_avis})" class="btn btn-primary btn-sm">Modifier</button>
            <button onclick="deleteAvis(${a.id_avis})" class="btn btn-danger btn-sm">Supprimer</button>
          </div>
        </div>
      </div>
    `;
  });
  
  updatePagination();
}

// Ouvrir modal d'ajout
function openAvisModal() {
  document.getElementById('modalAvisId').value = '';
  document.getElementById('modalTitle').innerHTML = '<i class="fas fa-star"></i> Donner mon avis';
  document.getElementById('modalSubmitButton').innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
  document.getElementById('avisModal').style.display = 'flex';
}

// Ouvrir modal d'édition
async function openEditModal(id) {
  const res = await fetch(`get_avis.php?id=${id}`);
  const data = await res.json();
  if (data.success) {
    const avis = data.avis;
    document.getElementById('modalAvisId').value = avis.id_avis;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Modifier mon avis';
    document.getElementById('modalNoteInput').value = avis.note;
    document.getElementById('modalContenuInput').value = avis.contenu;
    document.getElementById('avisModal').style.display = 'flex';
  }
}

// Supprimer un avis
async function deleteAvis(id) {
  if (!confirm('Voulez-vous vraiment supprimer cet avis ?')) return;
  const formData = new FormData();
  formData.append('id_avis', id);
  const res = await fetch('delete_avis.php', { method: 'POST', body: formData });
  const data = await res.json();
  if (data.success) {
    showNotification('✅ Avis supprimé', 'success');
    loadAllAvis();
  }
}

// Fermer modal
function closeAvisModal() {
  document.getElementById('avisModal').style.display = 'none';
}

// Étoiles dans la modal
let modalSelectedNote = 0;
document.getElementById('modalStarsContainer')?.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-star')) {
    modalSelectedNote = parseInt(e.target.dataset.note);
    document.getElementById('modalNoteInput').value = modalSelectedNote;
    document.querySelectorAll('.modal-star').forEach((s, i) => {
      s.classList.toggle('active', i < modalSelectedNote);
    });
  }
});

// Submission modal
document.getElementById('avisModalForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const isEdit = formData.get('id_avis');
  const endpoint = isEdit ? 'update_avis.php' : 'submit_avis.php';
  
  const res = await fetch(endpoint, { method: 'POST', body: formData });
  const data = await res.json();
  
  if (data.success) {
    closeAvisModal();
    loadAllAvis();
    showNotification(isEdit ? '✅ Avis modifié' : '✅ Avis ajouté', 'success');
  }
});

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
  loadAllAvis();
});