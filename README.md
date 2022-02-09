# Config

Simple Config reader that can map json objects to classes.

## Example

```php
$config = new Config();
$config->loadJsonFile(__DIR__."/../.config.json");
$config->map([
    CoreConfig::class => "core",
    DbConfig::class => "db",
    LoggingConfig::class => "logging"
]);

$core = $config->getConfig(CoreConfig::class);
```
