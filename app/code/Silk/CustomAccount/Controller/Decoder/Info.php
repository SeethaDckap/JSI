<?php

namespace Silk\CustomAccount\Controller\Decoder;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Silk\CustomAccount\Controller\Decoder\Search;

class Info extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        JsonFactory $resultJsonFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $baseSku = $this->getRequest()->getParam('base_sku');
        $preferSku = $this->getRequest()->getParam('prefer_sku');
        $selectedOptionString = substr($preferSku, strpos($preferSku, $baseSku) + strlen($baseSku) + 1);
        $selectedOptions = explode('-', $selectedOptionString);
        
        try {
            $possibleBaseSkus = $this->getAllPossibleBaseSkus($baseSku);
            $modificationList = $this->getFormattedModificationsDetails($possibleBaseSkus);
            $accessoriesList = $this->getFormattedAccessoriesDetails($possibleBaseSkus);
            $modificationsDependency = $this->getModificationDependency($possibleBaseSkus);
            $accessoriesDependency = $this->getAccessoriesDependency($possibleBaseSkus);
            $modificationsToAccessoriesDependency = $this->getModificationToAccessoriesDependency($possibleBaseSkus);
            $accessoriesToModificationsDependency = $this->getAccessoriesToModificationDependency($possibleBaseSkus);
            $templateInfo = $this->getTemplateInfo($possibleBaseSkus);
            $dimensionInfo = $this->getDimensionInfo($possibleBaseSkus);

            $modificationAccessoriesInfo = [
                'modifications' => $modificationList,
                'accessories' => $accessoriesList,
                'm2m_dependency' => $modificationsDependency,
                'm2a_dependency' => $modificationsToAccessoriesDependency,
                'a2m_dependency' => $accessoriesToModificationsDependency,
                'a2a_dependency' => $accessoriesDependency,
                'template_info' => $templateInfo,
                'dimension_info' => $dimensionInfo
            ];

            $hingeSideInfo = $this->getHingeSideInfo($baseSku, $possibleBaseSkus, $selectedOptions);
            
            $result = array_merge($modificationAccessoriesInfo, $hingeSideInfo);

            return $this->resultJsonFactory->create()->setData($result);

        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData([]);
        }
    }

    private function getTemplateInfo($possibleBaseSkus){
        $tableName = $this->resourceConnection->getTableName('decoder_mod_dependency');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, [])
            ->joinInner(
                [ 'template'
                  =>
                  $this->resourceConnection->getTableName('decoder_template_hdr')
                ],

                "template.template_cd = decoder_mod_dependency.template_cd",
                []
            )
            ->joinInner(
                [ 'template_detail'
                  =>
                  $this->resourceConnection->getTableName('decoder_template_dtl')
                ],

                "template_detail.decoder_template_hdr_uid = template.decoder_template_hdr_uid",
                []
            )
            ->joinInner(
                [ 'template_option'
                  =>
                  $this->resourceConnection->getTableName('decoder_segment_hdr')
                ],

                "template_option.decoder_segment_hdr_uid = template_detail.decoder_segment_hdr_uid",
                []
            )
            ->columns([
                'base_sku' => 'decoder_mod_dependency.base_sku',
                'template_cd' => 'decoder_mod_dependency.template_cd',
                'segments' => new \Zend_Db_Expr("GROUP_CONCAT(IF(template_detail.include_in_item_mask = 'Y' AND template_detail.required_flag = 'N', template_option.segment_cd, NULL) ORDER BY template_detail.sequence_no)"),
            ])
            ->where('decoder_mod_dependency.base_sku IN (?)', $possibleBaseSkus)
            ->group('decoder_mod_dependency.base_sku')
            ->group('decoder_mod_dependency.template_cd');

        $result = $connection->fetchAll($qry);

        if(!empty($result)){
            foreach ($result as &$value) {
                $value['segments'] = explode(',', $value['segments']);
            }
        }

        return $result;
    }

    private function getDimensionInfo($possibleBaseSkus){
        $tableName = $this->resourceConnection->getTableName('decoder_base_sku');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['base_sku', 'weight', 'unassembled_length', 'unassembled_width', 'unassembled_height', 'unassembled_cube', 'assembled_length', 'assembled_width', 'assembled_height', 'assembled_cube'])
            ->where('base_sku IN (?)', $possibleBaseSkus);

        return $connection->fetchAll($qry);
    }

    private function getHingeSideInfo($baseSku, $possibleBaseSkus, $selectedOptions){
        $info = [
            'hinge' => [
                'options' => [],
                'selected_option' => ''
            ],
            'finshedside' => [
                'options' => [],
                'selected_option' => ''
            ],
            'allow_assembly' => 0
        ];

        $tableName = $this->resourceConnection->getTableName('decoder_base_sku');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['hinge', 'sides', 'allow_assembly'])
            ->where('base_sku=?', $baseSku);
        $result = $connection->fetchAll($qry);

        $sideMap = [
            "FNS" => "N",
            "FRS" => "R",
            "FLS" => "L",
            "FBS" => "B"
        ];

        $hingeSideInfo = $result ? $result[0] : null;
        if($hingeSideInfo){
            $hingeData = (isset($hingeSideInfo['hinge']) && $hingeSideInfo['hinge']) ?  explode(',', $hingeSideInfo['hinge']) : [];
            $hingeSelectionOption = '';
            $hingeOptions = [];
            if(!empty($hingeData)){
                foreach ($hingeData as $option) {
                    $hingeOptions[] = [
                        "option_value" => $option,
                        "option_label" => $option
                    ];
                    if(in_array($option, $selectedOptions)){
                        $hingeSelectionOption = $option;
                    }
                }
            }
            $sidesData = (isset($hingeSideInfo['sides']) && $hingeSideInfo['sides']) ?  explode(',', $hingeSideInfo['sides']) : [];
            $sidesOptions = [];

            $sidesSelectionOption = '';
            if(!empty($sidesData)){
                foreach ($sidesData as $option) {
                     $sidesOptions[] = [
                        "option_value" => $option,
                        "option_label" => $sideMap[$option]
                    ];
                    if(in_array($option, $selectedOptions)){
                        $sidesSelectionOption = $option;
                    }
                }
            }
            
            $info = [
                'hinge' => [
                    'options' => $hingeOptions,
                    'selected_option' => $hingeSelectionOption
                ],
                'finshedside' => [
                    'options' => $sidesOptions,
                    'selected_option' => $sidesSelectionOption
                ],
                'allow_assembly' => (isset($hingeSideInfo['allow_assembly']) && $hingeSideInfo['allow_assembly'] == 'Y') ? 1 : 0
            ];
        }

        return $info;
    }

    private function getAllPossibleBaseSkus($baseSku){
        $possibleBaseSkus = [$baseSku];
        $currentBaseSet = [$baseSku];
        $tableName = $this->resourceConnection->getTableName('decoder_acc_dependency');
        $connection = $this->resourceConnection->getConnection();
        $limit = 10;
        while(!empty($currentBaseSet) && $limit > 0){
            $qry = $connection
                ->select()
                ->from($tableName, [])
                ->columns([new \Zend_Db_Expr("GROUP_CONCAT(DISTINCT(NULLIF(target_base_sku,'')))")])
                ->where('base_sku in (?)', $currentBaseSet);
            $result = $connection->fetchOne($qry);
            if(!empty($result)){
                $currentBaseSet = array_unique(explode(',',$result));
                $possibleBaseSkus = array_merge($possibleBaseSkus, $currentBaseSet);
            }
            else{
                $currentBaseSet = [];
            }
            $limit = $limit - 1;
        }

        $possibleBaseSkus = array_unique($possibleBaseSkus);

        return $possibleBaseSkus;
    }

    private function getFormattedModificationsDetails($possibleBaseSkus){
        $modificationList = $this->getModificationsList($possibleBaseSkus);
        $modifications = [];
        if(!empty($modificationList)){
            $modificationDetails = $this->getModificationDetails($modificationList);
            if(!empty($modificationDetails)){
                foreach ($modificationDetails as $segmentOptionsValue) {
                    $existKey = array_search($segmentOptionsValue['segment_code'], array_column($modifications, 'segment_code'));
                    if($existKey === false){
                        $modifications[] = [
                            'segment_code' => $segmentOptionsValue['segment_code'],
                            'segment_name' => $segmentOptionsValue['segment_name'],
                            'options' => [
                                [
                                    'option_value' => $segmentOptionsValue['option_value'],
                                    'option_label' => $segmentOptionsValue['option_label']
                                ]
                            ],
                            'selected_option' => ''
                        ];
                    }
                    else{
                        $modifications[$existKey]['options'][] = [
                            'option_value' => $segmentOptionsValue['option_value'],
                            'option_label' => $segmentOptionsValue['option_label']
                        ];
                    }
                }
            }
        }

        return $modifications;
    }

    private function getFormattedAccessoriesDetails($possibleBaseSkus){
        $accessories = [];
        $accessoriesList = $this->getAccessoriesList($possibleBaseSkus);
        if(!empty($accessoriesList)){
            $accessoryDetails = $this->getAccessoriesDetails($accessoriesList);
            foreach ($accessoryDetails as $accessory) {
                $accessories[] = [
                    'accessory_code' => $accessory['item_id'],
                    'accessory_name' => $accessory['description'],
                    'is_selected' => 0
                ];
            }
        }

        return $accessories;
    }


    private function getModificationDetails($modificationList){
        $tableName = $this->resourceConnection->getTableName('decoder_segment_dtl');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, [])
            ->joinInner(
                [ 'template_option'
                  =>
                  $this->resourceConnection->getTableName('decoder_segment_hdr')
                ],

                "template_option.decoder_segment_hdr_uid = decoder_segment_dtl.decoder_segment_hdr_uid",
                []
            )
            ->columns([
                'segment_code' => 'template_option.segment_cd',
                'segment_name' => 'template_option.segment_desc',
                'option_value' => 'decoder_segment_dtl.value_cd',
                'option_label' => 'decoder_segment_dtl.value_desc'
            ])
            ->where('template_option.segment_cd in (?)', $modificationList);

        return $connection->fetchAll($qry);
    }

    private function getAccessoriesDetails($accessoriesList){
        $tableName = $this->resourceConnection->getTableName('decoder_acc_details');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['item_id', 'description'])
            ->where('item_id in (?)', $accessoriesList);

        return $connection->fetchAll($qry);
    }

    private function getModificationsList($possibleBaseSkus){
        $tableName = $this->resourceConnection->getTableName('decoder_mod_dependency');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, [])
            ->columns([new \Zend_Db_Expr("CONCAT_WS(',', GROUP_CONCAT(DISTINCT(NULLIF(available_segement_cds,'')) SEPARATOR ','), GROUP_CONCAT(DISTINCT(NULLIF(segment_cd,'')) SEPARATOR ','))")])
            ->where('base_sku IN (?)', $possibleBaseSkus);
        $result = $connection->fetchOne($qry);
        if(!empty($result)){
            return array_unique(explode(',',$result));
        }

        return [];
    }

    private function getAccessoriesList($possibleBaseSkus){
        $tableName = $this->resourceConnection->getTableName('decoder_acc_dependency');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, [])
            ->columns([new \Zend_Db_Expr("GROUP_CONCAT(DISTINCT(NULLIF(item_id,'')) SEPARATOR ',')")])
            ->where('base_sku IN (?)', $possibleBaseSkus);
        $result = $connection->fetchOne($qry);
        if(!empty($result)){
            return array_unique(explode(',',$result));
        }

        return [];
    }

    private function getAccessoriesDependency($possibleBaseSkus){
        return $this->getFormattedDependecyData($possibleBaseSkus, '', 'decoder_acc_dependency');
    }


    private function getModificationDependency($possibleBaseSkus){
        return $this->getFormattedDependecyData($possibleBaseSkus, 'available_segement_cds', 'decoder_mod_dependency');
    }

    private function getModificationToAccessoriesDependency($possibleBaseSkus){
        return $this->getFormattedDependecyData($possibleBaseSkus, 'available_item_ids', 'decoder_mod_acc_dependency');
    }

    private function getAccessoriesToModificationDependency($possibleBaseSkus){
        return $this->getFormattedDependecyData($possibleBaseSkus, 'available_segement_cds', 'decoder_acc_mod_dependency');
    }

    private function getFormattedDependecyData($baseSkuSet, $multiLineDataField, $tableName){
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName)
            ->where('base_sku IN (?)', $baseSkuSet);
        $result = $connection->fetchAll($qry);

        if(!empty($result)){
            foreach ($result as &$dependency) {
                if($multiLineDataField && isset($dependency[$multiLineDataField]) && $dependency[$multiLineDataField]){
                    $dependency[$multiLineDataField] = explode(',', $dependency[$multiLineDataField]);
                }
                else{
                    $dependency[$multiLineDataField] = [];
                }
            }
        }

        return $result;
    }
}
