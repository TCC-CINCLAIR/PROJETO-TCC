<?php
// PDF com fonte padrão e WinAnsi: dispensa dependências e não grava arquivos públicos.
function gerarPdfRelatorio(string $titulo, string $periodo, string $texto): string
{
    $linhas = [];
    $paragrafos = array_merge([$titulo, $periodo, '', 'Resumo dos relatos à Clarice. Confira as informações antes da consulta.', ''], preg_split('/\R/u', $texto));
    foreach ($paragrafos as $paragrafo) {
        $linha = '';
        foreach (preg_split('/\s+/u', trim($paragrafo), -1, PREG_SPLIT_NO_EMPTY) as $palavra) {
            // Courier 11 usa 6,6 pontos por caractere: 72 caracteres cabem em 499 pontos.
            foreach (mb_str_split($palavra, 65, 'UTF-8') as $parte) {
                if (mb_strlen($linha . ' ' . $parte, 'UTF-8') > 72) { $linhas[] = $linha; $linha = ''; }
                $linha .= ($linha === '' ? '' : ' ') . $parte;
            }
        }
        $linhas[] = $linha;
    }
    $paginas = array_chunk($linhas, 45);
    $objetos = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 2 => '',
        3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>'];
    $kids = [];
    foreach ($paginas as $indice => $linhasPagina) {
        $id = 4 + $indice * 2; $fluxoId = $id + 1; $kids[] = "$id 0 R";
        $fluxo = "BT /F1 11 Tf 15 TL 48 790 Td\n";
        foreach ($linhasPagina as $linha) {
            $ansi = iconv('UTF-8', 'Windows-1252//TRANSLIT', $linha);
            $ansi = preg_replace('/[\x00-\x1F\x7F]/', ' ', $ansi === false ? '' : $ansi);
            $escapado = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ansi);
            $fluxo .= "($escapado) Tj T*\n";
        }
        $fluxo .= "ET\nBT /F1 9 Tf 48 35 Td (" . ($indice + 1) . ' / ' . count($paginas) . ") Tj ET\n";
        $objetos[$id] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents $fluxoId 0 R >>";
        $objetos[$fluxoId] = '<< /Length ' . strlen($fluxo) . ">>\nstream\n" . $fluxo . 'endstream';
    }
    $objetos[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
    $pdf = "%PDF-1.4\n"; $offsets = [0];
    foreach ($objetos as $id => $objeto) { $offsets[$id] = strlen($pdf); $pdf .= "$id 0 obj\n$objeto\nendobj\n"; }
    $xref = strlen($pdf); $total = count($objetos) + 1;
    $pdf .= "xref\n0 $total\n0000000000 65535 f \n";
    for ($id = 1; $id < $total; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
    return $pdf . "trailer\n<< /Size $total /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
}
