<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 30.05.18
 * Time: 16:08
 */

namespace common\components\PerfectParser\DataSources\CCDA;


use common\components\PerfectParser\Common\Prototype\DataSource;
use common\components\PerfectParser\DataSources\CCDA\resources\labs\RLab;
use common\components\PerfectParser\DataSources\CCDA\resources\RAllergy;
use common\components\PerfectParser\DataSources\CCDA\resources\RProblem;
use common\components\PerfectParser\DataSources\CCDA\resources\RResource;
use common\components\PerfectParser\DataSources\CCDA\resources\ResourceSet;
use common\components\PerfectParser\DataSources\CCDA\resources\RMedication;
use common\components\PerfectParser\DataSources\CCDA\resources\type\TElement;
use common\components\PerfectParser\Resources\RAllergies;
use common\components\PerfectParser\Resources\RLabs;
use common\components\PerfectParser\Resources\RMedications;
use common\components\PerfectParser\Resources\RProblems;
use common\models\Partners;
use common\models\Practices;
use common\helpers\SimpleXML;
use yii\helpers\StringHelper;

/**
 * Class source
 * @package common\components\PerfectParser
 */
class CCDA extends DataSource
{
    /**
     * @inheritdoc
     */
    const ID = 'Ccda';

    /** @const integer */
    const PARTNER_ID = Partners::PARTNER_CCDA;

    /** @const array List of attributes on correct CCDA file */
    const STRUCTURE_VERIFICATION = [
        'code'       => '34133-9',
        'codeSystem' => '2.16.840.1.113883.6.1',
    ];

    /** @const string */
    const ERR_PRACTICE_SECTIONS = 'Practice sections not found';

    /** @const string */
    const ERR_CHECK_CCDA_CODES = 'Document is not a CCDA Continuation of Care Document';

    /** @var string[] List of rest api methods available for this data source */
    public $restMethodsAvailable = [];

    /** @var string[] List of rest api methods available for this data source in test mode */
    public $restTestMethodsAvailable = [
        self::METHOD_MED_INFO
    ];

    /** @var array $internalResourceList List of internal resources (class names as array keys),
     *      related to external resources for current data source (string or array of class names as array values)
     */
    public $internalResourcesList = [
        RMedications::class => RMedication::class,
        RProblems::class => RProblem::class,
        RAllergies::class => RAllergy::class,
        RLabs::class => RLab::class
        //...
    ];

    /** @var bool $getPracticeFromLogin Get practice data from current logined practice enroller */
    public $getPracticeFromLogin = false;

    /** @var ResourceSet[] dirty data from CCDA */
    private $externalData;

    /** @var RResource[] */
    private $externalResources;

    /** @var array List of assigned authors (as array keys) for current resourceSet, that were found with known represented organization (as array values) */
    public $assignedAuthors;

    /** @var TElement */
    public $portalID;


    /**
     * @param string|false $debugXML
     * @param bool $onlyPatientData
     *
     * @return bool|\SimpleXMLElement
     */
    public function retriveExternalData($debugXML = false, $onlyPatientData = false)
    {
        $this->externalData = [];
        $errors = [];
        try {
            if ($debugXML) {
                $data = SimpleXML::loadString(str_replace("\n", "", $debugXML));
                $errors = libxml_get_errors();
            } else {
                $data = false;
                // should be implemented, like as
                //$data = $this->api->retrieveCcdaResources
            }

            if ($data) {
                if (\Yii::$app->perfectParser->strictImport && !$this->verifyCcda($data)) {
                    \Yii::$app->perfectParser->error(self::ERR_CHECK_CCDA_CODES);
                    return false;
                }

                if ($onlyPatientData) {
                    if (isset($data->recordTarget->patientRole)) {
                        return $data->recordTarget->patientRole;
                    }
                    \Yii::$app->perfectParser->error('Incorrect structure of CCDA file. Block "recordTarget->patientRole" not found');
                    return false;
                }

                if ($this->extractPortalData($data)) {
                    if (isset($data->component->structuredBody->component)) {
                        foreach ($data->component->structuredBody->component as $resourceSet) {
                            $section = (isset($resourceSet->section) ? $resourceSet->section : $resourceSet);
                            if (isset($section->text) && isset($section->entry)) {
                                $sectionName = RResource::getResourceSetId($section);
                                $this->externalData[] = new ResourceSet([
                                    'name'  =>  $sectionName,
                                    'references' => $section->text,
                                    'entry' => $section->entry,
                                    'parentSection' => $section
                                ]);
                                \Yii::$app->perfectParser->log('Section '.$sectionName.' retrieved successfully');
                            } else {
                                \Yii::$app->perfectParser->log('Skip empty entry. No data or unknown structure');
                            }
                        }
                        return true;
                    }
                    \Yii::$app->perfectParser->error('Incorrect structure of CCDA file. Block "component->structuredBody->component" not found');
                }
            } elseif (count($errors)) {
                $msg = 'Error parsing XML:<ul>';
                foreach ($errors as $one) {
                    $msg .= '<li>Code '.$one->code.': '.$one->message.' (row/col: '.$one->line.'/'.$one->column.')</li>';
                }
                $msg .= '</ul>';
                \Yii::$app->perfectParser->error($msg);
            }
            \Yii::$app->perfectParser->error('No data has been retrieved');
        } catch(\Exception $e) {
            \Yii::$app->perfectParser->error('Retrive External Data Exception: '.$e->getMessage());
        }
        return false;
    }

    /**
     * @return bool
     */
    public function loadExternalData()
    {
        $this->externalResources = [];
        try {
            foreach ($this->externalData as $resourceSet) {
                $this->assignedAuthors = [];
                $this->externalResources = array_merge($this->externalResources, $resourceSet->createResources($this->portalID));
            }
        } catch (\Throwable $e) {
            \Yii::$app->perfectParser->error('Load External Resources Exception: '.$e->getMessage());
        }

        return true;
    }

    /**
     * @return bool
     */
    public function loadInternalData()
    {
        $this->internalResources = [];
        try {
            foreach (array_keys($this->internalResourcesList) as $className){
                if (class_exists($className)){
                    \Yii::$app->perfectParser->log('%yProcessing resource: '.StringHelper::basename($className).'%n');
                    \Yii::$app->perfectParser->incPrefix();
                    $resourceModel = new $className();
                    /** @var $resourceModel \common\components\PerfectParser\Resources\RResource */
                    $resourceModel->load($this->externalResources);
                    $this->internalResources[] = $resourceModel;
                    \Yii::$app->perfectParser->decPrefix();
                } else {
                    \Yii::$app->perfectParser->error("Class Not Found: ".$className);
                }
            }
        } catch (\Exception $e) {
            \Yii::$app->perfectParser->error('Load Internal Resources Exception: '.$e->getMessage());
        }

        return true;
    }

    private function verifyCcda($data)
    {
        if (isset($data->code)) {
            foreach (self::STRUCTURE_VERIFICATION as $attr => $val) {
                if (!isset($data->code[$attr]) || (string) $data->code[$attr] != $val) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private function extractPortalData($data)
    {
        if (\Yii::$app->perfectParser->dataSource->getPracticeFromLogin) {
            $practice = \Yii::$app->enroller->model->practice;
        } else {
            /*
            $tmpTestUmrId = '1.2.840.114350.1.13.232.2.7.2.688879-20100';
            $tmpTestName = 'NYU Langone Medical Center';
            */

            $practiceSection = null;
            if (isset($data->recordTarget->patientRole->providerOrganization)) {
                $practiceSection = $data->recordTarget->patientRole->providerOrganization;
            } else if (isset($data->custodian->assignedCustodian->representedCustodianOrganization)) {
                $practiceSection = $data->custodian->assignedCustodian->representedCustodianOrganization;
            }

            if (!$practiceSection) {
                throw new \Exception(self::ERR_PRACTICE_SECTIONS);
            }

            $umrParts = [];
            $umrParts[] = (!empty($practiceSection->name) ? (string) $practiceSection->name : 'NA');
            for ($i = 0; $i <= 1; $i++) {
                $umrParts[] = (!empty($practiceSection->addr->streetAddressLine[$i]) ? (string) $practiceSection->addr->streetAddressLine[$i] : 'NA');
            }
            foreach (['city', 'state', 'postalCode', 'country'] as $part) {
                $umrParts[] = (!empty($practiceSection->addr->$part) ? (string) $practiceSection->addr->$part : 'NA');
            }

            array_walk($umrParts, function (&$val) {
                $val = str_replace(' ', '_', trim($val));
            });
            $umrId = implode('-', $umrParts);

            $practice = Practices::findOne(['practice_umr_id' => $umrId]);
            if (!$practice) {
                if (!empty($data->recordTarget->patientRole->providerOrganization->name)) {
                    $practiceName = trim((string) $data->recordTarget->patientRole->providerOrganization->name);
                } else if (!empty($data->custodian->assignedCustodian->representedCustodianOrganization->name)) {
                    $practiceName = trim((string) $data->custodian->assignedCustodian->representedCustodianOrganization->name);
                } else {
                    throw new \Exception('Not found practice name in CCDA file');
                }

                $practice = \Yii::$app->perfectParser->createPractice($umrId, $practiceName, '', 0, true);
                \Yii::$app->perfectParser->log("%GCreate new practice with practice_umr_id = ".$umrId."%n");
            }
        }

        $portalId = new TElement();
        $portalId->setValue($practice->practice_umr_id);
        $this->portalID = $portalId;
        $this->sourcePractice = $practice;
        \Yii::$app->perfectParser->log("%GSet Resource portalID. practice_umr_id = ".$practice->practice_umr_id."%n");

        return true;
    }

}
