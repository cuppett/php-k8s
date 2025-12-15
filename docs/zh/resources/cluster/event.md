# Event

Events provide insight into what is happening inside a cluster.

## List Events

```php
$events = $cluster->getAllEvents('default');

foreach ($events as $event) {
    echo "{$event->getReason()}: {$event->getMessage()}\n";
}
```

## Watch Events

```php
$cluster->event()->watchAll(function ($type, $event) {
    echo "[{$event->getType()}] {$event->getReason()}: {$event->getMessage()}\n";
    return false;
}, ['namespace' => 'default']);
```

---

*Originally from renoki-co/php-k8s documentation, adapted for cuppett/php-k8s fork*
