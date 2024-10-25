<?php
session_start(); // Démarrer la session

// Importer la bibliothèque pour le code-barres
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer l'ID unique numérique envoyé avec le formulaire
    if (!isset($_POST['uniqueId'])) {
        die('Erreur : Aucun ID unique fourni.');
    }

    $uniqueId = htmlspecialchars($_POST['uniqueId']);

    // Dossier des codes-barres
    $barcodeDir = __DIR__ . '/images/barcodes/';

    // Supprimer l'ancienne image de code-barres si elle existe
    if (isset($_SESSION['barcodePath']) && file_exists($_SESSION['barcodePath'])) {
        unlink($_SESSION['barcodePath']); // Supprimer l'ancienne image
    }

    // Créer une instance du générateur de code-barres et générer l'image
    $generator = new BarcodeGeneratorPNG();
    $barcodeImage = $generator->getBarcode($uniqueId, $generator::TYPE_CODE_128);

    // Enregistrer l'image dans le dossier avec un chemin absolu
    if (!is_dir($barcodeDir)) {
        mkdir($barcodeDir, 0755, true); // Créer le dossier s'il n'existe pas
    }
    $barcodePath = $barcodeDir . 'barcode-' . $uniqueId . '.png';

    // Écrire l'image du code-barres dans le fichier PNG
    if (file_put_contents($barcodePath, $barcodeImage) === false) {
        die("Erreur : Impossible de créer l'image du code-barres dans le dossier spécifié.");
    }

    // Sauvegarder le chemin de la nouvelle image du code-barres dans la session
    $_SESSION['barcodePath'] = $barcodePath;

    // Sauvegarder les informations du formulaire dans la session
    $_SESSION['formData'] = [
        'firstname'   => htmlspecialchars($_POST['firstname']),
        'lastname'    => htmlspecialchars($_POST['lastname']),
        'address'     => htmlspecialchars($_POST['address']),
        'postalCode'  => htmlspecialchars($_POST['postalCode']),
        'locality'    => htmlspecialchars($_POST['locality']),
        'country'     => htmlspecialchars($_POST['country']),
        'phone'       => htmlspecialchars($_POST['phone']),
        'email'       => htmlspecialchars($_POST['email']),
        'iban'        => htmlspecialchars($_POST['iban']),
        'bankName'    => htmlspecialchars($_POST['bankName']),
        'swift'       => htmlspecialchars($_POST['swift']),
        'idCard'      => htmlspecialchars($_POST['idCard']),
        'expiryDate'  => htmlspecialchars($_POST['expiryDate']),
    ];

    // Utiliser un chemin relatif pour l'affichage de l'image du code-barres
    $relativeBarcodePath = './images/barcodes/barcode-' . $uniqueId . '.png';

    // Récupérer les données du formulaire de la session pour les afficher
    $formData = $_SESSION['formData'];
?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bordereau d'achat</title>
        <link rel="stylesheet" href="css/bordereau.css?<?= rand() ?>">
    </head>

    <body>
        <div class="container">
            <header>
                <div class="header-barcode">
                    <div class="header-title">
                        <h1>Bordereau d'achat</h1>
                    </div>
                    <div class="barcode-section">
                        <p class="date">Créé le <?php echo date('d/m/Y'); ?></p>
                        <!-- Afficher l'image PNG générée -->
                        <img src="<?php echo $relativeBarcodePath; ?>" alt="Code-barres correspondant à l'ID" />
                        <p class="id_barcode"><?php echo $uniqueId; ?></p>
                    </div>
                </div>

                <div class="header-info">
                    <p class="header-info-p">Ce bordereau d’achat est à inclure dans votre colis à destination de l’adresse
                        suivante :</p>
                    <div class="header-address">
                        <h2>METALCASH / SERVICE D'ENVOI POSTAL</h2>
                        <p>Avenue André Ernst 3A, 4800 Verviers, Belgique</p>
                    </div>
                </div>
                <p class="note">
                    NOTE: Les colis pesant moins de 10 kg ne sont pas acceptés (voir les conditions en bas de page).
                </p>
            </header>

            <main>
                <div class="background-image">
                    <img src="./images/logo-HD.png" alt="logo metalcash" />
                </div>


                <div class="separation-benificiary"></div>
                <section class="beneficiary-info">
                    <div class="column">
                        <h3>Identification du bénéficiaire</h3>
                        <div class="info-grid">
                            <p>Prénom: <span><?php echo $formData['firstname']; ?></span></p>
                            <p>Nom de famille: <span><?php echo $formData['lastname']; ?></span></p>
                            <p>Rue: <span><?php echo $formData['address']; ?></span></p>
                            <p>CP, Localité: <span><?php echo $formData['postalCode'] . ', ' . $formData['locality']; ?></span></p>
                            <p>Pays: <span><?php echo $formData['country']; ?></span></p>
                            <p>Téléphone: <span><?php echo $formData['phone']; ?></span></p>
                            <p>E-mail: <span><?php echo $formData['email']; ?></span></p>
                        </div>
                    </div>
                    <div class="column">
                        <h3>Coordonnées bancaires</h3>
                        <div class="info-grid">
                            <p>Titulaire du compte: <span><?php echo $formData['firstname'] . ' ' . $formData['lastname']; ?></span></p>
                            <p>IBAN: <span><?php echo $formData['iban']; ?></span></p>
                            <p>Nom de la banque: <span><?php echo $formData['bankName']; ?></span></p>
                            <p>SWIFT: <span><?php echo $formData['swift']; ?></span></p>
                            <div class="card-info">
                                <p>Numéro de la carte d'identité: <span><?php echo $formData['idCard']; ?></span></p>
                                <p>Date d'expiration de la carte d'identité: <span><?php echo $formData['expiryDate']; ?></span></p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type de métaux</th>
                                <th>Description <span class="th-desc">(facultatif)</span></th>
                                <th>Poids en kg</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($_POST['materialType']) && is_array($_POST['materialType'])) {
                                foreach ($_POST['materialType'] as $index => $type) {
                                    $description = $_POST['description'][$index] ?? 'N/A';
                                    $weight = $_POST['weight'][$index] ?? 'N/A';
                                    echo '<tr>';
                                    echo '<td>' . ($index + 1) . '</td>';
                                    echo '<td>' . htmlspecialchars($type) . '</td>';
                                    echo '<td>' . htmlspecialchars($description) . '</td>';
                                    echo '<td>' . htmlspecialchars($weight) . '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                    </table>
                </section>
            </main>

            <footer>
                <div class="footer-content">
                    <p class="accept-conditions">
                        J’ai pris connaissance des conditions générales et de la déclaration de confidentialité et je les accepte.
                    </p>
                    <p class="certificate">
                        <strong>Certificat de propriété :</strong><br>
                        En tant que personne majeure disposant pleinement de ma capacité juridique, je certifie sur l'honneur que le matériel proposé à la vente par l'entreprise Metalcash sprl m’appartient de manière exclusive et sans restriction, qu'il n'est issu d'aucune activité illégale, qu'il n'est ni mis en gage ni cédé, et qu'il ne contient aucune composante dangereuse.
                    </p>
                    <p class="conditions">
                        <strong>Lire les conditions générales et la déclaration de confidentialité :</strong><br>
                        <a href="https://www.metalcash.be/faq">https://www.metalcash.be/faq</a> — <a href="https://www.metalcash.be/legal">https://www.metalcash.be/legal</a>
                    </p>
                </div>
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <p>Date</p>
                    <div class="signature-line"></div>
                    <p>Signature</p>
                </div>
            </footer>
        </div>
    </body>

    </html>

<?php
} else {
    echo "Erreur lors de l'envoi du formulaire, veuillez réessayer s'il vous plaît.";
}
?>