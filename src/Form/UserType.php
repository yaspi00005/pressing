<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('telephone', IntegerType::class, ['label' => 'Téléphone'])
            ->add('role', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Rôle',
                'choices' => array_flip(User::ROLES),
                'data' => $options['role_actuel'],
                'help' => 'Administrateur : tout. Réception / caisse : commandes, clients, encaissements. Atelier : suivi des commandes et stock.',
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => $options['mot_de_passe_obligatoire'],
                'label' => $options['mot_de_passe_obligatoire'] ? 'Mot de passe' : 'Nouveau mot de passe',
                'help' => $options['mot_de_passe_obligatoire'] ? '8 caractères minimum.' : 'Laisser vide pour conserver le mot de passe actuel.',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => $options['mot_de_passe_obligatoire']
                    ? [new NotBlank(message: 'Saisissez un mot de passe.'), new Length(min: 8, minMessage: '8 caractères minimum.')]
                    : [new Length(min: 8, minMessage: '8 caractères minimum.')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class, 'mot_de_passe_obligatoire' => true, 'role_actuel' => 'ROLE_RECEPTION']);
    }
}
