# Thomas
Modern development tools for WordPress (very rough & experimental.)

## Features
**Basic ORM** - model your application's objects, query them and relate them together on any fields.

For instance, if I were building a directory of surly greengrocers --
```
class Shop extends ThomasModel {

	// add fields to objects (stored as post meta)
	protected $fields = array(
		'location',
		'price_range'
	);

}

class Shopkeeper extends ThomasModel {

	protected $fields = array(
		'name',
		'temperament',
		'works_at'
	);

	// create arbitrary relationships between objects
	protected $relations = array(
		'works_at' => 'Shop'
	)
}

$shop = new Shop();
$shop->location = 'High Street';

$greengrocer = new Shopkeeper();
$greengrocer->works_at = $grocer;
$greengrocer->temperament = 'Rude';

// You only need to save the highest-level object
$shopkeeper->save();
```
