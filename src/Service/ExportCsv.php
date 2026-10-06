<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Export tableur (CSV ; séparateur point-virgule + BOM UTF-8 : s'ouvre directement dans Excel). */
class ExportCsv
{
    /**
     * @param string[]                 $entetes
     * @param iterable<array<mixed>>   $lignes
     */
    public function repondre(string $nomFichier, array $entetes, iterable $lignes): StreamedResponse
    {
        $response = new StreamedResponse(static function () use ($entetes, $lignes): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $entetes, ';');
            foreach ($lignes as $ligne) {
                fputcsv($out, array_map(static fn ($v) => $v instanceof \DateTimeInterface ? $v->format('d/m/Y H:i') : $v, $ligne), ';');
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$nomFichier.'-'.date('Ymd-His').'.csv"');

        return $response;
    }
}
