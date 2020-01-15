<?php
namespace common\components\widgets\controls;

/**
 * Class PhoneStatusInfo
 * @package common\components\widgets\controls
 * @property View $view
 */
class PhoneStatusInfo extends \yii\base\Widget
{
    /** @var \common\models\Patient $patient */
    public $patient;

    /** @var boolean */
    public $withInput = true;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->view->registerJs('
            $(document).on("click", ".verify-link", function (e) {
                e.preventDefault();

                $.ajax({
                    url: "/subscriber-home/check-readiness-sms-resend",
                    type: "get",
                    dataType: "json",
                    success: function (resp) {
                        if (resp.available){
                            $("#phoneForRSModal").text(resp.cellPhone);
                            $("#resendSmsModal").modal("show");
                        } else {
                            $("#phoneForNAModal").text(resp.cellPhone);
                            $("#dayForReset").text(resp.daysForReset);
                            $("#resendSmsModalNotAvailable").modal("show");
                        }
                    }
                });
            });

            $(document).on("click", ".resendSmsBtnConfirm", function (e) {
                e.preventDefault();
                $("#resendSmsModal").modal("hide");

                $.ajax({
                    url: "/subscriber-home/resend-confirm-sms",
                    type: "post",
                    dataType: "json"
                });
            });
        ');
        return parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->render('phone-status-info', ['patient' => $this->patient, 'withInput' => $this->withInput]);
    }

}
