// Fonction pour basculer entre les formulaires et appliquer les styles de bouton
function displayForm(formId, button) {
    // Retire les classes actives et inactives de tous les boutons
    document.querySelectorAll('.choice-contact-button').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('inactive'); // Applique inactive par défaut
    });

    // Ajoute la classe active au bouton sélectionné
    button.classList.add('active');
    button.classList.remove('inactive'); // Retire l'effet inactif du bouton actif

    // Cache tous les formulaires et affiche le formulaire sélectionné avec l'animation
    document.querySelectorAll('.form-box').forEach(form => {
        form.classList.remove('active');
    });

    setTimeout(() => {
        document.getElementById(formId).classList.add('active');
    }, 100);
}

// Au chargement de la page, applique la classe active au bouton de contact et inactive à l'autre
document.addEventListener('DOMContentLoaded', () => {
    // Applique les styles de chargement initial
    document.getElementById('contactButton').classList.add('active');
    document.getElementById('bordereauButton').classList.add('inactive');

    // Affiche le formulaire de contact par défaut
    document.getElementById('form-contact-box').classList.add('active');

});
