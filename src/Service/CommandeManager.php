<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\Paiement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CommandeManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private NumeroCommandeGenerator $numeros,
        private Journalisation $journal,
        #[Autowire('%app.majoration_express%')] private int $majorationExpress,
        #[Autowire('%app.points_par_tranche%')] private int $pointsParTranche,
    ) {
    }

    /** Finalise une commande saisie (numéro, majoration express, créateur) puis l'enregistre. */
    public function enregistrer(Commande $commande, ?User $user, int $acompte = 0, string $modeAcompte = 'especes'): void
    {
        if (null === $commande->getNumero()) {
            $commande->setNumero($this->numeros->generer($commande->getDateDepot()));
            $commande->setCreatedBy($user);
        }
        $commande->setMajorationPourcent($commande->isUrgent() ? $this->majorationExpress : 0);
        if (!$commande->isLivraisonDomicile()) {
            $commande->setFraisLivraison(0);
            $commande->setAdresseLivraison(null);
        }

        if ($acompte > 0 && $commande->getReste() > 0) {
            $this->ajouterPaiement($commande, $acompte, $modeAcompte, null, $user, false);
        }

        $nouvelle = null === $commande->getId();
        $commande->recalculer();
        $this->em->persist($commande);
        $this->em->flush();

        $this->journal->noter($nouvelle ? 'creation' : 'modification', ($nouvelle ? 'Commande créée : ' : 'Commande modifiée : ').$commande->getNumero().' ('.$commande->getTotal().' '.'FCFA)', 'commande', $commande->getId(), $user);
        $this->em->flush();
    }

    public function ajouterPaiement(Commande $commande, int $montant, string $mode, ?string $reference, ?User $user, bool $flush = true): Paiement
    {
        $montant = min($montant, $commande->getReste());
        if ($montant <= 0) {
            throw new \DomainException('Cette commande est déjà soldée.');
        }

        $paiement = (new Paiement())
            ->setMontant($montant)
            ->setMode(isset(Paiement::MODES[$mode]) ? $mode : 'especes')
            ->setReference($reference ?: null)
            ->setDatePaiement(new \DateTimeImmutable())
            ->setCreatedBy($user);
        $commande->addPaiement($paiement);
        $commande->recalculer();
        $this->em->persist($paiement);
        if ($flush) {
            $this->journal->noter('paiement', sprintf('Paiement de %d FCFA (%s) sur %s', $montant, $paiement->getModeLabel(), $commande->getNumero()), 'commande', $commande->getId(), $user);
            $this->em->flush();
        }

        return $paiement;
    }

    /** Change le statut ; à la livraison, date de livraison + points de fidélité. */
    public function changerStatut(Commande $commande, string $statut): void
    {
        if (!isset(Commande::STATUTS[$statut])) {
            throw new \InvalidArgumentException('Statut inconnu.');
        }
        $ancien = $commande->getStatutLabel();
        $commande->setStatut($statut);

        if (Commande::STATUT_LIVRE === $statut) {
            $commande->setDateLivraison(new \DateTimeImmutable());
            if (!$commande->isPointsCredites() && $this->pointsParTranche > 0) {
                $client = $commande->getClient();
                $client->setPointsFidelite($client->getPointsFidelite() + intdiv($commande->getTotal(), $this->pointsParTranche));
                $commande->setPointsCredites(true);
            }
        }
        $this->journal->noter('statut', sprintf('%s : %s → %s', $commande->getNumero(), $ancien, $commande->getStatutLabel()), 'commande', $commande->getId());
        $this->em->flush();
    }
}
