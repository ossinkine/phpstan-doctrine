<?php declare(strict_types = 1);

namespace PHPStan\Rules\Doctrine\ORM;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Doctrine\DescriptorRegistry;
use PHPStan\Type\Doctrine\Descriptors\BigIntType;
use PHPStan\Type\Doctrine\Descriptors\BinaryType;
use PHPStan\Type\Doctrine\Descriptors\DateTimeImmutableType;
use PHPStan\Type\Doctrine\Descriptors\DateTimeType;
use PHPStan\Type\Doctrine\Descriptors\StringType;
use PHPStan\Type\Doctrine\ObjectMetadataResolver;

class EntityColumnRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new EntityColumnRule(
			new ObjectMetadataResolver(__DIR__ . '/entity-manager.php', null),
			new DescriptorRegistry([
				new BigIntType(),
				new StringType(),
				new DateTimeType(),
				new DateTimeImmutableType(),
				new BinaryType(),
			])
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/MyBrokenEntity.php'], [
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenEntity::$id type mapping mismatch: database can contain string but property expects int|null.',
				19,
			],
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenEntity::$one type mapping mismatch: database can contain string|null but property expects string.',
				25,
			],
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenEntity::$two type mapping mismatch: property can contain string|null but database expects string.',
				31,
			],
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenEntity::$three type mapping mismatch: database can contain DateTime but property expects DateTimeImmutable.',
				37,
			],
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenEntity::$four type mapping mismatch: database can contain DateTimeImmutable but property expects DateTime.',
				43,
			],
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenEntity::$four type mapping mismatch: property can contain DateTime but database expects DateTimeImmutable.',
				43,
			],
		]);
	}

	public function testSuperclass(): void
	{
		$this->analyse([__DIR__ . '/data/MyBrokenSuperclass.php'], [
			[
				'Property PHPStan\Rules\Doctrine\ORM\MyBrokenSuperclass::$five type mapping mismatch: database can contain resource but property expects int.',
				17,
			],
		]);
	}

	/**
	 * @dataProvider generatedIdsProvider
	 */
	public function testGeneratedIds(string $file, array $expectedErrors): void
	{
		$this->analyse([$file], $expectedErrors);
	}

	public function generatedIdsProvider(): Iterator
	{
		yield 'not nullable' => [__DIR__ . '/data/GeneratedIdEntity1.php', []];
		yield 'nullable column' => [
			__DIR__ . '/data/GeneratedIdEntity2.php',
			[
				[
					'Property PHPStan\Rules\Doctrine\ORM\GeneratedIdEntity2::$id type mapping mismatch: database can contain string|null but property expects string.',
					19,
				],
			],
		];
		yield 'nullable property' => [__DIR__ . '/data/GeneratedIdEntity3.php', []];
		yield 'nullable both' => [__DIR__ . '/data/GeneratedIdEntity4.php', []];
	}

}