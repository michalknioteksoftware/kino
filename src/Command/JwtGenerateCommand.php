<?php

declare(strict_types=1);

namespace App\Command;

use Firebase\JWT\JWT;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:jwt:generate',
    description: 'Generate a JWT token for API authentication or testing.',
)]
final class JwtGenerateCommand extends Command
{
    public function __construct(
        #[Autowire(param: 'kernel.secret')]
        private readonly string $appSecret,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Signing secret (default: JWT_SECRET or APP_SECRET from .env)')
            ->addOption('exp', null, InputOption::VALUE_REQUIRED, 'Expiration in minutes from now', '60')
            ->addOption('sub', null, InputOption::VALUE_REQUIRED, 'Subject claim (e.g. user id or username)')
            ->addOption('payload', 'p', InputOption::VALUE_REQUIRED, 'Extra claims as JSON object, e.g. \'{"role":"admin"}\'')
            ->addOption('alg', null, InputOption::VALUE_REQUIRED, 'Algorithm', 'HS256');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $secret = $input->getOption('secret')
            ?? ($_ENV['JWT_SECRET'] ?? null)
            ?? $this->appSecret;
        if ($secret === '' || $secret === 'change-me-in-production') {
            $io->warning('Using a default or placeholder secret. Set JWT_SECRET in .env for production.');
        }

        // HS256 requires key >= 256 bits (32 bytes); derive a key of correct length when too short
        if (strlen($secret) < 32) {
            $secret = hash('sha256', $secret, true);
        }

        $expMinutes = (int) $input->getOption('exp');
        $payload = [
            'iat' => time(),
            'exp' => time() + ($expMinutes * 60),
        ];

        if ($input->getOption('sub') !== null) {
            $payload['sub'] = $input->getOption('sub');
        }

        $extra = $input->getOption('payload');
        if ($extra !== null) {
            $decoded = json_decode($extra, true);
            if (!is_array($decoded)) {
                $io->error('Invalid --payload JSON.');
                return Command::FAILURE;
            }
            $payload = array_merge($payload, $decoded);
        }

        $alg = $input->getOption('alg');
        $token = JWT::encode($payload, $secret, $alg);

        $io->success('JWT token generated.');
        $output->writeln($token);

        return Command::SUCCESS;
    }
}
