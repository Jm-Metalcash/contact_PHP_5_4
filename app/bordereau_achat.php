<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'lang/lang.fr_be.php';

session_start(); // Démarrer la session

// Importer la bibliothèque pour le code-barres
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

include 'config.php';

// Connexion à la base de données
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
} catch (PDOException $e) {
    die("Erreur : Impossible de se connecter à la base de données.");
}

// Vérifier si l'ID unique est fourni en session ou via la requête
if (isset($_SESSION['uniqueId'])) {
    $uniqueId = $_SESSION['uniqueId'];
} else {
    die("Erreur : Aucun ID unique disponible.");
}

// Récupérer les informations du bordereau depuis la base de données
try {
    $query = $db->prepare("SELECT * FROM bordereau_generate WHERE id = :id");
    $query->execute(array(':id' => $uniqueId));
    $formData = $query->fetch(PDO::FETCH_ASSOC);

    if (!$formData) {
        throw new Exception("Erreur : Aucun bordereau trouvé pour cet ID.");
    }
} catch (Exception $e) {
    error_log("Erreur dans la récupération du bordereau : " . $e->getMessage(), 0);
    die("Une erreur est survenue, veuillez réessayer plus tard.");
}

// Vérifier les données importantes pour éviter les erreurs d'affichage
if (empty($formData['iban']) || empty($formData['phone'])) {
    die("Erreur : Certaines informations de contact sont manquantes.");
}

// Récupérer les informations des colis
try {
    $packagesQuery = $db->prepare("SELECT * FROM package_informations WHERE bordereau_id = :bordereau_id");
    $packagesQuery->execute(array(':bordereau_id' => $uniqueId));
    $packages = $packagesQuery->fetchAll(PDO::FETCH_ASSOC);

    error_log("Nombre de colis récupérés pour le bordereau $uniqueId : " . count($packages));
    $packageContentQuery = $db->prepare("SELECT * FROM package_content WHERE package_id = :package_id");
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des informations de colis : " . $e->getMessage(), 0);
    die("Une erreur est survenue lors de la récupération des informations des colis.");
}

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
    <title><?php echo htmlspecialchars(NEW_TITLE_PAGE_BORDEREAU); ?> - Metalcash</title>
    <link rel="stylesheet" href="css/bordereau.css?<?php echo rand(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div id="content">
        <?php foreach ($packages as $package): ?>
            <?php
            $formattedPackageId = sprintf('%02d', $package['package_number']);
            $currentUniqueId = $uniqueId . '-' . $formattedPackageId;

            error_log("Récupération du contenu pour le colis avec ID : " . $package['id']);


            $barcodeImage = $generator->getBarcode($currentUniqueId, $generator::TYPE_CODE_128);
            $barcodePath = $barcodeDir . 'barcode-' . $currentUniqueId . '.png';

            if (file_put_contents($barcodePath, $barcodeImage) === false) {
                die("Erreur : Impossible de créer l'image du code-barres pour le colis {$package['package_number']}.");
            }

            $relativeBarcodePath = './images/barcodes/barcode-' . $currentUniqueId . '.png';

            // Charger le contenu du colis
            $packageContentQuery->execute(array(':package_id' => $package['id']));
            $materials = $packageContentQuery->fetchAll(PDO::FETCH_ASSOC);

            error_log("Nombre de matériaux récupérés pour le colis {$package['id']} : " . count($materials));
            ?>

            <div class="download-pdf downloadPdfButton" data-package-id="<?php echo htmlspecialchars($package['package_number']); ?>">
                <div id="downloadPdf">
                    <a href="#" class="btn-slide2" onclick="downloadPdf('<?php echo htmlspecialchars($package['package_number']); ?>')">
                        <span class="circle2"><i class="fa fa-download"></i></span>
                        <span class="title-hover2"><?php echo htmlspecialchars(BTN_TEXT2_DOWNLOAD_PDF); ?> <?php echo htmlspecialchars($package['package_number']); ?></span>
                        <span class="title2"><?php echo htmlspecialchars(BTN_DOWNLOAD_PDF); ?></span>
                    </a>
                </div>
            </div>

            <div class="pdf-container">
                <div class="container bordereau" id="bordereau-<?php echo htmlspecialchars($package['package_number']); ?>">
                    <header>
                        <div class="header-barcode">
                            <div class="header-title">
                                <h1><?php echo htmlspecialchars(TITLE_HEADER_BORDEREAU); ?> <span class="package-ref"><?php echo htmlspecialchars($package['package_number']) ?>/<?php echo count($packages); ?></span></h1>
                            </div>
                            <div class="barcode-section">
                                <p class="date"><?php echo htmlspecialchars(DATE_GENERATE_BARRE_CODE); ?> <?php echo date('d/m/Y'); ?></p>
                                <img src="<?php echo htmlspecialchars($relativeBarcodePath); ?>" alt="Metalcash - Bordereau ID" />
                                <p class="id_barcode"><?php echo htmlspecialchars($currentUniqueId); ?></p>
                            </div>
                        </div>
                        <div class="header-info">
                            <p class="header-info-p"><?php echo htmlspecialchars(NEW_BORDEREAU_DESCRIPTION); ?></p>
                            <div class="header-address">
                                <h2>METALCASH / <?php echo htmlspecialchars(HEADER_BORDEREAU_INFORMATIONS); ?></h2>
                                <p><?php echo htmlspecialchars(NEW_BORDEREAU_ADDRESS); ?></p>
                            </div>
                        </div>
                        <p class="note"><?php echo htmlspecialchars(NEW_NOTE_BORDEREAU); ?></p>
                    </header>

                    <main>
                        <section class="beneficiary-info">
                            <div class="column">
                                <h3><?php echo htmlspecialchars(BENEFICIARY_INFO); ?></h3>
                                <div class="info-grid">
                                    <p><?php echo htmlspecialchars(NEW_FIELD_FIRSTNAME); ?>: <span><?php echo htmlspecialchars($formData['firstname']); ?></span></p>
                                    <p><?php echo htmlspecialchars(NEW_FIELD_LASTNAME); ?>: <span><?php echo htmlspecialchars($formData['lastname']); ?></span></p>
                                    <p><?php echo htmlspecialchars(BENEFICIARY_INFO_STREET); ?>: <span><?php echo htmlspecialchars($formData['address']); ?></span></p>
                                    <p><?php echo htmlspecialchars(BENEFICIARY_INFO_LOCALITY); ?>: <span><?php echo htmlspecialchars($formData['postalCode']) . ', ' . htmlspecialchars($formData['locality']); ?></span></p>
                                    <p><?php echo htmlspecialchars(NEW_FIELD_COUNTRY); ?>: <span><?php echo htmlspecialchars($formData['country']); ?></span></p>
                                    <p><?php echo htmlspecialchars(BENEFICIARY_INFO_PHONE); ?>: <span><?php echo htmlspecialchars($formData['phone']); ?></span></p>
                                    <p><?php echo htmlspecialchars(NEW_FIELD_EMAIL); ?>: <span><?php echo htmlspecialchars($formData['email']); ?></span></p>
                                </div>
                            </div>
                            <div class="column">
                                <h3><?php echo htmlspecialchars(TITLE_HEADER_BANK); ?></h3>
                                <div class="info-grid">
                                    <p><?php echo htmlspecialchars(NEW_FIELD_ACCOUNT_HOLDER); ?>: <span><?php echo htmlspecialchars($formData['firstname'] . ' ' . $formData['lastname']); ?></span></p>
                                    <p><?php echo htmlspecialchars(NEW_FIELD_IBAN); ?>: <span><?php echo htmlspecialchars($formData['iban']); ?></span></p>
                                    <p><?php echo htmlspecialchars(NEW_FIELD_BANKNAME); ?>: <span><?php echo htmlspecialchars($formData['bankName']); ?></span></p>
                                    <p><?php echo htmlspecialchars(NEW_FIELD_SWIFT); ?>: <span><?php echo htmlspecialchars($formData['swift']); ?></span></p>
                                    <div class="card-info">
                                        <p><?php echo htmlspecialchars(NEW_FIELD_ID_CARD_BORDEREAU); ?>: <span><?php echo htmlspecialchars($formData['idCard']); ?></span></p>
                                        <p><?php echo htmlspecialchars(NEW_FIELD_ID_EXPIRY_BORDEREAU); ?>: <span class="expiry-date-ci"><?php echo htmlspecialchars($formData['expiryDate']); ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="table-section">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo htmlspecialchars(NEW_FIELD_TYPE_METAL); ?></th>
                                        <th><?php echo htmlspecialchars(NEW_FIELD_DESCRIPTION_PACKAGE); ?></th>
                                        <th><?php echo htmlspecialchars(NEW_FIELD_WEIGHT); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materials as $index => $material): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($material['material_type']); ?></td>
                                            <td><?php echo htmlspecialchars(isset($material['description']) ? $material['description'] : 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(isset($material['weight']) ? $material['weight'] : 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </section>
                    </main>

                    <footer>
                        <div class="footer-content">
                            <p class="accept-conditions"><?php echo htmlspecialchars(BORDEREAU_CONDITION_ACCEPT); ?></p>
                            <p class="certificate"><strong><?php echo htmlspecialchars(BORDEREAU_CERTIFCATE_TITLE); ?> :</strong><br><?php echo htmlspecialchars(BORDEREAU_CERTIFCATE_DESCRIPTION); ?></p>
                            <p class="conditions"><strong><?php echo htmlspecialchars(BORDEREAU_CONDITIONS_TITLE); ?> :</strong><br><a href="<?php echo htmlspecialchars(BORDEREAU_CONDITIONS_LINK1); ?>"><?php echo htmlspecialchars(BORDEREAU_CONDITIONS_LINK1); ?></a> — <a href="<?php echo htmlspecialchars(BORDEREAU_CONDITIONS_LINK2); ?>"><?php echo htmlspecialchars(BORDEREAU_CONDITIONS_LINK2); ?></a></p>
                        </div>
                        <div class="signature-section">
                            <p><?php echo htmlspecialchars(BORDEREAU_DATE); ?></p>
                            <p class="signature-name"><?php echo BORDEREAU_SIGNATURE_NAME; ?></p>
                            <p><?php echo htmlspecialchars(BORDEREAU_SIGNATURE); ?></p>
                        </div>
                    </footer>

                    <div class="content-cut-out">
                        <i class="fa-solid fa-scissors scissors-icon"></i>
                        <p><?php echo htmlspecialchars(BORDEREAU_CUTE_TEXT); ?></p>
                        <i class="fa-solid fa-scissors scissors-icon2"></i>
                        <div class="informations-cut">
                            <img src="<?php echo htmlspecialchars($relativeBarcodePath); ?>" alt="Metalcash-package-id" />
                            <span class="cut-uniqueId"><?php echo htmlspecialchars($currentUniqueId); ?></span>
                            <span class="cut-packageId"><?php echo htmlspecialchars(NEW_TITLE_INDEX_PACKAGE); ?> <?php echo htmlspecialchars($package['package_number']); ?>/<?php echo count($packages); ?></span>
                            <span class="cut-name"><?php echo htmlspecialchars($formData['firstname'] . ' ' . $formData['lastname'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>

        <div class="footer-btn-back">
            <a href="/" class="formbold-btn"><?php echo htmlspecialchars(BORDEREAU_BTN_BACK); ?></a>
        </div>
    </div>

    <script>
        function downloadPdf(packageId) {
            const element = document.getElementById('bordereau-' + packageId);
            const downloadButton = document.querySelector('.download-pdf[data-package-id="' + packageId + '"]');

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

            html2pdf().from(element).set(options).save().then(function() {
                downloadButton.style.visibility = 'visible';
            });
        }
    </script>

</body>

</html>
