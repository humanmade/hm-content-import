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

## Parallelization

HMCI supports parallelization of imports, whereby each `process_item()` on the importer will run in a separate process. This is achieved by using the `pcntl_fork()` function. However, this is opt-in per importer as implementors must be careful to ensure that the `process_item()` does not rely on data populated by other calls to process_item(). For example, if `process_item()` adds to a global array, or array on the importer for a customer `$import_map`, then this data will not be available to other processes.

If your importer is thread-safe in this way, you should implement the `HMCI\Iterator\Thread_Sage` interface.

When importers are in in parallel, `wp_defer_term_counting()` is called, so `wp term count <taxonomy>` will need to be run after the import.

Pass `--threads=<number>` to the CLI command to specify the number of threads to use. For maximum performance, this should be atleast set to the number of CPU cores on the server. In many cases,
`process_item()` will be I/O  bound so using a higher number of threads than cores can still have a positive impact.

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
UPDATE wp_postmeta SET meta_key = 'hmci_canonical_id' WHERE meta_key LIKE 'hmci_canonical_id_%';
UPDATE wp_usermeta SET meta_key = 'hmci_canonical_id' WHERE meta_key LIKE 'hmci_canonical_id_%';
UPDATE wp_commentmeta SET meta_key = 'hmci_canonical_id' WHERE meta_key LIKE 'hmci_canonical_id_%';
UPDATE wp_termmeta SET meta_key = 'hmci_canonical_id' WHERE meta_key LIKE 'hmci_canonical_id_%';
```
