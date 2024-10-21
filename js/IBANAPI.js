document.getElementById('iban').addEventListener('blur', function() {
    const iban = document.getElementById('iban').value.trim(); // Utilisation de trim pour enlever les espaces en trop
    const ibanError = document.getElementById('ibanError');
    const bankNameField = document.getElementById('bankName');
    const swiftField = document.getElementById('swift');

    // Si l'IBAN est vide, ne pas afficher le message d'erreur et ne pas appeler l'API
    if (!iban) {
        ibanError.classList.remove('show'); // Assure de cacher l'erreur s'il était déjà visible
        return;
    }

    // Réinitialiser le message d'erreur (masquer l'erreur avec l'animation)
    ibanError.classList.remove('show');

    // Appel à l'API OpenIBAN pour valider l'IBAN et récupérer les informations bancaires
    const url = `https://openiban.com/validate/${iban}?getBIC=true&validateBankCode=true&format=json`;

    fetch(url)
        .then(response => response.json())
        .then(data => {

            // Si l'IBAN est valide, afficher les informations dans la console et auto-compléter les champs
            if (data.valid) {
                
                // Corrige l'accès aux propriétés dans data.bankData
                const bankName = data.bankData.name || 'Nom de banque non disponible';
                const bic = data.bankData.bic || 'BIC non disponible';
                

                // Remplir les champs bankName et swift avec les données reçues
                bankNameField.value = bankName;
                swiftField.value = bic;

                // Ajouter la classe has-value pour un visuel similaire à un champ déjà rempli
                bankNameField.classList.add('has-value');
                swiftField.classList.add('has-value');
            } else {
                // Afficher l'erreur si l'IBAN est invalide
                ibanError.classList.add('show');
            }
        })
        .catch(error => {
            // Afficher l'erreur en cas de problème avec l'API
            ibanError.classList.add('show');
            console.error("Erreur :", error);
        });
});

// Efface les champs bankName et swift lorsque l'utilisateur modifie l'IBAN
document.getElementById('iban').addEventListener('input', function() {
    const bankNameField = document.getElementById('bankName');
    const swiftField = document.getElementById('swift');
    
    // Vider les champs bankName et swift
    bankNameField.value = '';
    swiftField.value = '';
    
    // Retirer la classe has-value pour montrer que les champs ne sont plus remplis
    bankNameField.classList.remove('has-value');
    swiftField.classList.remove('has-value');
});
