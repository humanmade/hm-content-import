# Running Imports

HMCI provides a CLI command for running imports. Each importer is a sub-command of the `hmci import` command.

Importers are typically re-runable, meaning that items already previoulsy imported will be updated with the new data. Therefore it's usually safe to re-run an import once the importer may have been updated to import additional data.

## Resuming / splitting imports

Running an import will internally track the progress, so if at any point the importer is interrupted, it can be resumed from the last saved progress. This is done with the `--resume` flag.

Importers can also be "split" or chunked with the `--count` and `--offset` arguments. This allows you to split an import into multiple chunks, which can be run in parallel (manually). When running in parallel, the `--thread_id` argument can be used to keep a unique progress value per each, when threading. For example:

```
wp hmci import posts --count=100 --offset=0 --thread_id=1    // Run from shell 1
wp hmci import posts --count=100 --offset=100 --thread_id=2  // Run from shell 2
```

If the first import process is interrupted, the progress can be resumed with `wp hmci import posts --count=100 --offset=0 --thread_id=1 --resume`.

## Importer specific arguments

Each registered importer can have it's own spcecific arguments. Running `wp hmci import <importer> --help` will show the available arguments for that importer.


```
SYNOPSIS

  wp hmci import <importer> [--count=<number>] [--offset=<number>] [--resume] [--verbose] [--disable_global_terms] [--disable_trackbacks] [--disable_intermediate_images] [--define_wp_importing]
  [--thread_id=<id>]

  <importer>
    The importer to run.

  [--count=<number>]
    The number of items to import. Defaults to all.

  [--offset=<number>]
    The number of items to skip. Defaults to 0.

  [--resume]
    Resume the import from the last saved progress.

  [--verbose]
    Show verbose output.

  [--disable_global_terms]
    Disable global terms. Defaults to true.

  [--disable_trackbacks]
    Disable trackbacks. Defaults to true.

  [--disable_intermediate_images]
    Disable intermediate image sizes.

  [--define_wp_importing]
    Define WP_IMPORTING constant. Defaults to true.

  [--thread_id=<id>]
    Thread ID to keep a unique progress value per each, when threading.
```
