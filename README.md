# `zicht/page-bundle`
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zicht/page-bundle/badges/quality-score.png?b=release%2F2.5.x)](https://scrutinizer-ci.com/g/zicht/page-bundle/?branch=release%2F2.5.x)
[![Code Coverage](https://scrutinizer-ci.com/g/zicht/page-bundle/badges/coverage.png?b=release%2F2.5.x)](https://scrutinizer-ci.com/g/zicht/page-bundle/?branch=release%2F2.5.x)
[![Build Status](https://scrutinizer-ci.com/g/zicht/page-bundle/badges/build.png?b=release%2F2.5.x)](https://scrutinizer-ci.com/g/zicht/page-bundle/build-status/release/2.5.x)

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

# Maintainer(s)
* Rik van der Kemp <rik@zicht.nl>
* Muhammed Akbulut <muhammed@zicht.nl>

