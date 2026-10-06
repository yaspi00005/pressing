<?php

namespace App\Service;

use App\Entity\Journal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/** Enregistre « qui a fait quoi » ; affiché au bas des fiches et dans Administration > Journal. */
class Journalisation
{
    public function __construct(private EntityManagerInterface $em, private Security $security)
    {
    }

    /** Ajoute une entrée au journal (sera écrite au prochain flush). */
    public function noter(string $action, string $libelle, ?string $cible = null, ?int $cibleId = null, ?User $user = null): void
    {
        $user ??= $this->security->getUser();
        $entree = (new Journal())
            ->setUser($user instanceof User ? $user : null)
            ->setAction($action)
            ->setLibelle($libelle)
            ->setCible($cible)
            ->setCibleId($cibleId);
        $this->em->persist($entree);
    }
}
