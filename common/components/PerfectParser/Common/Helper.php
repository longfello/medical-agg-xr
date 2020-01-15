<?php

namespace common\components\PerfectParser\Common;

use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\NewRxnormCode;
use common\models\Patient;
use common\models\Allergies;
use common\models\Conditions;
use common\models\Problems;
use common\models\EmergencyContacts;
use common\models\Hospitals;
use common\models\Insurance;
use common\models\MedicalHistory;
use common\models\Medications;
use common\models\OtherPhysicians;

use common\models\PatientInfo;

use common\models\RxnormLookup;
use common\models\RxnormRestCache;
use common\models\RxnormUnknown;
use common\models\SurgicalHistory;
use common\models\Vaccinations;
use common\models\LabsReports;
use common\models\LabsComplex;

use Exception;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use yii\db\Expression;

/**
 * Util Functions used to support Medfusion integration
 */
class Helper
{
    /** @const Default practice name */
    const STILL_CONFIGURING = 'Still configuring';
    /**
     *
     */
    const RESOURCE_PATIENT = 'Patient';
    /**
     *
     */
    const RESOURCE_PROCEDURE = 'Procedure';
    /**
     *
     */
    const RESOURCE_ALLERGY_INTOLERANCE = 'AllergyIntolerance';
    /**
     *
     */
    const RESOURCE_ALLERGY_REACTION = 'AllergyReaction';
    /**
     *
     */
    const RESOURCE_MEDICATION = 'Medication';
    /**
     *
     */
    const RESOURCE_BUNDLE = 'Bundle';
    /**
     *
     */
    const RESOURCE_MEDICATION_STATEMENT = 'MedicationStatement';
    /**
     *
     */
    const RESOURCE_MEDICATION_ORDER = 'MedicationOrder';
    /**
     *
     */
    const RESOURCE_MEDICATION_DISPENSE = 'MedicationDispense';
    /**
     *
     */
    const RESOURCE_MEDICATION_ADMINISTRATION = 'MedicationAdministration';
    /**
     *
     */
    const RESOURCE_DEVICE = 'Device';
    /**
     *
     */
    const RESOURCE_OBSERVATION = 'Observation';
    /**
     *
     */
    const RESOURCE_IMMUNIZATION = 'Immunization';
    /**
     *
     */
    const RESOURCE_CONDITION = 'Condition';
    /**
     *
     */
    const RESOURCE_ORGANIZATION = 'Organization';
    /**
     *
     */
    const RESOURCE_PRACTITIONER = 'Practitioner';
    /**
     *
     */
    const RESOURCE_EMERGENCY_CONTACT = 'EmergencyContact';

    /**
     * Return MedInfo section by model class
     *
     * @param $modelClassName
     *
     * @return bool|mixed
     */
    public static function get_medinfo_name_from_model($modelClassName)
    {
        $mapping = [
            PatientInfo::class => 'Demographics/Description, Device Dependencies',
            Allergies::class => 'Allergies',
            Medications::class => 'Medications',
            Conditions::class => 'Current Conditions',
            Problems::class => 'Current Conditions',
            SurgicalHistory::class => 'Surgical History',
            MedicalHistory::class => 'Medical History',
            //'Images and Uploads',
            Vaccinations::class => 'Vaccinations',
            Hospitals::class => 'Preferred Hospitals',
            Insurance::class => 'Insurance',
            EmergencyContacts::class => 'Emergency Contacts',
            OtherPhysicians::class => 'Physicians',
            LabsReports::class => 'Lab Data',
            LabsComplex::class => 'Lab Data',
            //'Comments'
        ];

        return (isset($mapping[$modelClassName]) ? $mapping[$modelClassName] : false);
    }

    /**
     *
     * @param string $resource MF resource name
     * @return string MedInfo block name related to MF resource name, or MF resource name (if relation not exists)
     */
    public static function get_medinfo_name_from_resource($resource)
    {
        $mapping = [
            self::RESOURCE_PATIENT => 'Demographics/Description',
            self::RESOURCE_PROCEDURE => 'Surgical History',
            self::RESOURCE_ALLERGY_INTOLERANCE => 'Allergies',
            self::RESOURCE_MEDICATION => 'Medication',
            self::RESOURCE_BUNDLE => 'Medications',
            self::RESOURCE_MEDICATION_STATEMENT => 'Medications',
            self::RESOURCE_MEDICATION_ORDER => 'Medications',
            self::RESOURCE_MEDICATION_DISPENSE => 'Medications',
            self::RESOURCE_MEDICATION_ADMINISTRATION => 'Medications',
            self::RESOURCE_DEVICE => 'Device Dependencies',
            self::RESOURCE_OBSERVATION => 'Device Dependencies',
            self::RESOURCE_IMMUNIZATION => 'Vaccinations',
            self::RESOURCE_CONDITION => 'Current Conditions',
            self::RESOURCE_EMERGENCY_CONTACT => 'EmergencyContact',
            self::RESOURCE_OBSERVATION => 'Lab Data',
        ];

        return (isset($mapping[$resource]) ? $mapping[$resource] : $resource);
    }

    /**
     * getNameByRxCode($code)
     * Return ingredient name by rxcode from rxnav.nlm.nih.gov,
     * if can't find return name from life_rxnorm_lookup table,
     * if can't find save rxcode to life_rxnorm_unknown table.
     *
     * @param integer $code
     *
     * @return string|false
     * @throws GuzzleException
     * @throws \Throwable
     */
    public static function getNameByRxCode($code)
    {
        $res = false;

        //Get result from cache
        $res_in_cache = RxnormRestCache::find()->where(['rxnorm_id' => $code])->one();
        /** @var $res_in_cache RxnormRestCache */
        if ($res_in_cache) {
            //Result found in cache
            $res = $res_in_cache->ingredient;
        }

        if (!$res) {
            //Get result from rxnav.nlm.nih.gov
            try {
                $client = new Client();
                $request = $client->request('GET', 'https://rxnav.nlm.nih.gov/REST/rxcui/' . $code . '/');

                if ($request->getStatusCode() == 200) {
                    $xml = simplexml_load_string($request->getBody());
                    if (isset($xml->idGroup->name)) {
                        //Result found in rxnav.nlm.nih.gov
                        $res = (string)$xml->idGroup->name;

                        //Save to cache
                        $rxnorm_rest_cache_model = new RxnormRestCache();
                        $rxnorm_rest_cache_model->rxnorm_id = $code;
                        $rxnorm_rest_cache_model->ingredient = $res;
                        if (!$rxnorm_rest_cache_model->save()) {
                            \Yii::error("Error on inserting in life_rxnorm_rest_cache"
                                . print_r($rxnorm_rest_cache_model->getErrors(), true));
                        }
                    }
                } else {
                    \Yii::error("Error in request to rxnav.nlm.nih.gov"
                        . print_r($request->getStatusCode() . " " . $request->getReasonPhrase(), true));
                }
            } catch (Exception $e) {
                \Yii::error("Error in request to rxnav.nlm.nih.gov"
                    . print_r($e->getMessage(), true));
            }
        }

        //Get result from life_rxnorm_lookup
        if (!$res) {
            $res_in_lookup = RxnormLookup::find()->where(['rxnorm_id' => $code])->one();
            /** @var $res_in_lookup RxnormLookup */
            if ($res_in_lookup) {
                //Result found in life_rxnorm_lookup
                $res = $res_in_lookup->ingredient;
            }
        }

        // Can't find result - save to life_rxnorm_unknown
        if (!$res) {
            $rxnorm_unknown_model = RxnormUnknown::find()->where(['rxnorm_id' => $code])->one();
            /** @var $rxnorm_unknown_model RxnormUnknown */
            if (!$rxnorm_unknown_model) {
                $rxnorm_unknown_model = new RxnormUnknown();
                $rxnorm_unknown_model->rxnorm_id = $code;

                foreach (explode(',', env('MEDFUSION_EMAIL', null, true)) as $email) {
                    $mail = new NewRxnormCode(['code' => $code]);
                    $mail->send(trim($email), true, Email::getID());
                }
            }
            $rxnorm_unknown_model->updated = new Expression('NOW()');
            if (!$rxnorm_unknown_model->save()) {
                \Yii::error("Error on updating life_rxnorm_unknown table"
                    . print_r($rxnorm_unknown_model->getErrors(), true));
            }
        }

        return $res;
    }

    /**
     * check_for_mf_users_to_poll()
     * checks if any users require a medfusion update based on mf_next_check field
     * if update is reqire
     *
     * @return Patient[] of life_patient user objects that require updates
     *
     * @throws Exception
     */
    public static function check_for_mf_users_to_poll()
    {
        $mf_users_to_update = Patient::findBySql('
SELECT * FROM life_patients WHERE mf_uuid IS NOT NULL AND (mf_next_check IS NULL OR mf_next_check < NOW()) 
ORDER BY mf_next_check ASC')->all();
        $update_in = getenv("MEDFUSION_POLL_INTERVAL");
        foreach ($mf_users_to_update as $index => $user) {
            /** @var Patient $user */
            $user->mf_next_check = new Expression('NOW() + Interval ' . $update_in);
            if (!$user->save()) {
                throw new Exception("Error on updating mf_next_check =>".print_r($user->getErrors(), true));
            }
        }

        return $mf_users_to_update;
    }

}
