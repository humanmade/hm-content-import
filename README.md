# HM Content Import

Migration framework for WordPress, attempts to reduce overhead in migrating content from differing data sources

## Introduction

HMCI is an extensible, performant, scriptable, pausable, resumable, and horizontally scalable WP-CLI framework for importing large amounts of content into WordPress. It is a base framework for performing custom migrations of large amounts of content from any source and therefore requires the development of an import handler for each type of content being imported.

It typically supports both new imports and delta imports, although this is dependent on the individual import handlers written for each migration.

HMCI can be run in multiple threads in parallel, making it horizontally scalable in order to reduce the time required to process large imports. It has been used as the basis of large data migrations for Human Made clients such as The Sun, L'Express, Capgemini, and others.

## Ingestion

HMCI supports ingestion of data via iterators that support:

* Single files
  - CSV
  - JSON
* Directories of files
  - JSON
  - XML
* A direct MySQL database connection
* WordPress Posts (for internal migration)

## Insertion

HMCI supports inserting imported data into:

* WordPress
  - Attachments
  - Comments
  - Guest Authors (used by Co-Authors Plus and PublishPress)
  - Posts
  - Taxonomy Terms
  - Users
* Files
  - CSV

## Migrating From Version 1

In Version 2 we changed the way canonical IDs are stored. This means that you will need to migrate your existing data to the new format, if you are planning to resume / to delta imports with data that was imported under Version 1.

Run the following SQL query to migrate your existing data:

```sql
UPDATE wp_postmeta SET meta_key = CONCAT('hmci_canonical_id_', meta_value) WHERE meta_key = 'hmci_canonical_id';
UPDATE wp_usermeta SET meta_key = CONCAT('hmci_canonical_id_', meta_value) WHERE meta_key = 'hmci_canonical_id';
UPDATE wp_commentmeta SET meta_key = CONCAT('hmci_canonical_id_', meta_value) WHERE meta_key = 'hmci_canonical_id';
UPDATE wp_termmeta SET meta_key = CONCAT('hmci_canonical_id_', meta_value) WHERE meta_key = 'hmci_canonical_id';
```

Should you need to revert this migration, you can run the following SQL query:

```sql
UPDATE wp_postmeta SET meta_key = 'canonical_id' WHERE meta_key LIKE 'canonical_id_%';
UPDATE wp_usermeta SET meta_key = 'canonical_id' WHERE meta_key LIKE 'canonical_id_%';
UPDATE wp_commentmeta SET meta_key = 'canonical_id' WHERE meta_key LIKE 'canonical_id_%';
UPDATE wp_termmeta SET meta_key = 'canonical_id' WHERE meta_key LIKE 'canonical_id_%';
```
