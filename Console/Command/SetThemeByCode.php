<?php

namespace RedChamps\ThemeSetCommand\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Theme\Model\ResourceModel\Theme;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetThemeByCode extends Command
{
    const INPUT_KEY_THEME_CODE = 'theme-code';
    const INPUT_KEY_SCOPE = 'scope';
    const INPUT_KEY_SCOPE_ID = 'scope-id';

    /**
     * @var Theme\Collection
     */
    private $themeCollection;

    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(
        Theme\Collection $themeCollection,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        string $name = null
    ) {
        parent::__construct($name);
        $this->themeCollection = $themeCollection;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
    }

    protected function configure()
    {
        $this->setName('redChamps:set:theme');
        $this->setDescription('Set theme by code.');
        $this->addArgument(
            self::INPUT_KEY_THEME_CODE,
            InputArgument::REQUIRED,
            'Theme code, for example Magento/luma'
        );
        $this->addOption(
            self::INPUT_KEY_SCOPE,
            's',
            InputOption::VALUE_OPTIONAL,
            'Scope.'
        );
        $this->addOption(
            self::INPUT_KEY_SCOPE_ID,
            'sid',
            InputOption::VALUE_OPTIONAL,
            'Scope ID.'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $theme = $this->themeCollection->addFieldToFilter(
            'code',
            $input->getArgument(self::INPUT_KEY_THEME_CODE)
        )->getFirstItem();
        if ($theme && $theme->getId()) {
            $this->configWriter->save(
                'design/theme/theme_id',
                $theme->getId(),
                $input->getOption(self::INPUT_KEY_SCOPE)?:ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $input->getOption(self::INPUT_KEY_SCOPE_ID)?:0
            );
            $this->cacheTypeList->cleanType('config');
            $this->cacheTypeList->cleanType('full_page');
            $output->writeln('<info>Successfully set.</info>');
        } else {
            $output->writeln("<error>No theme found with code {$input->getArgument(self::INPUT_KEY_THEME_CODE)}.</error>");
        }
    }
}
