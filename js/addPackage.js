const packagesContainer = document.getElementById('packagesContainer');
let currentPackageCount = 0; // Compte actuel des colis affichés

//Désactive le scroll dans l'input
document.getElementById('packageNumber').addEventListener('wheel', function(event) {
    event.preventDefault();
});

document.getElementById('packageNumber').addEventListener('change', function() {
    const packageNumber = parseInt(this.value) || currentPackageCount; // Garde la valeur actuelle si input vide

    // Si le nombre de colis est supérieur au nombre actuel, ajoute seulement les nouveaux colis
    if (packageNumber > currentPackageCount) {
        for (let i = currentPackageCount + 1; i <= packageNumber; i++) {
            addPackageSection(i);
        }
    } 
    // Si le nombre de colis est inférieur au nombre actuel, retire seulement les colis en excès
    else if (packageNumber < currentPackageCount) {
        for (let i = currentPackageCount; i > packageNumber; i--) {
            const packageDiv = document.getElementById(`package-${i}`);
            if (packageDiv) packageDiv.remove();
        }
    }

    // Mettre à jour le nombre de colis affichés
    currentPackageCount = packageNumber;
});

// Fonction pour ajouter un nouveau colis
function addPackageSection(index) {
    const packageDiv = document.createElement('div');
    packageDiv.classList.add('package-number');
    packageDiv.id = `package-${index}`;
    packageDiv.innerHTML = `
        <h4>Colis ${index}</h4>
        <div class="materials-section" id="materials-section-${index}">
            <div class="formbold-input-flex material-entry">
                <div>
                    <label class="formbold-form-label">Type de matériaux</label>
                    <input type="text" name="materialType[${index}][]" class="formbold-form-input" placeholder="exemple: étain" />
                </div>
                <div>
                    <label class="formbold-form-label">Poids en kg</label>
                    <input type="number" step="1" min="0" name="weight[${index}][]" class="formbold-form-input weight-selected" placeholder="Poids en kg" />
                </div>
                <div>
                    <label class="formbold-form-label">Descriptif (facultatif)</label>
                    <input type="text" name="description[${index}][]" class="formbold-form-input" placeholder="exemple: couverts" />
                </div>
            </div>
        </div>
        <button type="button" class="btn-add add-material-button" data-package-id="${index}">Ajouter un autre matériau</button>
    `;
    packagesContainer.appendChild(packageDiv);

    // Ajouter un écouteur d'événement pour le bouton d'ajout de matériau
    packageDiv.querySelector('.add-material-button').addEventListener('click', function() {
        addMaterial(index);
    });
}

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

    if (materialEntries.length >= 5) {
        alert('Limite de matériaux atteinte pour ce colis.');
        return;
    }

    const newMaterialEntry = document.createElement('div');
    newMaterialEntry.className = 'formbold-input-flex material-entry';

    newMaterialEntry.appendChild(createInputField('Type de matériaux', `materialType[${packageId}][]`, 'text', 'exemple: étain'));
    newMaterialEntry.appendChild(createInputField('Poids en kg', `weight[${packageId}][]`, 'number', 'Poids en kg', '1'));
    newMaterialEntry.appendChild(createInputField('Descriptif (facultatif)', `description[${packageId}][]`, 'text', 'exemple: couverts'));

    materialsSection.appendChild(newMaterialEntry);
}