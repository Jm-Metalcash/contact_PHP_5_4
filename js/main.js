//CONSTANTES & VARIABLES
const selectInputs = document.querySelectorAll('input');

//Empêcher d'envoyer le formulaire avec "enter"
document.querySelector('form').addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});


selectInputs.forEach((input, index) => {
    input.addEventListener('keydown', function(event) {
        if (event.key === "Enter") {
            event.preventDefault(); // Empêche la soumission du formulaire
            const nextInput = selectInputs[index + 1]; // Sélectionne l'input suivant
            if (nextInput) {
                nextInput.focus(); // Déplace le focus vers l'input suivant
            }
        }
    });
});
