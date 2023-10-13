About
====

Drupal site for demonstration purposes for the talk about Drupal background
tasks in [DrupalCon Lille](https://events.drupal.org/lille2023/session/when-things-take-longer-you-thought-moving-workloads-background-processes).

Slides are available [here](https://plopesc.github.io/background_tasks_slides).

Setup
====

Clone this repository:

```
git clone git@github.com:plopesc/say_hi.git
```
If you are planning to use ddev, now it's a good moment for ```ddev start```.

Download composer dependencies if you haven't yet ```ddev composer install```

You can run ```ddev exec ./install.sh```

Forms
====

Say Hi module (web/modules/custom/say_hi) provides 3 different forms to compare
the different behavior of the form depending on the approach taken to process
the request:

* **/hi**: Traditional approach, performing all the tasks in the main thread.
* **/hi-batch**: Batch API approach. Using a batch to maintain resources under control
* **/hi-queue**: Queue API approach. Using a secondary queue to perform the tasks during cron run

Tips
====
To add new users to see the difference between the different forms, new users
can be added using ```drush genu 1000``` to add new 1000 users.

To process the queue you can use the queue worker, triggering it via ```drush cron```
or run the queue specific drush command ```drush queue:run say_hi_greetings```
