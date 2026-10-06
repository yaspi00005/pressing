<?php

namespace App\Form;

use App\Entity\ProduitStock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Produit', 'attr' => ['class' => 'form-control']])
            ->add('unite', TextType::class, ['label' => 'Unité (L, kg, bidon…)', 'attr' => ['class' => 'form-control']])
            ->add('quantite', IntegerType::class, ['label' => 'Quantité en stock', 'attr' => ['class' => 'form-control', 'min' => 0]])
            ->add('seuilAlerte', IntegerType::class, ['label' => "Seuil d'alerte", 'attr' => ['class' => 'form-control', 'min' => 0]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ProduitStock::class]);
    }
}
