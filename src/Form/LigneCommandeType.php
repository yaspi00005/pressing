<?php

namespace App\Form;

use App\Entity\ArticlesSousCategorie;
use App\Entity\LigneCommande;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class LigneCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => ArticlesSousCategorie::class,
                'choice_label' => 'noms',
                'group_by' => fn (ArticlesSousCategorie $a) => $a->getCategorie()?->getNoms(),
                'placeholder' => 'Article…',
                'label' => 'Article',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-select ligne-article'],
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'query_builder' => fn ($r) => $r->createQueryBuilder('s')->andWhere('s.actif = true')->orderBy('s.nom'),
                'placeholder' => 'Service…',
                'label' => 'Service',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-select ligne-service'],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Qté',
                'constraints' => [new NotBlank(), new Positive()],
                'attr' => ['class' => 'form-control ligne-qte', 'min' => 1],
            ])
            ->add('prixUnitaire', IntegerType::class, [
                'label' => 'Prix unitaire',
                'constraints' => [new NotBlank(), new PositiveOrZero()],
                'attr' => ['class' => 'form-control ligne-prix', 'min' => 0],
            ])
            ->add('observation', TextType::class, [
                'required' => false,
                'label' => 'Observation (tache, couleur, défaut…)',
                'attr' => ['class' => 'form-control', 'maxlength' => 255],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => LigneCommande::class]);
    }
}
