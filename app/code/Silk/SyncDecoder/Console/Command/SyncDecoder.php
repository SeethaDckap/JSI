<?php
namespace Silk\SyncDecoder\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncDecoder extends Command
{
    private $appState;

    protected $scopeConfig;

    protected $resourceConnection;

    protected $ctmtbFactory;

    const NAME = 'syncdecoder';

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Silk\SyncDecoder\Model\Message\Request\CTMTBFactory $ctmtbFactory
    ) {
        parent::__construct(self::NAME);
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->ctmtbFactory = $ctmtbFactory;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/syncdecoder.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $output->writeln('<info>' . "Start Sync" . '</info>');
        try {
            $ctmtb = $this->ctmtbFactory->create();
            $tables = $ctmtb->sendMessage();
            if(!empty($tables)){
                foreach ($tables as $table){
                    try {
                        $this->importDecoderData($table->getData('name'), $table->getData('data'), $table->getData('schema'));
                        $output->writeln('<info>' . "Table " . $table->getData('name') . ' Updated</info>');
                    } catch (\Exception $e) {
                        $output->writeln('<error>' . $e->getMessage() . '</error>');
                    }
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
        $output->writeln('<info>' . "End Sync" . '</info>');
    }

    private function importDecoderData($tableName, $decoderData, $schema){
        $connection = $this->resourceConnection->getConnection();
        $query = $this->formatJsonDataToQuery($tableName, $schema, $decoderData);
        if($query){
            $connection->truncateTable($tableName);
            $connection->query($query);
        }
    }

    private function formatJsonDataToQuery($tableName, $schema, $decoderData){
        $columnNames = [];
        $columnValues = [];

        if(!empty($decoderData)){
            $tableData = json_decode($decoderData, true);
            $schemaData = json_decode($schema, true);
            foreach ($schemaData as $column) {
                $columnNames[] = $column['COLUMN_NAME'];
            }

            foreach ($tableData as $data) {
                $row = [];
                foreach ($columnNames as $column) {
                    $row[$column] = isset($data[$column]) ? addslashes($data[$column]) : NULL;
                }
                $columnValues[] = '("' . implode('","', array_values($row)) . '")';
            }

            $columnNameQuery = '`' . implode('`,`', $columnNames) . '`';
            $columnValueQuery = implode(',', array_values($columnValues));

            return "INSERT INTO `{$tableName}` ({$columnNameQuery}) VALUES {$columnValueQuery}";
        }

        return '';
    }


}