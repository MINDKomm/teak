<?php

namespace Teak\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReferenceGenerator
 */
abstract class ReferenceGenerator extends Command
{
    // Arguments
    const ARG_FILES = 'files';

    // Options
    const OPT_OUTPUT = 'output';
    const OPT_FILENAME = 'filename';
    const OPT_FILE_PREFIX = 'file_prefix';
    const OPT_IGNORE = 'ignore';
    const OPT_HOOK_TYPE = 'hook_type';
    const OPT_PARENT = 'parent';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $classCollection
     */
    abstract protected function handleClassCollection($input, $output, $classCollection);

    protected function configure()
    {
        $this
            ->setDescription('Generate a reference')
            ->addArgument(
                self::ARG_FILES,
                InputArgument::REQUIRED,
                'Class files or source directory'
            )
            ->addOption(
                self::OPT_OUTPUT,
                'o',
                InputOption::VALUE_REQUIRED,
                'Output destination.'
            )
            ->addOption(
                self::OPT_IGNORE,
                'i',
                InputOption::VALUE_OPTIONAL,
                'Directories to ignore',
                ''
            )
            ->addOption(
                self::OPT_FILENAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'File name',
                ''
            )
            ->addOption(
                self::OPT_FILE_PREFIX,
                'p',
                InputOption::VALUE_OPTIONAL,
                'File prefix',
                ''
            )
            ->addOption(
                self::OPT_HOOK_TYPE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Hook type ("filter" or "action")',
                null
            )
            ->addOption(
                self::OPT_PARENT,
                null,
                InputOption::VALUE_OPTIONAL,
                'The parent string to use for the Front Matter block',
                'reference'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files  = $input->getArgument(self::ARG_FILES);
        $ignore = explode(',', $input->getOption(self::OPT_IGNORE));

        if (is_dir($files)) {
            $files = $this->getFilesInFolder($files, $ignore);
        } else {
            // Convert to array
            $files = [ $files ];
        }

        sort($files);

        $body = $this->handleClassCollection($input, $output, $files);

        if (is_array($body)) {
            $body = implode(PHP_EOL, $body);
        }

        $output->writeln(PHP_EOL . $body);
    }

    public function getFilesInFolder($dir, $ignore, $files = [])
    {
        /**
         * @var \SplFileInfo $f
         */
        foreach (new \FilesystemIterator($dir) as $f) {
            if ($f->isFile() && ! $f->isLink() && '.' !== substr($f->getFilename(), 0, 1)) {
                $files[] = $f->getRealPath();
            } elseif ($f->isDir()) {
                $files = $this->getFilesInFolder($f->getRealPath(), $ignore, $files);
            }
        }

        return $files;
    }
}
