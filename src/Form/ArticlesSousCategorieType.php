<?php

namespace App\Form;

use App\Entity\ArticlesSousCategorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticlesSousCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('noms',TextType::class,['attr'=>['class' => 'form-control']])
            ->add('codeArticles',TextType::class,['attr'=>['class' => 'form-control']])
            ->add('prix',IntegerType::class,['attr'=>['class' => 'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticlesSousCategorie::class,
        ]);
    }
}
