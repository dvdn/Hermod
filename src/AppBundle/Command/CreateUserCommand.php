<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hermod:user:create')
            ->setDescription('Creates a new user and generates its token.')
            ->setHelp(
                'This command helps you to create a new user by providing its username. '.
                'The token is automatically generated.'
            )
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user');
    }

    private function isUsernameAlreadyUsed($username)
    {
        return (bool) $this->getContainer()
            ->get('doctrine')
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $username]);
    }

    private function addRoleForUser(OutputInterface $output, string $username)
    {
        $commandName = 'hermod:user:role';
        $command = $this->getApplication()->find($commandName);
        $arguments = [
            'command' => $commandName,
            'username' => $username
        ];

        $command->run(new ArrayInput($arguments), $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('User Creation');

        $username = $input->getArgument('username');

        if (!$username) {
            $username = $io->ask('Please choose a username for the new user');
        }

        while ($this->isUsernameAlreadyUsed($username)) {
            $username = $io->ask(sprintf('Username %s is already used, please choose another one', $username));
        }

        $token = Uuid::uuid4();

        $user = new User($username, $token);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $io->success(sprintf('User "%s" created successfully! Here is the token: "%s"', $username, $token));
        $this->addRoleForUser($output, $username);
    }
}
