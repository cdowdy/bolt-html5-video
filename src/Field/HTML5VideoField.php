<?php

namespace Bolt\Extension\cdowdy\html5video\Field;

use Bolt\Storage\EntityManager;
use Bolt\Storage\Field\Type\FieldTypeBase;
use Bolt\Storage\QuerySet;

/**
 * Custom field type class for use in ContentTypes.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class HTML5VideoField extends FieldTypeBase {

	/**
	 * @param QuerySet           $queries
	 * @param mixed              $entity
	 * @param EntityManager|null $em
	 */
	public function persist( QuerySet $queries, $entity, EntityManager $em = null )
	{
		$key   = $this->mapping['fieldname'];
		$qb    = $queries->getPrimary();
		$value = $entity->get( $key );
//		if ( ! $value instanceof Url ) {
//			$value = Url::fromNative( $value );
//		}
		$qb->setValue( $key, ':' . $key );
		$qb->set( $key, ':' . $key );
		$qb->setParameter( $key, (string) $value );
	}

	/**
	 * @param $data
	 * @param $entity
	 */
	public function hydrate( $data, $entity )
	{
		$key = $this->mapping['fieldname'];
		$val = isset( $data[ $key ] ) ? $data[ $key ] : null;
		if ( $val !== null ) {
			$this->set( $entity, $val );
		}

	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'h5video';
	}

	/**
	 * json_array is deprecated as of dbal 2.6 http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#json-array
	 * so we'll use json instead!
	 */
	public function getStorageType()
	{
		return 'json';
	}

	/**
	 * @return string
	 */
	public function getTemplate()
	{
		return 'fields/_videolist-field.twig';
	}


	/**
	 * @return array
	 */
	public function getStorageOptions()
	{
		return [
			'default' => ''
		];
	}
}
