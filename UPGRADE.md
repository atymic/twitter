# 3.x Upgrade Guide

### Namespace Change

We have moved the package namespace from `Thujohn\Twitter` to `Atymic\Twitter`.

You need to change all references of the old namespace to the new one.

### Config file changes

The keys in the config file have changed. If you did not publish the config file and make changes, you do not need to do this step since the environment variable names have not changed.

Run `php artisan vendor:publish --provider="Atymic\Twitter\ServiceProvider\LaravelServiceProvider"` and compare the old `ttwitter` config file with the new one, moving your changes across. Then delete the old config file.
