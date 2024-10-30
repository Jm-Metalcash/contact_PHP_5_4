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
    if (!is_dir($barcodeDir)) {
        mkdir($barcodeDir, 0755, true); // Créer le dossier s'il n'existe pas
    }

    // Supprimer l'ancienne image de code-barres si elle existe
    if (isset($_SESSION['barcodePath']) && file_exists($_SESSION['barcodePath'])) {
        unlink($_SESSION['barcodePath']); // Supprimer l'ancienne image
    }

    // Créer une instance du générateur de code-barres et générer l'image
    $generator = new BarcodeGeneratorPNG();
    $barcodeImage = $generator->getBarcode($uniqueId, $generator::TYPE_CODE_128);
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
    $formData = $_SESSION['formData'];

?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bordereaux d'achat</title>
        <link rel="stylesheet" href="css/bordereau.css?<?= rand() ?>">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body>
        <?php
        $randomNumber = rand(1000, 9999);
        // Boucle pour générer un bordereau par colis
        if (isset($_POST['materialType']) && is_array($_POST['materialType'])) {
            foreach ($_POST['materialType'] as $packageId => $materials) {
                $currentUniqueId = $uniqueId . '-' . '00' . $packageId;
        ?>

                <div class="download-pdf downloadPdfButton" data-package-id="<?php echo $packageId; ?>">
                    <div id="downloadPdf">
                        <a href="#" class="btn-slide2" onclick="downloadPdf(<?php echo $packageId; ?>)">
                            <span class="circle2"><i class="fa fa-download"></i></span>
                            <span class="title-hover2">Colis <?php echo $packageId; ?></span>
                            <span class="title2">Télécharger PDF</span>
                        </a>
                    </div>
                </div>

                <div class="container bordereau" id="bordereau-<?php echo $packageId; ?>">
                    <header>
                        <div class="header-barcode">
                            <div class="header-title">
                                <h1>Bordereau d'achat - Colis <?php echo $packageId; ?></h1>
                            </div>
                            <div class="barcode-section">
                                <p class="date">Créé le <?php echo date('d/m/Y'); ?></p>
                                <img src="<?php echo $relativeBarcodePath; ?>" alt="Code-barres correspondant à l'ID" />
                                <p class="id_barcode"><?php echo $currentUniqueId; ?></p>
                            </div>
                        </div>

                        <div class="header-info">
                            <p class="header-info-p">Ce bordereau d’achat est à inclure dans votre colis à destination de l’adresse suivante :</p>
                            <div class="header-address">
                                <h2>METALCASH / SERVICE D'ENVOI POSTAL</h2>
                                <p>Avenue André Ernst 3A, 4800 Verviers, Belgique</p>
                            </div>
                        </div>
                        <p class="note">NOTE: Les colis pesant moins de 10 kg ne sont pas acceptés (voir les conditions en bas de page).</p>
                    </header>

                    <main>
                        <!-- <div class="background-image">
                            <img src="./images/logo-HD.png" alt="logo metalcash" />
                        </div> -->

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
                                    foreach ($materials as $index => $type) {
                                        $description = $_POST['description'][$packageId][$index] ?? 'N/A';
                                        $weight = $_POST['weight'][$packageId][$index] ?? 'N/A';
                                        echo '<tr>';
                                        echo '<td>' . ($index + 1) . '</td>';
                                        echo '<td>' . htmlspecialchars($type) . '</td>';
                                        echo '<td>' . htmlspecialchars($description) . '</td>';
                                        echo '<td>' . htmlspecialchars($weight) . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </section>
                    </main>
                    <footer>
                        <div class="footer-content">
                            <p class="accept-conditions">J’ai pris connaissance des conditions générales et de la déclaration de confidentialité et je les accepte.</p>
                            <p class="certificate"><strong>Certificat de propriété :</strong><br>En tant que personne majeure disposant pleinement de ma capacité juridique, je certifie sur l'honneur que le matériel proposé à la vente par l'entreprise Metalcash sprl m’appartient de manière exclusive et sans restriction, qu'il n'est issu d'aucune activité illégale, qu'il n'est ni mis en gage ni cédé, et qu'il ne contient aucune composante dangereuse.</p>
                            <p class="conditions"><strong>Lire les conditions générales et la déclaration de confidentialité :</strong><br><a href="https://www.metalcash.be/faq">https://www.metalcash.be/faq</a> — <a href="https://www.metalcash.be/legal">https://www.metalcash.be/legal</a></p>
                        </div>
                        <div class="signature-section">
                            <div class="signature-line"></div>
                            <p>Date</p>
                            <div class="signature-line"></div>
                            <p>Signature</p>
                        </div>
                    </footer>
                </div>
        <?php
            }
        }
        ?>
        <script>
            function downloadPdf(packageId) {
                // Cibler le conteneur de bordereau correspondant
                const element = document.getElementById('bordereau-' + packageId);
                const downloadButton = document.querySelector('.download-pdf[data-package-id="' + packageId + '"]');

                // Masquer le bouton de téléchargement
                downloadButton.style.visibility = 'hidden';

                const options = {
                    margin: [10, 15, 10, 15],
                    filename: 'bordereau_achat_metalcash_colis_' + packageId + '.pdf',
                    html2canvas: {
                        scrollX: 0,
                        scrollY: 0,
                        scale: 2,
                        useCORS: true
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait'
                    }
                };

                html2pdf().from(element).set(options).save().then(() => {
                    // Réafficher le bouton après la génération du PDF
                    downloadButton.style.visibility = 'visible';
                });
            }
        </script>

    </body>

    </html>

<?php
} else {
    echo "Erreur lors de l'envoi du formulaire, veuillez réessayer s'il vous plaît.";
}
?>