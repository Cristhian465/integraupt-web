namespace App\Services;

class AuditoriaExportService
{
    public function generarPdf($filtro)
    {
        // aquí luego conectas DomPDF
        return "PDF_BINARY_DATA";
    }

    public function generarExcel($filtro)
    {
        // aquí luego conectas Laravel Excel
        return "EXCEL_BINARY_DATA";
    }
}