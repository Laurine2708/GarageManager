<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Chisel\Chisel;
use Laravel\Chisel\Question;
use Laravel\Chisel\Script;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;

/**
 * Commande d'installation des fonctionnalités du kit de démarrage Laravel.
 */
class InstallFeaturesCommand extends Command
{
    /**
     * Nom de la commande et option permettant de fournir les réponses en JSON.
     *
     * @var string
     */
    protected $signature = 'install:features
        {--answers= : JSON string of answers to skip interactive prompts}';

    /**
     * Description affichée dans l'aide des commandes Artisan.
     *
     * @var string
     */
    protected $description = 'Choose which starter kit features to keep';

    /**
     * Collecte les choix du starter kit, applique le script Chisel et construit les ressources.
     *
     * Les options d'installation peuvent différer les hooks ou éviter les étapes Node.
     */
    public function handle(): int
    {
        if ($this->shouldDeferInstallerHooks()) {
            return self::SUCCESS;
        }

        if (! file_exists(base_path('chisel.php'))) {
            return self::SUCCESS;
        }

        /** @var Script $script */
        $script = require base_path('chisel.php');

        $providedAnswers = $this->option('answers') === null
            ? []
            : json_decode((string) $this->option('answers'), true, 512, JSON_THROW_ON_ERROR);

        $answers = $script
            ->collectAnswers()
            ->onQuestion(fn (Question $question) => multiselect(
                label: $question->label,
                options: $question->options,
                default: $question->default ?? [],
                required: $question->required,
                hint: $question->hint,
            ))
            ->interactive($this->input->isInteractive())
            ->withAnswers($providedAnswers);

        $skipNode = $this->shouldSkipNode();

        if (! $skipNode) {
            $this->installNodeDependencies();
        }

        $script->chisel($answers);

        if (! $skipNode) {
            $this->buildAssets();
        }

        return self::SUCCESS;
    }

    /**
     * Indique si les hooks d'installation doivent être reportés.
     */
    protected function shouldDeferInstallerHooks(): bool
    {
        if ($this->option('answers') !== null) {
            return false;
        }

        return $this->installerFlag('LARAVEL_INSTALLER_DEFER_HOOKS');
    }

    /**
     * Indique si les étapes d'installation et de compilation Node doivent être omises.
     */
    protected function shouldSkipNode(): bool
    {
        return $this->installerFlag('LARAVEL_INSTALLER_NO_NODE');
    }

    /**
     * Lit un indicateur booléen depuis l'environnement du processus.
     */
    protected function installerFlag(string $name): bool
    {
        return filter_var(
            $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name),
            FILTER_VALIDATE_BOOL,
        );
    }

    /**
     * Installe les dépendances JavaScript avec le gestionnaire détecté par Chisel.
     */
    protected function installNodeDependencies(): void
    {
        $npm = Chisel::in(base_path())->npm();
        $packageManager = $npm->packageManager();

        spin(
            fn () => $npm->install(),
            "Installing dependencies with {$packageManager->value}...",
        );
    }

    /**
     * Compile les ressources front-end après l'application des choix Chisel.
     */
    protected function buildAssets(): void
    {
        $npm = Chisel::in(base_path())->npm();

        spin(
            fn () => $npm->run('build'),
            'Building assets...',
        );
    }
}
