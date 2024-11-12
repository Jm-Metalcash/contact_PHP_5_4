//CONSTANTES & VARIABLES
const selectInputs = document.querySelectorAll('input');
const textareaQuestion = document.getElementById('question');
const firstnameInput = document.getElementById("firstname");
const lastnameInput = document.getElementById("lastname");

const bordereauFormSelected = document.getElementById("bordereauForm");
const firstnameInputBordereau = bordereauFormSelected.querySelector("#firstname");
const lastnameInputBordereau = bordereauFormSelected.querySelector("#lastname");
const accountHolderInput = bordereauFormSelected.querySelector("#accountHolder");

//Empêcher d'envoyer le formulaire avec "enter"
document.querySelector('form').addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});

// Ajoute un écouteur d'événement pour le textarea "question" pour gérer la touche "Enter"
textareaQuestion.addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Empêche le comportement par défaut

        // Obtenir la position du curseur
        const cursorPosition = textareaQuestion.selectionStart;
        
        // Insérer un saut de ligne à la position actuelle du curseur
        const textBefore = textareaQuestion.value.substring(0, cursorPosition);
        const textAfter = textareaQuestion.value.substring(textareaQuestion.selectionEnd);
        
        // Mettre à jour la valeur du textarea avec le saut de ligne inséré
        textareaQuestion.value = textBefore + "\n" + textAfter;

        // Positionner le curseur juste après le saut de ligne
        textareaQuestion.selectionStart = textareaQuestion.selectionEnd = cursorPosition + 1;
    }
});


// Ajoute un écouteur d'événements pour les autres inputs
selectInputs.forEach((input, index) => {
    input.addEventListener('keydown', function(event) {
        // Vérifie si la touche "Enter" est pressée
        if (event.key === "Enter") {
            event.preventDefault(); // Empêche la soumission du formulaire
            const nextInput = selectInputs[index + 1]; // Sélectionne l'input suivant
            if (nextInput) {
                nextInput.focus(); // Déplace le focus vers l'input suivant
            }
        }
    });
});


//Affiche le prénom et nom dans l'input AccountHolder dans le form bordereau
function updateAccountHolder() {
    const firstname = firstnameInputBordereau.value;
    const lastname = lastnameInputBordereau.value;

    // Combiner les deux valeurs avec un espace entre
    const combinedName = `${firstname} ${lastname}`.trim();

    // Mettre à jour l'input accountHolder avec le nom combiné
    accountHolderInput.value = combinedName;
}

// Écouteurs d'événement pour détecter les changements dans les champs prénom et nom de famille
firstnameInputBordereau.addEventListener("input", updateAccountHolder);
lastnameInputBordereau.addEventListener("input", updateAccountHolder);