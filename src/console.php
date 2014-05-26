<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\ProcessBuilder;

$console = new Application('CQRS Blog Engine', '1.0');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);

$console
    ->register('server:run')
    ->setDefinition([
        new InputArgument('address', InputArgument::OPTIONAL, 'Address:port', 'localhost:9000')
    ])
    ->setDescription('Runs with PHP built-in web server')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command runs the embedded web server:

    <info>%command.full_name%</info>

You can also customize the default address and port the web server listens to:

    <info>%command.full_name% 127.0.0.1:8080</info>
EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($console) {

        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            throw new Exception('This feature only runs with PHP 5.4.0 or higher.');
        }

        $app = __DIR__ . '/../web/index_dev.php';
        while (!file_exists($app)) {
            $dialog = $console->getHelperSet()->get('dialog');
            $app = $dialog->ask($output, sprintf('<comment>I cannot find "%s". What\'s the absoulte path of "console.php"?</comment> ', $app), __DIR__ . '/../web/index_dev.php');
        }

        $output->writeln(sprintf('Application running on <info>%s</info>', $input->getArgument('address')));

        $builder = new ProcessBuilder(array(PHP_BINARY, '-S', $input->getArgument('address'), $app));

        $builder->setWorkingDirectory(getcwd());
        $builder->setTimeout(null);
        $builder->getProcess()->run(function ($type, $buffer) use ($output) {
            if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()) {
                $output->write($buffer);
            }
        });
    })
;

return $console;
