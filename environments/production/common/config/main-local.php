<?php

return [
	'components' => [
		'db'     => [
			'class'    => 'yii\db\Connection',
			'dsn'      => 'mysql:host=localhost;dbname=mini-elba',
			'username' => 'root',
			'password' => '',
			'charset'  => 'utf8',
		],
		'mailer' => [
			'class'            => 'yii\swiftmailer\Mailer',
			'viewPath'         => '@common/mail',
			'useFileTransport' => false,
			'transport'        => [
				'class'      => 'Swift_SmtpTransport',
				'host'       => 'smtp.gmail.com',
				'username'   => 'no-reply@codepeckers.ru',
				'password'   => 'IPNVAE[i0obou35',
				'port'       => '587',
				'encryption' => 'tls',
			],
		],
	],
];
