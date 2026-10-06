<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom du service', 'attr' => ['class' => 'form-control']])
            ->add('code', TextType::class, ['label' => 'Code', 'attr' => ['class' => 'form-control']])
            ->add('actif', CheckboxType::class, ['required' => false, 'label' => 'Actif', 'attr' => ['class' => 'form-check-input']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Service::class]);
    }
}
