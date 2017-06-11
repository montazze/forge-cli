<?php

namespace Sven\ForgeCLI\Commands;

use Sven\ForgeCLI\Config;
use Themsaid\Forge\Forge;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /**
     * @var bool
     */
    protected $needsForge = true;

    /**
     * @var \Themsaid\Forge\Forge
     */
    protected $forge;

    /**
     * @var array
     */
    protected $optionMap = [];

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    abstract public function perform(InputInterface $input, OutputInterface $output);

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->needsForge) {
            $this->perform($input, $output);

            return;
        }

        $key = (new Config)->get('key');

        $this->forge = new Forge($key);

        $this->perform($input, $output);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array                                             $header
     * @param array                                             $rows
     */
    protected function table(OutputInterface $output, array $header, array $rows)
    {
        $table = new Table($output);
        $table->setHeaders($header)
            ->setRows($rows);

        $table->render();
    }

    /**
     * @param array      $options
     * @param array|null $optionMap
     *
     * @return array
     */
    protected function fillData(array $options, array $optionMap = null)
    {
        $data = [];

        foreach ($optionMap ?: $this->optionMap as $option => $requestKey) {
            if (! isset($options[$option])) {
                continue;
            }

            $data[$requestKey] = $options[$option];
        }

        return $data;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param string                                          $option
     *
     * @return bool|string
     */
    protected function getFileContent(InputInterface $input, $option)
    {
        $filename = $input->hasOption($option) ? $input->getOption($option) : 'php://stdin';

        if ($filename && ftell(STDIN) === 0) {
            return file_get_contents($filename);
        }

        throw new \InvalidArgumentException('This command requires either the "--'.$option.'" option to be set or an input from STDIN.');
    }
}
