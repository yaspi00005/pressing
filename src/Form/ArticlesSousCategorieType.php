<?php

namespace App\Form;

use App\Entity\ArticlesSousCategorie;
use App\Entity\ArticlesCategories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('noms',TextType::class,['label' => 'Nom','attr'=>['class' => 'form-control']])
            ->add('codeArticles',TextType::class,['label' => 'Code','required' => false,'attr'=>['class' => 'form-control']])
            ->add('prix',IntegerType::class,['label' => 'Prix de base','attr'=>['class' => 'form-control','min' => 0]])
        ;
        if ($options['avec_categorie']) {
            $builder->add('categorie', EntityType::class, [
                'class' => ArticlesCategories::class,
                'choice_label' => 'noms',
                'placeholder' => 'Choisir une catégorie…',
                'label' => 'Catégorie',
                'attr' => ['class' => 'form-select'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticlesSousCategorie::class,
            'avec_categorie' => false,
        ]);
    }
}
