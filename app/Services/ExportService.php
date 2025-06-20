<?php
namespace App\Services;

use App\Models\Devis;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportService
{
    public function exportDevisToPdf(Devis $devis)
    {
        $data = [
            'devis' => $devis,
            'type' => 'devis'
        ];

        return Pdf::loadView('exports.document-pdf', $data)
                  ->setPaper('a4', 'portrait');
    }

    public function exportFactureToPdf(Facture $facture)
    {
        $data = [
            'facture' => $facture,
            'type' => 'facture'
        ];

        return Pdf::loadView('exports.document-pdf', $data)
                  ->setPaper('a4', 'portrait');
    }

    public function exportDevisToExcel(Devis $devis)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configuration de base
        $sheet->setTitle('Devis ' . $devis->numero_devis);
        
        // En-têtes du document
        $this->setDocumentHeaders($sheet, $devis, 'DEVIS');
        
        // Informations client
        $this->setClientInfo($sheet, $devis->client, 8);
        
        // Tableau des articles
        $this->setArticlesTable($sheet, $devis->lignes, 12);
        
        // Totaux
        $lastRow = 12 + count($devis->lignes) + 1;
        $this->setTotals($sheet, $devis, $lastRow);
        
        // Styles
        $this->applyStyles($sheet);
        
        // Sauvegarde temporaire
        $fileName = 'devis_' . $devis->numero_devis . '_' . time() . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        return $filePath;
    }

    public function exportFactureToExcel(Facture $facture)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configuration de base
        $sheet->setTitle('Facture ' . $facture->numero_facture);
        
        // En-têtes du document
        $this->setDocumentHeaders($sheet, $facture, 'FACTURE');
        
        // Informations client
        $this->setClientInfo($sheet, $facture->client, 8);
        
        // Tableau des articles
        $this->setArticlesTable($sheet, $facture->lignes, 12);
        
        // Totaux
        $lastRow = 12 + count($facture->lignes) + 1;
        $this->setTotals($sheet, $facture, $lastRow);
        
        // Informations de paiement
        $this->setPaymentInfo($sheet, $facture, $lastRow + 3);
        
        // Styles
        $this->applyStyles($sheet);
        
        // Sauvegarde temporaire
        $fileName = 'facture_' . $facture->numero_facture . '_' . time() . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        return $filePath;
    }

    private function setDocumentHeaders($sheet, $document, $type)
    {
        // Logo/Nom de l'entreprise
        if (isset($document->company)) {
            $sheet->setCellValue('A1', $document->company->nom);
            $sheet->setCellValue('A2', $document->company->adresse);
            $sheet->setCellValue('A3', $document->company->ville . ', ' . $document->company->pays);
        }
        
        // Type de document et numéro
        $numeroField = $type === 'DEVIS' ? 'numero_devis' : 'numero_facture';
        $sheet->setCellValue('F1', $type);
        $sheet->setCellValue('F2', 'N° ' . $document->$numeroField);
        
        // Dates
        if ($type === 'DEVIS') {
            $sheet->setCellValue('F3', 'Date: ' . $document->date_creation->format('d/m/Y'));
            $sheet->setCellValue('F4', 'Expiration: ' . $document->date_expiration->format('d/m/Y'));
        } else {
            $sheet->setCellValue('F3', 'Date: ' . $document->date_emission->format('d/m/Y'));
            $sheet->setCellValue('F4', 'Échéance: ' . $document->date_echeance->format('d/m/Y'));
        }
    }

    private function setClientInfo($sheet, $client, $startRow)
    {
        $sheet->setCellValue('A' . $startRow, 'CLIENT:');
        $sheet->setCellValue('A' . ($startRow + 1), $client->nom_complet);
        $sheet->setCellValue('A' . ($startRow + 2), $client->adresse);
        $sheet->setCellValue('A' . ($startRow + 3), $client->ville . ', ' . $client->pays);
    }

    private function setArticlesTable($sheet, $lignes, $startRow)
    {
        // En-têtes du tableau
        $headers = ['Description', 'Quantité', 'Prix unitaire', 'Montant HT', 'TVA', 'Montant TTC'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $startRow, $header);
            $col++;
        }
        
        // Lignes du tableau
        $row = $startRow + 1;
        foreach ($lignes as $ligne) {
            $sheet->setCellValue('A' . $row, $ligne->article->description);
            $sheet->setCellValue('B' . $row, $ligne->quantite);
            $sheet->setCellValue('C' . $row, number_format($ligne->prix_unitaire, 2) . ' €');
            $sheet->setCellValue('D' . $row, number_format($ligne->montant_ht, 2) . ' €');
            $sheet->setCellValue('E' . $row, number_format($ligne->montant_tva, 2) . ' €');
            $sheet->setCellValue('F' . $row, number_format($ligne->montant_ttc, 2) . ' €');
            $row++;
        }
    }

    private function setTotals($sheet, $document, $startRow)
    {
        $sheet->setCellValue('E' . $startRow, 'Total HT:');
        $sheet->setCellValue('F' . $startRow, number_format($document->montant_ht, 2) . ' €');
        
        $sheet->setCellValue('E' . ($startRow + 1), 'Total TTC:');
        $sheet->setCellValue('F' . ($startRow + 1), number_format($document->montant_ttc, 2) . ' €');
    }

    private function setPaymentInfo($sheet, $facture, $startRow)
    {
        $sheet->setCellValue('A' . $startRow, 'INFORMATIONS DE PAIEMENT:');
        $sheet->setCellValue('A' . ($startRow + 1), 'Mode de paiement: ' . $facture->mode_paiement);
        $sheet->setCellValue('A' . ($startRow + 2), 'Conditions: ' . $facture->condition_paiement);
        $sheet->setCellValue('A' . ($startRow + 3), 'Devise: ' . $facture->devise);
    }

    private function applyStyles($sheet)
    {
        // Style pour les en-têtes
        $sheet->getStyle('A1:F4')->getFont()->setBold(true);
        $sheet->getStyle('F1')->getFont()->setSize(16);
        
        // Largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        
        // Alignement
        $sheet->getStyle('B:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}