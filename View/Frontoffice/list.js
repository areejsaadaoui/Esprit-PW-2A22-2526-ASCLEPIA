// ANIMATION DES CARTES AU SCROLL

const cards = document.querySelectorAll('.post-card');
if (cards.length > 0) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
};


// ===== DARK / LIGHT MODE =====
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Vérifier le thème sauvegardé
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') {
    body.classList.add('dark-mode');
    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
} else {
    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
}

// Basculement du thème
themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    
    if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        localStorage.setItem('theme', 'light');
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
});

// Gestion des likes – chaque post indépendant
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const postId = this.dataset.id;
        const isLiked = this.classList.contains('liked');
        const action = isLiked ? 'unlike' : 'like';
        
        fetch('../Backoffice/toggleLike.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_post=' + postId + '&action=' + action
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Met à jour UNIQUEMENT ce bouton
                this.classList.toggle('liked');
                
                // Met à jour l'icône
                const icon = this.querySelector('i');
                if (this.classList.contains('liked')) {
                    icon.className = 'fa-solid fa-heart';
                    icon.style.color = '#ef4444';
                } else {
                    icon.className = 'fa-regular fa-heart';
                    icon.style.color = '';
                }
                
                // Met à jour le compteur de ce post
                const countSpan = this.querySelector('.like-count');
                countSpan.textContent = data.newCount;
            }
        })
        .catch(error => console.error('Erreur:', error));
    });
});