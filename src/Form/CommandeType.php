<?php

namespace App\Form;

use App\Entity\Clients;
use App\Entity\Commande;
use App\Entity\Paiement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Clients::class,
                'choice_label' => fn (Clients $c) => $c->getNomComplet().' — '.$c->getTelephones(),
                'query_builder' => fn ($r) => $r->createQueryBuilder('c')->orderBy('c.nom')->addOrderBy('c.prenom'),
                'placeholder' => 'Choisir un client…',
                'label' => 'Client',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-select', 'id' => 'commande_client'],
            ])
            ->add('dateRetraitPrevue', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'Retrait prévu le',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('urgent', CheckboxType::class, [
                'required' => false,
                'label' => 'Service express (majoration)',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('modeLivraison', ChoiceType::class, [
                'label' => 'Retour du linge',
                'choices' => ['Retrait au pressing' => Commande::MODE_RETRAIT, 'Livraison à domicile' => Commande::MODE_DOMICILE],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('adresseLivraison', TextType::class, [
                'required' => false,
                'label' => 'Adresse de livraison',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('fraisLivraison', IntegerType::class, [
                'label' => 'Frais de livraison',
                'constraints' => [new PositiveOrZero()],
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('remise', IntegerType::class, [
                'label' => 'Remise',
                'constraints' => [new PositiveOrZero()],
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'label' => 'Notes internes',
                'attr' => ['class' => 'form-control', 'rows' => 2],
            ])
            ->add('lignes', CollectionType::class, [
                'entry_type' => LigneCommandeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'constraints' => [new Count(min: 1, minMessage: 'Ajoutez au moins un article.')],
            ]);

        if ($options['avec_acompte']) {
            $builder
                ->add('acompte', IntegerType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Acompte versé',
                    'constraints' => [new PositiveOrZero()],
                    'attr' => ['class' => 'form-control', 'min' => 0],
                ])
                ->add('modeAcompte', ChoiceType::class, [
                    'mapped' => false,
                    'label' => 'Mode de paiement',
                    'choices' => array_flip(Paiement::MODES),
                    'attr' => ['class' => 'form-select'],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Commande::class, 'avec_acompte' => true]);
    }
}
