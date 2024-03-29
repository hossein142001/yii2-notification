# Yii 2.0 Notification

[![Latest Stable Version](https://poser.pugx.org/hossein142001/yii2-notification/v/stable)](https://packagist.org/packages/hossein142001/yii2-notification) 
[![Latest Unstable Version](https://poser.pugx.org/hossein142001/yii2-notification/v/unstable)](https://packagist.org/packages/hossein142001/yii2-notification) 
[![Total Downloads](https://poser.pugx.org/hossein142001/yii2-notification/downloads)](https://packagist.org/packages/hossein142001/yii2-notification) [![License](https://poser.pugx.org/hossein142001/yii2-notification/license)](https://packagist.org/packages/hossein142001/yii2-notification)

## Install

```sh
$ composer require --prefer-dist "hossein142001/yii2-notification"
$ php ./yii migrate/up -p=@hossein142001/notification/migrations
```

## Configurate

```php
    'modules' => [
        // Notification by providers
        'notification' => [
          'class' => 'hossein142001\notification\Module',
          'providers' => [

              // SMS prostor-sms.ru
              'sms' => [
                'class' => 'hossein142001\notification\providers\sms',
                'config' => [
                  'gate' => '',
                  'port' => 80,
                  'login' => '',
                  'password' => '',
                  'signature' => '',
                ]
              ],

              // E-mail
              'email' => [
                'class' => 'hossein142001\notification\providers\email',
                'emailViewPath' => '@common/mail',
                'events' => [
                  'frontend\controllers\SiteController' => [
                    'Request',
                    'Signup',
                  ],
                  'backend\controllers\deal\SiteController' => [
                    'Login',
                    'Confirm',
                  ]
                ]                
              ]
          ],
        ]
    ],        
```

## Using

### By method send

```php
use hossein142001\notification\components\Notification;
    
    /* @var \hossein142001\notification\Module $sender */
    $sender = Yii::$app->getModule('notification');
    
    $notification = new Notification([
      'from' => [\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'],
      'to' => $deal['userSeller']['email'], // string or array
      'toId' => $deal['userSeller']['id'], // string or array
      'phone' => $deal['userSeller']['phone_number'], // string or array
      'subject' => "\"{$deal['userBuyer']['nameForOut']}\" offers you a deal for \"{$deal['ads']['product']->getName()}\"",
      'token' => 'TOKEN',
      'content' => "",
      'params' => [
        'productName' => $deal['ads']['product']->getName(),
        'avatar' => $deal['userBuyer']->avatarFile,
        'fromUserName' => $deal['userBuyer']['nameForOut'],
      ],
      'view' => ['html' => 'Request-html', 'text' => 'Request-text'],
      'path' => '@common/mail/deal',
      'notify' => ['growl', 'A confirmation email has been sent to your email'],
      'callback' => function(Provider $provider, $status){
        // Here you can process the response from notification providers
      }
    ]);
           
    $sender->sendEvent($notification);
```

### By Event

```php
use yii\base\Event;
use hossein142001\notification\components\Notification;

$event = new Notification(['params' => [
  'from' => [\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'],
  'to' => $user->email,
  'subject' => 'registration on the site ' . \Yii::$app->name,
  'emailView' => ['html' => 'signUp-html', 'text' => 'signUp-text'],
  'user' => $user,
  'phone' => $user->phone_number,
  'notify' => ['growl', 'A confirmation email has been sent to your email'],
]]);
Notification::trigger(self::className(),'Signup', $event);
```

or full

```php
$notification = new Notification([
  'from' => [\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'],
  'to' => $deal['userSeller']['email'], // string or array
  'toId' => $deal['userSeller']['id'], // string or array
  'phone' => $deal['userSeller']['phone_number'], // string or array
  'subject' => "\"{$deal['userBuyer']['nameForOut']}\" offers you a deal for \"{$deal['ads']['product']->getName()}\"",
  'token' => 'TOKEN',
  'content' => "",
  'params' => [
    'productName' => $deal['ads']['product']->getName(),
    'avatar' => $deal['userBuyer']->avatarFile,
    'fromUserName' => $deal['userBuyer']['nameForOut'],
  ],
  'view' => ['html' => 'Request-html', 'text' => 'Request-text'],
  'path' => '@common/mail/deal',
  'notify' => ['growl', 'A confirmation email has been sent to your email'],
  'callback' => function(Provider $provider, $status){
    // Here you can process the response from notification providers
  }
]);
Notification::trigger(self::className(),'Request', $notification);
```

### With mirocow/yii2-queue             

```php
    \Yii::$app->queue->getChannel()->push(new MessageModel([
        'worker' => 'notification',
        'method' => 'action',
        'arguments' => [
            'triggerClass' => self::class,
            'methodName' => 'Subscribe',
            'arguments' => [
                'param' => 'value'
            ],
        ],
    ]), 30);
```

## Tests

```bash
$ ./vendor/bin/codecept -c ./vendor/hossein142001/yii2-notification run unit
```
