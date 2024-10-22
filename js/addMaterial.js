// Fonction pour créer un élément dans le formulaire
function createInputField(labelText, name, type = 'text', placeholder = '', step = null) {
    var fieldWrapper = document.createElement('div');

    var label = document.createElement('label');
    label.className = 'formbold-form-label';
    label.textContent = labelText;

    var input = document.createElement('input');
    input.type = type;
    input.name = name;
    input.className = 'formbold-form-input';
    input.placeholder = placeholder;
    if (step) input.step = step;

    // Ajoute la classe has-value si l'input possède une valeur sinon on retire has-value
    input.addEventListener('blur', function() {
        if (this.value) {
            this.classList.add('has-value');
        } else {
            this.classList.remove('has-value');
        }
    });

    fieldWrapper.appendChild(label);
    fieldWrapper.appendChild(input);

    return fieldWrapper;
}

// Fonction pour ajouter un nouveau matériau
function addMaterial() {
    var materialsSection = document.getElementById('materials-section');
    var materialEntries = materialsSection.getElementsByClassName('material-entry');

    // Limite fixée à 8 entrées
    if (materialEntries.length >= 8) {
        document.getElementById('add-material').style.display = 'none';
        var message = document.createElement('p');
        message.textContent = "Limite de matériaux atteinte pour ce colis.";
        message.classList.add('limit-message');
        materialsSection.appendChild(message);
        return;
    }

    var newMaterialEntry = document.createElement('div');
    newMaterialEntry.className = 'formbold-input-flex material-entry material-entry-animation';

    // Ajouter les champs "Type de matériaux", "Poids en kg" et "Descriptif"
    newMaterialEntry.appendChild(createInputField('Type de matériaux', 'materialType[]', 'text', 'exemple: étain'));
    newMaterialEntry.appendChild(createInputField('Poids en kg', 'weight[]', 'number', 'Poids en kg', '1'));
    newMaterialEntry.appendChild(createInputField('Descriptif (facultatif)', 'description[]', 'text', 'exemple: couverts'));

    materialsSection.appendChild(newMaterialEntry);

    // Ajouter une animation pour le nouveau bloc
    setTimeout(function() {
        newMaterialEntry.classList.add('fade-in');
    }, 0);
}

// Attacher l'événement au bouton d'ajout
document.getElementById('add-material').addEventListener('click', addMaterial);
