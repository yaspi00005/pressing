<?php

namespace App\Service;

use App\Repository\CommandeRepository;

class NumeroCommandeGenerator
{
    public function __construct(private CommandeRepository $commandes)
    {
    }

    /** Numéro du type P231006-004 : jour de dépôt + compteur journalier. */
    public function generer(\DateTimeImmutable $date): string
    {
        $prefix = 'P'.$date->format('ymd').'-';
        $n = $this->commandes->compterNumerosAvecPrefixe($prefix) + 1;

        return $prefix.str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }
}
