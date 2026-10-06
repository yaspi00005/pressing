<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Crée un compte administrateur (ou réinitialise son mot de passe).')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $users,
        private UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::OPTIONAL, "Nom d'utilisateur")
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe (sinon demandé de façon masquée)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username') ?: $io->ask("Nom d'utilisateur", 'admin');
        $password = $input->getArgument('password');
        if (!$password) {
            $question = (new Question('Mot de passe (8 caractères minimum)'))->setHidden(true)->setHiddenFallback(false);
            $password = $io->askQuestion($question);
        }
        if (null === $password || \strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');

            return Command::FAILURE;
        }

        $user = $this->users->findOneBy(['username' => $username]);
        $nouveau = null === $user;
        if ($nouveau) {
            $user = (new User())->setUsername($username)->setPrenom('Admin')->setNom('Pressing')->setTelephone(0);
            $this->em->persist($user);
        }
        $user->setRoles(['ROLE_ADMIN'])->setPassword($this->hasher->hashPassword($user, $password));
        $this->em->flush();

        $io->success(sprintf('Administrateur « %s » %s.', $username, $nouveau ? 'créé' : 'mis à jour (mot de passe réinitialisé)'));

        return Command::SUCCESS;
    }
}
