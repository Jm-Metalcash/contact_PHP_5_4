<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $pdfDir = __DIR__ . '/temp_pdfs/';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }

    // Enregistrer le fichier PDF dans le dossier `temp_pdfs`
    $filePath = $pdfDir . basename($_FILES['pdf']['name']);
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $filePath)) {
        echo json_encode(['success' => true, 'file' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
