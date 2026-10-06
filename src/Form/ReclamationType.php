<?php

namespace App\Form;

use App\Entity\Clients;
use App\Entity\Commande;
use App\Entity\Reclamation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Clients::class,
                'choice_label' => fn (Clients $c) => $c->getNomComplet(),
                'placeholder' => 'Choisir un client…',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('commande', EntityType::class, [
                'class' => Commande::class,
                'choice_label' => 'numero',
                'required' => false,
                'placeholder' => 'Commande concernée (facultatif)',
                'label' => 'Commande',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('objet', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'rows' => 4]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Reclamation::class]);
    }
}
