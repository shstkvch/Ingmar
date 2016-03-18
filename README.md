# Thomas
Modern development tools for WordPress (very rough & experimental.)

** NOTHING WORKS YET **

## Features
**Basic ORM** - model your application's objects, query them and relate them together on any fields. (50% working)

**Validation** - half-working. You can specify validation methods in the models (is_string, is_email, is_ClassName) and the thing will spit at you if you pass in the wrong data

## Example
For instance, if I were building a directory of surly greengrocers: --
```
/*
 * Define some models for our shops and shopkeepers --
 */
class Shop extends ThomasModel {

	/*
	 * Add fields to objects (stored as post meta)
	 */
	protected $fields = [
		'location',
		'price_range'
	];

	/*
	 * Validate your data before saving so you don't accidentally
	 * destroy everything and lose your hard-earned reputation:
	 */
	protected $validations = [
		'location' => 'is_string'
		'temperament' => [
			'is_integer',
			'$val <= 5', // basic conditional support
			'$val > 0'
		]
	];

}

class Shopkeeper extends ThomasModel {

	protected $fields = [
		'name',
		'temperament',
		'works_at'
	];

	/*
	 * Create arbitrary relationships between objects,
	 * just specify the name of the class you created:
	 */
	protected $relations = [
		'works_at' => 'Shop'
	];

	protected $validations = [
		'name' => [
			'is_string',
			'exists'
		],
		'temperament' => 'is_string'
	];

}

$shop = new Shop();
$shop->location = 'High Street';

$greengrocer = new Shopkeeper();
$greengrocer->name = 'Simon';
$greengrocer->works_at = $grocer;
$greengrocer->temperament = 'Rude';

/*
 * You only need to save the highest-level object,
 * related objects are saved automatically:
 */
$greengrocer->save();

// You can also pass arrays into the constructor:
$greengrocer2 = new Shopkeeper( [
	'name' => 'Tim';
	'works_at' => new Shop( [
		'location' => 'South Street'
	] ),
	'temperament' => 'polite'
] );

$greengrocer2->save();

/*
 * You can now query the greengrocers model by
 * chaining methods -- much nicer than faffing with
 * WP_Query:
 */
$collection = Shopkeeper::where( 'temperament', '==', 'polite' )->get();

/*
 * ThomasCollections are very basic right now. You can
 * get the first, last items and enumerate them --
 */
var_dump( $collection->first() );

```
