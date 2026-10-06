<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class Paginateur
{
    public function paginer(QueryBuilder $qb, Request $request, int $parDefaut = 25): Pagination
    {
        $parPage = $request->query->getInt('lignes', $parDefaut);
        if (!\in_array($parPage, Pagination::LIGNES_PAR_PAGE, true)) {
            $parPage = $parDefaut;
        }
        $page = max(1, $request->query->getInt('page', 1));

        $qb->setFirstResult(($page - 1) * $parPage)->setMaxResults($parPage);
        $paginator = new Paginator($qb, true);
        $total = \count($paginator);

        // Page demandée au-delà de la fin : on revient à la dernière.
        $pages = max(1, (int) ceil($total / $parPage));
        if ($page > $pages) {
            $page = $pages;
            $qb->setFirstResult(($page - 1) * $parPage);
            $paginator = new Paginator($qb, true);
        }

        return new Pagination(iterator_to_array($paginator->getIterator(), false), $page, $parPage, $total);
    }
}
