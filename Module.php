<?php

namespace hossein142001\notification;

use hossein142001\notification\components\JobEvent;
use hossein142001\notification\components\Notification;
use hossein142001\notification\components\Provider;
use hossein142001\notification\models\NotificationStatus;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\Json;

class Module extends \yii\base\Module implements BootstrapInterface
{
    const EVENT_BEFORE_SEND = 'beforeSend';

    const EVENT_AFTER_SEND = 'afterSend';

    public $controllerNamespace = 'hossein142001\notification\controllers';

    public $storeNotificationStatus = false;

    public $providers = [];

    private $_providers = [];

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        foreach ($this->providers as $providerName => $provider){
            if(empty($provider['events'])) continue;
            $this->attachEvents($providerName, $provider);
        }
    }

    /**
     * @param Notification $notification
     */
    public function sendEvent(Notification $notification)
    {
        if(isset($notification->data['provider'])) {
            $provider = Yii::createObject($notification->data[ 'provider' ]);
        } elseif(isset($notification->data['providerName'])) {
            $provider = $this->provider($notification->data['providerName']);
        } else {
            throw new Exception(Yii::t('app', 'Provider not found'));
        }

        /** @var Provider $provider */
        if(!$provider || !$provider->enabled){
            return;
        }

        $event = new JobEvent([
            'provider' => $notification->data['providerName'],
            'event' => $notification->name,
            'params' => $notification,
        ]);

        $this->trigger(self::EVENT_BEFORE_SEND, $event);

        if(!$event->isValid){
            return;
        }

        $provider->send($notification);
        $this->setProviderStatus($notification, $provider);


        $event->status = $provider->status;
        $event->errors = $provider->errors;
        $this->trigger(self::EVENT_AFTER_SEND, $event);
        unset($provider, $event);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function provider($name)
    {
        if (!isset($this->_providers[$name])) {
            if (isset($this->providers[$name])) {
                $this->_providers[$name] = Yii::createObject($this->providers[$name]);
            }
        }
        return $this->_providers[$name];
    }

    /**
     * @param $provider
     */
    public function attachEvents($providerName, $provider)
    {
        foreach ($provider['events'] as $className => $events) {
            foreach ($events as $eventName) {
                Notification::on($className, $eventName, [$this, 'sendEvent'], ['providerName' => $providerName, 'provider' => $provider]);
            }
        }
    }

    /**
     * @param $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * @param Notification $notification
     * @param Provider $provider
     * @return int|void
     */
    private function setProviderStatus(Notification &$notification, $provider = null)
    {
        if(!$this->storeNotificationStatus){
            return;
        }
        $providerName = $notification->data['providerName'];
        $status = new NotificationStatus;
        $status->provider = $providerName;
        $status->event = $notification->name;
        $status->params = Json::encode($notification->getAttributes());
        $status->status = $provider->getStatus();
        $status->save();
        return $status->id;
    }

}
