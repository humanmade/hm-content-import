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

