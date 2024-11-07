<?php
session_start(); // Démarrer la session

// Importer PHPMailer et ses dépendances
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '.\vendor\phpmailer\phpmailer\src\Exception.php';
require '.\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require '.\vendor\phpmailer\phpmailer\src\SMTP.php';

// Connexion à la base de données
include 'config.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firstname'], $_POST['lastname'], $_POST['email'])) {

    $uniqueId = $_POST['uniqueId'];

    // Insertion des données dans `bordereau_generate`
    $insertBordereau = $db->prepare("
        INSERT INTO bordereau_generate (id, status, firstname, lastname, email, phone, address, postalCode, locality, country, accountHolder, iban, bankName, swift, idCard, expiryDate)
        VALUES (:id, :status, :firstname, :lastname, :email, :phone, :address, :postalCode, :locality, :country, :accountHolder, :iban, :bankName, :swift, :idCard, :expiryDate)
    ");

    $insertBordereau->execute([
        ':id' => $uniqueId,
        ':status' => 1,
        ':firstname' => $_POST['firstname'],
        ':lastname' => $_POST['lastname'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'],
        ':address' => $_POST['address'],
        ':postalCode' => $_POST['postalCode'],
        ':locality' => $_POST['locality'],
        ':country' => $_POST['country'],
        ':accountHolder' => $_POST['firstname'] . ' ' . $_POST['lastname'],
        ':iban' => $_POST['iban'],
        ':bankName' => $_POST['bankName'],
        ':swift' => $_POST['swift'],
        ':idCard' => $_POST['idCard'],
        ':expiryDate' => $_POST['expiryDate']
    ]);

    // Insertion des informations des colis et des matériaux dans la base de données
    if (isset($_POST['materialType']) && is_array($_POST['materialType'])) {
        $insertPackage = $db->prepare("
            INSERT INTO package_informations (id, bordereau_id, package_number)
            VALUES (:id, :bordereau_id, :package_number)
        ");

        $insertContent = $db->prepare("
            INSERT INTO package_content (package_id, material_type, description, weight)
            VALUES (:package_id, :material_type, :description, :weight)
        ");

        foreach ($_POST['materialType'] as $packageId => $materials) {
            $currentUniqueId = $uniqueId . '-' . sprintf('%02d', $packageId);

            $insertPackage->execute([
                ':id' => $currentUniqueId,
                ':bordereau_id' => $uniqueId,
                ':package_number' => $packageId
            ]);

            foreach ($materials as $index => $materialType) {
                $insertContent->execute([
                    ':package_id' => $currentUniqueId,
                    ':material_type' => $materialType,
                    ':description' => $_POST['description'][$packageId][$index] ?? null,
                    ':weight' => $_POST['weight'][$packageId][$index] ?? null
                ]);
            }
        }
    }

    // Récupérer les informations de chaque colis et leurs contenus
    $packagesQuery = $db->prepare("
        SELECT p.id AS package_id, p.package_number, c.material_type, c.description, c.weight 
        FROM package_informations p 
        LEFT JOIN package_content c ON p.id = c.package_id 
        WHERE p.bordereau_id = :bordereau_id 
        ORDER BY p.package_number, c.material_type
    ");
    $packagesQuery->execute([':bordereau_id' => $uniqueId]);
    $packagesData = $packagesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Construire les tables HTML pour chaque colis
    $packagesTables = "";
    $currentPackageId = null;
    foreach ($packagesData as $data) {
        if ($currentPackageId !== $data['package_id']) {
            if ($currentPackageId !== null) {
                $packagesTables .= "</table>";
            }
            $currentPackageId = $data['package_id'];

            $packagesTables .= "
            <div style='background-color: #f9f9f9; padding: 10px; margin-bottom: 10px; border-radius: 8px;'>
                <h3 style='color: #333; font-size: 18px; margin-top: 10px;'>Colis {$data['package_number']} ({$data['package_id']})</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;'>Type de Matériau</th>
                        <th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;'>Description</th>
                        <th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;'>Poids (kg)</th>
                    </tr>";
        }

        $packagesTables .= "
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$data['material_type']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$data['description']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$data['weight']}</td>
            </tr>";
    }
    $packagesTables .= "</table></div>";

    // Contenu du message de notification en HTML
    $subject = "[Metalcash - Notification] Nouveau bordereau généré ({$uniqueId})";
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>$subject</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
        <div style='max-width: 900px; margin: auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);'>
            <div style='text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eaeaea;'>
                <h1 style='font-size: 16px; color: #005893;'>Metalcash</h1>
                <h2 style='font-size: 24px; color: #333;'>Détails du Bordereau</h2>
            </div>
            <div style='padding: 20px;'>
                <p style='color: #555; font-size: 16px;'><strong>ID Bordereau :</strong> {$uniqueId}</p>
                <p style='color: #555; font-size: 16px;'><strong>Prénom :</strong> {$_POST['firstname']}</p>
                <p style='color: #555; font-size: 16px;'><strong>Nom :</strong> {$_POST['lastname']}</p>
                <p style='color: #555; font-size: 16px;'><strong>E-mail :</strong> <a href='mailto:{$_POST['email']}' style='color: #1d72b8;'>{$_POST['email']}</a></p>
                <p style='color: #555; font-size: 16px;'><strong>Téléphone :</strong> {$_POST['phone']}</p>
                <p style='color: #555; font-size: 16px;'><strong>Adresse :</strong> {$_POST['address']}</p>
                <p style='color: #555; font-size: 16px;'><strong>Localité :</strong> {$_POST['locality']}, {$_POST['postalCode']}, {$_POST['country']}</p>
                <p style='color: #555; font-size: 16px;'><strong>Titulaire du compte :</strong> {$_POST['firstname']} {$_POST['lastname']}</p>
                <p style='color: #555; font-size: 16px;'><strong>IBAN :</strong> {$_POST['iban']}</p>
                <p style='color: #555; font-size: 16px;'><strong>Banque :</strong> {$_POST['bankName']} ({$_POST['swift']})</p>
                <p style='color: #555; font-size: 16px;'><strong>Nombre de colis :</strong> " . count(array_unique(array_column($packagesData, 'package_id'))) . "</p>
                <h3 style='color: #333; font-size: 20px; margin-top: 20px;'>Récapitulatif des colis :</h3>
                $packagesTables
            </div>
            <div style='text-align: center; padding-top: 20px; border-top: 1px solid #eaeaea; margin-top: 20px;'>
                <p style='font-size: 12px; color: #888;'>Cet e-mail a été envoyé automatiquement depuis Metalcash.</p>
            </div>
        </div>
    </body>
    </html>
    ";


    // Créer une instance de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'itmetalcash@gmail.com';
        $mail->Password = 'zqlsdmutmhkguhvh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Définir la langue sur français et encodage UTF-8
        $mail->setLanguage('fr', '.\vendor\phpmailer\phpmailer\language');
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Configuration de l'e-mail
        $mail->setFrom('itmetalcash@gmail.com', 'Metalcash');
        $mail->addAddress('jm@metalcash.be');

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Envoyer l'e-mail
        $mail->send();
        echo "Notification envoyée avec succès.";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
    }

    $_SESSION['uniqueId'] = $uniqueId;
    header("Location: bordereau_achat.php");
    exit();
} else {
    echo "Erreur : Données de formulaire manquantes.";
}
