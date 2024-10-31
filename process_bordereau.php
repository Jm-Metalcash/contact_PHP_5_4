<?php
session_start(); // Démarrer la session

// Connexion à la base de données
$db = new PDO('mysql:host=localhost;dbname=metalcash_contact;charset=utf8', 'root', '');

// Vérifier si le formulaire a été soumis avec les données nécessaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firstname'], $_POST['lastname'], $_POST['email'])) {

    $uniqueId = $_POST['uniqueId'];

    // Préparer l'insertion dans `bordereau_generate`
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

    // Préparer les colis
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

    $_SESSION['uniqueId'] = $uniqueId;

    // Redirection vers bordereau_achat.php après soumission
    header("Location: bordereau_achat.php");
    exit();

} else {
    echo "Erreur : Données de formulaire manquantes.";
}
