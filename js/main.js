//CONSTANTES & VARIABLES
const selectInputs = document.querySelectorAll('input');
const firstnameInput = document.getElementById("firstname");
const lastnameInput = document.getElementById("lastname");
const accountHolderInput = document.getElementById("accountHolder");

//Empêcher d'envoyer le formulaire avec "enter"
document.querySelector('form').addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});

// Passe au prochain input quand on appuie sur "enter"
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


function updateAccountHolder() {
    const firstname = firstnameInput.value;
    const lastname = lastnameInput.value;

    // Combiner les deux valeurs avec un espace entre
    const combinedName = `${firstname} ${lastname}`.trim();

    // Mettre à jour l'input accountHolder avec le nom combiné
    accountHolderInput.value = combinedName;
}

// Écouteurs d'événement pour détecter les changements dans les champs prénom et nom de famille
firstnameInput.addEventListener("input", updateAccountHolder);
lastnameInput.addEventListener("input", updateAccountHolder);