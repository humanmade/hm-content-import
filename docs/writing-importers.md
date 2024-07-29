# Writing Importers

Importers in HMCI are classes that extend the `HMCI\Iterator\Base` class. This class provides a standard interface for iterating over data, and a standard interface for processing data. An importer is typically a single data type, so it's likely that a project migration will have multiple importers.

Think of an importer as a "data source" which will fetch items to be imported, and then process each iteam to be imported. An importer need only implement the `get_items( int $offset, int $count ): array<mixed>` method, which is used to fetch items from the data source. The type of each item returned by this method is up to the importer. Optionally `get_count(): int` can be implemented to return the total number of items in the data source, if the count can be calculated in a more performant way than `count( while ( get_items() )... )`.

The importer class then must implement the `process_item( mixed $item ) : bool` method, which is responsible for importing the item. This method is passed each item returned by the `get_items()` method. The bulk of the importer logic is implemented in this method.

## Iterators

As mentioned, all importers should extend the `HMCI\Iterator\Base` class. That is because importers _are_ `Iterators`. It's unlikley you'll extend the `HMCI\Iterator\Base` class directly though, as HMCI providers a number of base iterators that you can extend to create your own iterators. For example, if you are importering data from a collection of CSV file, you can extend the `HMCI\Iterator\Files\CSV` class to create your own iterator. This way you will only need to implement the `process_item( mixed $item ) : bool` method, and HMCI will handle the rest.

See all the iterators in the [inc/classes/iterator](./inc/classes/iterator) folder.

## Inserters

Inserters in HMCI are classes that extend the `HMCI\Inserter\Base` class. HMCI bundles standard inserters for most WordPress types, but it's possible to write your own inserters for custom data types.

The Inserter class will automatically handle the insert/update logic based off a "canonical id" field (see below).

See all the importers in the [inc/classes/inserter](./inc/classes/inserter) and the WordPress specific inserters in the [inc/classes/inserter/wp](./inc/classes/inserter/wp) folder.

## Canonical IDs

When writing importers with HMCI, all items that are being imported will have a canonical ID. This is a unique identifier from the source data, and is used to match already imported items in the WordPress database.

Note: canonical IDs should be unique accross importers if those importers are importing the same WordPress data type. For example, if you have an importer that imports articles with canonincal IDs raniging from 1-100, and another importer imports products that also have canonical IDs ranging from 1-100, then the two importers will have a conflict. Therefor it's recommended to use a prefix for the canonical IDs, such as `article_1`, `article_2`, etc.

## Examples

Check the examples below to see how to write your own importers:

- [Basic Importer](./docs/example-importer.php)
