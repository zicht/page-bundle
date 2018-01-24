# `zicht/page-bundle`
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zicht/page-bundle/badges/quality-score.png?b=release%2F3.x)](https://scrutinizer-ci.com/g/zicht/page-bundle/?branch=release%2F3.x)
[![Code Coverage](https://scrutinizer-ci.com/g/zicht/page-bundle/badges/coverage.png?b=release%2F3.x)](https://scrutinizer-ci.com/g/zicht/page-bundle/?branch=release%2F3.x)
[![Build Status](https://scrutinizer-ci.com/g/zicht/page-bundle/badges/build.png?b=release%2F3.x)](https://scrutinizer-ci.com/g/zicht/page-bundle/build-status/release/3.x)

Provides composable pages through a simple entity model.

## Approach

The idea is that any content page of any type is always composed of a 
few "content items", which are in turn of any type. This is modelled
in a one-to-many related of `Page` -> `ContentItem`, where both 
`Page` and `ContentItem` follow an Entity inheritance model, so 
"type" of page and "type" of content item are directly implemented
by simply having another derivative class.

## A piece of the puzzle

See the documentation for `zicht/cms` (https://github.com/zicht/cms)
for more information on how the `zicht/page-bundle` relates to the
other Zicht Bundles

## Voters
To enable the use of [voters](http://symfony.com/doc/current/security/voters.html) in your configuration you should properly 
configure `security.yml` or in the `security` part of your config.

At least you should have these lines:
```
security:
    access_decision_manager:
            strategy: unanimous
```

### PageVoter
The PageVoter requires the page to have `Zicht\Bundle\PageBundle\Model\PageInterface` implemented.
This Voter looks for the `isPublic` public function to check whether a page can be displayed to the public.

### ScheduledContentVoter
The ScheduledContentVoter requires the page to have `Zicht\Bundle\PageBundle\Model\ScheduledContentInterface` implemented.
With this voter a page can be scheduled for publication. 

# Maintainers
* Philip Bergman <philip@zicht.nl>
* Boudewijn Schoon <boudewijn@zicht.nl>
* Erik Trapman <erik@zicht.nl>
