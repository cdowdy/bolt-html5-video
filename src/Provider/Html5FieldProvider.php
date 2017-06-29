<?php

namespace Bolt\Extension\cdowdy\html5video\Provider;

use Bolt\Extension\cdowdy\html5video\Field\HTML5VideoField;
use Bolt\Storage\FieldManager;
use Silex\Application;
use Silex\ServiceProviderInterface;

class HTML5FieldProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['storage.typemap'] = array_merge(
			$app['storage.typemap'],
			[
				'h5video' => HTML5VideoField::class
			]
		);
		$app['storage.field_manager'] = $app->share(
			$app->extend(
				'storage.field_manager',
				function (FieldManager $manager) {
					$manager->addFieldType('h5video', new HTML5VideoField());
					return $manager;
				}
			)
		);
	}
	/**
	 * {@inheritdoc}
	 */
	public function boot(Application $app)
	{
	}
}