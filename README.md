# OracleMigrationsBundle

This bundle integrates the Doctrine Migrations Bundle improving permformances in Oracle.


## Installation

### Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

    $ composer require bueue/oracle-migrations-bundle


### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the ``app/AppKernel.php`` file of your project:


    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Bueue\OracleMigrationsBundle\BueueOracleMigrationsBundle(),
            );

            // ...
        }

        // ...
    }


### Step 3: Add the driver_class option
Add the ``driver_class`` option to the default connection in your ``config.yml``:

    # Doctrine Configuration example
    doctrine:
        dbal:
            default_connection: default
            connections:
                default:
                    driver:   "oci8"
                    host:     "%database_host%"
                    port:     "%database_port%"
                    dbname:   "%database_name%"
                    user:     "%database_user%"
                    password: "%database_password%"
                    charset:  "%database_charset%"
                    driver_class: Bueue\OracleMigrationsBundle\MigrationsDriver

Usage
-----

See https://github.com/doctrine/migrations (commands remain the same)


Notes
-----
The bundle creates 3 materialized views:

* **MV\_MIG\_COLS\_DB\_NAME**
* **MV\_MIG\_FKS\_DB\_NAME**
* **MV\_MIG\_IDXS\_DB\_NAME**

where ``DB_NAME`` is your database name.
