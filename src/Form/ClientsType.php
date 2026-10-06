<?php

namespace App\Form;

use App\Entity\Clients;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom',TextType::class,['attr'=>['class' => 'form-control']])
            ->add('nom',TextType::class,['attr'=>['class' => 'form-control']])
            ->add('telephones',TelType::class,['attr'=>['class' => 'form-control']])
            ->add('genres',ChoiceType::class,['attr'=>['class' => 'select',
    ], 'choices'=>['Homme' => 'Homme','Femme' =>'Femme']])
            ->add('adresses',TextType::class,['attr'=>['class' => 'form-control']])
            ->add('email',TextType::class,['attr'=>['class' => 'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clients::class,
        ]);
    }
}
