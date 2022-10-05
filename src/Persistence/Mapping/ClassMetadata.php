<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use ReflectionClass;

/**
 * Contract for a Doctrine persistence layer ClassMetadata class to implement.
 *
 * @template-covariant T of object
 */
interface ClassMetadata
{
    /**
     * Gets the fully-qualified class name of this persistent class.
     *
     * @return string
     * @psalm-return class-string<T>
     */
    public function getName();

    /**
     * Gets the mapped identifier field name.
     *
     * The returned structure is an array of the identifier field names.
     *
     * @return array<int, string>
     * @psalm-return list<string>
     */
    public function getIdentifier();

    /**
     * Gets the ReflectionClass instance for this mapped class.
     *
     * @return ReflectionClass<T>
     */
    public function getReflectionClass();

    /**
     * Checks if the given field name is a mapped identifier for this class.
     *
     * @return bool
     */
    public function isIdentifier(string $fieldName);

    /**
     * Checks if the given field is a mapped property for this class.
     *
     * @return bool
     */
    public function hasField(string $fieldName);

    /**
     * Checks if the given field is a mapped association for this class.
     *
     * @return bool
     */
    public function hasAssociation(string $fieldName);

    /**
     * Checks if the given field is a mapped single valued association for this class.
     *
     * @return bool
     */
    public function isSingleValuedAssociation(string $fieldName);

    /**
     * Checks if the given field is a mapped collection valued association for this class.
     *
     * @return bool
     */
    public function isCollectionValuedAssociation(string $fieldName);

    /**
     * A numerically indexed list of field names of this persistent class.
     *
     * This array includes identifier fields if present on this class.
     *
     * @return array<int, string>
     */
    public function getFieldNames();

    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * @return array<int, string>
     */
    public function getIdentifierFieldNames();

    /**
     * Returns a numerically indexed list of association names of this persistent class.
     *
     * This array includes identifier associations if present on this class.
     *
     * @return array<int, string>
     */
    public function getAssociationNames();

    /**
     * Returns a type name of this field.
     *
     * This type names can be implementation specific but should at least include the php types:
     * integer, string, boolean, float/double, datetime.
     *
     * @return string|null
     */
    public function getTypeOfField(string $fieldName);

    /**
     * Returns the target class name of the given association.
     *
     * @return string|null
     * @psalm-return class-string|null
     */
    public function getAssociationTargetClass(string $assocName);

    /**
     * Checks if the association is the inverse side of a bidirectional association.
     *
     * @return bool
     */
    public function isAssociationInverseSide(string $assocName);

    /**
     * Returns the target field of the owning side of the association.
     *
     * @return string
     */
    public function getAssociationMappedByTargetField(string $assocName);

    /**
     * Returns the identifier of this object as an array with field name as key.
     *
     * Has to return an empty array if no identifier isset.
     *
     * @return array<string, mixed>
     */
    public function getIdentifierValues(object $object);
}
