<?php

namespace App\Form;

use App\Entity\Depense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, ['label' => 'Libellé', 'attr' => ['class' => 'form-control']])
            ->add('categorie', ChoiceType::class, ['label' => 'Catégorie', 'choices' => array_flip(Depense::CATEGORIES), 'attr' => ['class' => 'form-select']])
            ->add('montant', IntegerType::class, ['label' => 'Montant', 'attr' => ['class' => 'form-control', 'min' => 1]])
            ->add('dateDepense', DateType::class, ['label' => 'Date', 'widget' => 'single_text', 'input' => 'datetime_immutable', 'attr' => ['class' => 'form-control']])
            ->add('notes', TextType::class, ['required' => false, 'label' => 'Notes', 'attr' => ['class' => 'form-control']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Depense::class]);
    }
}
