document.addEventListener('DOMContentLoaded', function() {
   
    const tousLesFormulaires = document.querySelectorAll('form');
    
    tousLesFormulaires.forEach(function(form) {
        
        form.addEventListener('submit', function(event) {
            const textarea = form.querySelector('textarea[name="texte_rep"]');
            if (!textarea) return; 
            
            const texte = textarea.value;
            
            if (texte.trim() === '') {
                alert('❌ La réponse ne peut pas être vide.');
                event.preventDefault();
                return false;
            }
            
            if (texte.length > 200) {
                alert('❌ La réponse ne doit pas dépasser 200 caractères. (' + texte.length + ' caractères saisis)');
                event.preventDefault();
                return false;
            }
         
            if (texte.toLowerCase().indexOf('xampp') !== -1) {
                alert('❌ Le mot "xampp" est interdit dans les réponses.');
                event.preventDefault();
                return false;
            }
            
            return true;
        });
        
    });
    
});