<?php

namespace App\Twig;

use App\Entity\Commande;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire('%app.devise%')] private string $devise,
        #[Autowire('%app.indicatif_pays%')] private string $indicatif,
        #[Autowire('%app.pressing.nom%')] private string $nomPressing,
    ) {
    }

    public function getFilters(): array
    {
        return [new TwigFilter('money', $this->money(...))];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('whatsapp_link', $this->whatsappLink(...)),
            new TwigFunction('alertes', [AlertesRuntime::class, 'alertes']),
        ];
    }

    public function money(int|float|null $montant): string
    {
        return number_format((float) $montant, 0, ',', "\u{202F}").' '.$this->devise;
    }

    /** Lien WhatsApp (sans API) avec message prérempli selon le statut de la commande. */
    public function whatsappLink(Commande $commande): string
    {
        $client = $commande->getClient();
        $nom = $client->getPrenom();
        $numero = $commande->getNumero();
        $reste = $commande->getReste();
        $suffixeReste = $reste > 0 ? ' Reste à payer : '.$this->money($reste).'.' : '';

        $texte = match ($commande->getStatut()) {
            Commande::STATUT_PRET => "Bonjour $nom, votre commande $numero est prête, vous pouvez venir la retirer.$suffixeReste",
            Commande::STATUT_LIVRE => "Bonjour $nom, merci pour votre confiance ! Commande $numero livrée.",
            Commande::STATUT_ANNULE => "Bonjour $nom, votre commande $numero a été annulée.",
            default => "Bonjour $nom, nous avons bien reçu votre commande $numero. Retrait prévu le ".$commande->getDateRetraitPrevue()->format('d/m/Y à H:i').'.'.$suffixeReste,
        };

        return 'https://wa.me/'.$this->indicatif.preg_replace('/\D/', '', (string) $client->getTelephones()).'?text='.rawurlencode($texte.' - '.$this->nomPressing);
    }
}
