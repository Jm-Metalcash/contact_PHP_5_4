<?php
session_start(); // Démarrer la session

// Importer la bibliothèque pour le code-barres
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Connexion à la base de données
$db = new PDO('mysql:host=localhost;dbname=metalcash_contact;charset=utf8', 'root', '');

// Vérifier si l'ID unique est fourni en session ou via la requête
if (isset($_SESSION['uniqueId'])) {
    $uniqueId = $_SESSION['uniqueId'];
} else {
    die("Erreur : Aucun ID unique disponible.");
}

// Récupérer les informations du bordereau depuis la base de données
$query = $db->prepare("
    SELECT * FROM bordereau_generate WHERE id = :id
");
$query->execute([':id' => $uniqueId]);
$formData = $query->fetch(PDO::FETCH_ASSOC);

if (!$formData) {
    die("Erreur : Aucun bordereau trouvé pour cet ID.");
}

// Récupérer les informations des colis et leur contenu
$packagesQuery = $db->prepare("
    SELECT * FROM package_informations WHERE bordereau_id = :bordereau_id
");
$packagesQuery->execute([':bordereau_id' => $uniqueId]);
$packages = $packagesQuery->fetchAll(PDO::FETCH_ASSOC);

$packageContentQuery = $db->prepare("
    SELECT * FROM package_content WHERE package_id = :package_id
");

// Dossier des codes-barres
$barcodeDir = __DIR__ . '/images/barcodes/';

// Créer une instance du générateur de code-barres
$generator = new BarcodeGeneratorPNG();

// Fonction pour vider le dossier des codes-barres
function clearBarcodeDirectory($directory)
{
    if (is_dir($directory)) {
        $files = glob($directory . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// Nettoyer le dossier des codes-barres
clearBarcodeDirectory($barcodeDir);

// Générer le code-barres général basé sur $uniqueId
$barcodeImageGeneral = $generator->getBarcode($uniqueId, $generator::TYPE_CODE_128);
$barcodePathGeneral = $barcodeDir . 'barcode-general-' . $uniqueId . '.png';

if (file_put_contents($barcodePathGeneral, $barcodeImageGeneral) === false) {
    die("Erreur : Impossible de créer l'image du code-barres général.");
}

$relativeBarcodePathGeneral = './images/barcodes/barcode-general-' . $uniqueId . '.png';
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
    <div id="content">
        <?php foreach ($packages as $package): ?>
            <?php
            $formattedPackageId = sprintf('%02d', $package['package_number']);
            $currentUniqueId = $uniqueId . '-' . $formattedPackageId;
            $barcodeImage = $generator->getBarcode($currentUniqueId, $generator::TYPE_CODE_128);
            $barcodePath = $barcodeDir . 'barcode-' . $currentUniqueId . '.png';

            if (file_put_contents($barcodePath, $barcodeImage) === false) {
                die("Erreur : Impossible de créer l'image du code-barres pour le colis {$package['package_number']}.");
            }

            $relativeBarcodePath = './images/barcodes/barcode-' . $currentUniqueId . '.png';

            // Charger le contenu du colis
            $packageContentQuery->execute([':package_id' => $package['id']]);
            $materials = $packageContentQuery->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="download-pdf downloadPdfButton" data-package-id="<?php echo $package['package_number']; ?>">
                <div id="downloadPdf">
                    <a href="#" class="btn-slide2" onclick="downloadPdf('<?php echo $package['package_number']; ?>')">
                        <span class="circle2"><i class="fa fa-download"></i></span>
                        <span class="title-hover2">Colis <?php echo $package['package_number']; ?></span>
                        <span class="title2">Télécharger PDF</span>
                    </a>
                </div>
            </div>


            <div class="pdf-container">
                <div class="container bordereau" id="bordereau-<?= $package['package_number']; ?>">
                    <header>
                        <div class="header-barcode">
                            <div class="header-title">
                                <h1>Bordereau d'achat <span class="package-ref"><?= $package['package_number'] ?>/<?= count($packages); ?></span></h1>
                            </div>
                            <div class="barcode-section">
                                <p class="date">Créé le <?= date('d/m/Y'); ?></p>
                                <img src="<?= $relativeBarcodePath; ?>" alt="Metalcash - Bordereau ID" />
                                <p class="id_barcode"><?= $currentUniqueId ?></p>
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
                        <section class="beneficiary-info">
                            <div class="column">
                                <h3>Identification du bénéficiaire</h3>
                                <div class="info-grid">
                                    <p>Prénom: <span><?= $formData['firstname']; ?></span></p>
                                    <p>Nom de famille: <span><?= $formData['lastname']; ?></span></p>
                                    <p>Rue: <span><?= $formData['address']; ?></span></p>
                                    <p>CP, Localité: <span><?= $formData['postalCode'] . ', ' . $formData['locality']; ?></span></p>
                                    <p>Pays: <span><?= $formData['country']; ?></span></p>
                                    <p>Téléphone: <span><?= $formData['phone']; ?></span></p>
                                    <p>E-mail: <span><?= $formData['email']; ?></span></p>
                                </div>
                            </div>
                            <div class="column">
                                <h3>Coordonnées bancaires</h3>
                                <div class="info-grid">
                                    <p>Titulaire du compte: <span><?= $formData['firstname'] . ' ' . $formData['lastname']; ?></span></p>
                                    <p>IBAN: <span><?= $formData['iban']; ?></span></p>
                                    <p>Nom de la banque: <span><?= $formData['bankName']; ?></span></p>
                                    <p>SWIFT: <span><?= $formData['swift']; ?></span></p>
                                    <div class="card-info">
                                        <p>Numéro de la carte d'identité: <span><?= $formData['idCard']; ?></span></p>
                                        <p>Date d'expiration de la carte d'identité: <span><?= $formData['expiryDate']; ?></span></p>
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
                                    <?php foreach ($materials as $index => $material): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= htmlspecialchars($material['material_type']); ?></td>
                                            <td><?= htmlspecialchars($material['description'] ?? 'N/A'); ?></td>
                                            <td><?= htmlspecialchars($material['weight'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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

                    <div class="content-cut-out">
                        <i class="fa-solid fa-scissors scissors-icon"></i>
                        <p>N'oubliez pas de découper la partie ci-dessous et de l'apposer à l'extérieur du colis bien visible</p>
                        <i class="fa-solid fa-scissors scissors-icon2"></i>
                        <div class="informations-cut">
                            <!-- Code-barres spécifique au colis -->
                            <img src="<?= $relativeBarcodePath; ?>" alt="Metalcash-package-id" />
                            <span class="cut-uniqueId"><?= $currentUniqueId ?></span>
                            <span class="cut-packageId">Colis <?= $package['package_number'] ?>/<?= count($packages); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="footer-btn-back">
            <a href="/" class="formbold-btn">Retour vers l'accueil</a>
        </div>
    </div>

    <script>
        function downloadPdf(packageId) {
            // Sélectionne l'élément correspondant au packageId pour le téléchargement
            const element = document.getElementById('bordereau-' + packageId);
            const downloadButton = document.querySelector('.download-pdf[data-package-id="' + packageId + '"]');

            // Masquer le bouton de téléchargement temporairement pendant la génération PDF
            downloadButton.style.visibility = 'hidden';

            const options = {
                margin: [5, 10, 0, 13],
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

            // Générer et télécharger le PDF
            html2pdf().from(element).set(options).save().then(() => {
                downloadButton.style.visibility = 'visible'; // Rendre le bouton visible après la génération PDF
            });
        }
    </script>

</body>

</html>