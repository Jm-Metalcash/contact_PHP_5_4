
//Fonction qui permet d'ajouter un div selon le nb de colis
document.getElementById('packageNumber').addEventListener('input', function() {
    const packageNumber = parseInt(this.value) || 0;
    const packagesContainer = document.getElementById('packagesContainer');

    // Réinitialiser le conteneur
    packagesContainer.innerHTML = '';

    for (let i = 1; i <= packageNumber; i++) {
        const packageDiv = document.createElement('div');
        packageDiv.classList.add('package-number');
        packageDiv.innerHTML = `
            <h4>Colis ${i}</h4>
            <div class="materials-section" id="materials-section-${i}">
                <div class="formbold-input-flex material-entry">
                    <div>
                        <label for="materialType${i}" class="formbold-form-label">Type de matériaux</label>
                        <input type="text" name="materialType[]" class="formbold-form-input" placeholder="exemple: étain" />
                    </div>
                    <div>
                        <label for="weight${i}" class="formbold-form-label">Poids en kg</label>
                        <input type="number" step="1" name="weight[]" class="formbold-form-input" placeholder="Poids en kg" />
                    </div>
                    <div>
                        <label for="description${i}" class="formbold-form-label">Descriptif (facultatif)</label>
                        <input type="text" name="description[]" class="formbold-form-input" placeholder="exemple: couverts" />
                    </div>
                </div>
            </div>
            <button type="button" class="btn-add add-material-button" data-package-id="${i}">Ajouter un autre matériau</button>
        `;
        packagesContainer.appendChild(packageDiv);
    }

    // Ajouter l'événement pour chaque bouton d'ajout de matériau
    document.querySelectorAll('.add-material-button').forEach(button => {
        button.addEventListener('click', function() {
            const packageId = button.getAttribute('data-package-id');
            addMaterial(packageId);
        });
    });
});


// Fonction pour créer un champ d'entrée dans le formulaire
function createInputField(labelText, name, type = 'text', placeholder = '', step = null) {
    const fieldWrapper = document.createElement('div');
    const label = document.createElement('label');
    label.className = 'formbold-form-label';
    label.textContent = labelText;

    const input = document.createElement('input');
    input.type = type;
    input.name = name;
    input.className = 'formbold-form-input';
    input.placeholder = placeholder;
    if (step) input.step = step;

    fieldWrapper.appendChild(label);
    fieldWrapper.appendChild(input);

    return fieldWrapper;
}

// Fonction pour ajouter un nouveau matériau dans le colis spécifié
function addMaterial(packageId) {
    const materialsSection = document.getElementById(`materials-section-${packageId}`);
    const materialEntries = materialsSection.getElementsByClassName('material-entry');

    // Limite fixée à 8 entrées par colis
    if (materialEntries.length >= 8) {
        alert('Limite de matériaux atteinte pour ce colis.');
        return;
    }

    const newMaterialEntry = document.createElement('div');
    newMaterialEntry.className = 'formbold-input-flex material-entry';

    // Ajouter les champs "Type de matériaux", "Poids en kg" et "Descriptif"
    newMaterialEntry.appendChild(createInputField('Type de matériaux', 'materialType[]', 'text', 'exemple: étain'));
    newMaterialEntry.appendChild(createInputField('Poids en kg', 'weight[]', 'number', 'Poids en kg', '1'));
    newMaterialEntry.appendChild(createInputField('Descriptif (facultatif)', 'description[]', 'text', 'exemple: couverts'));

    materialsSection.appendChild(newMaterialEntry);
}