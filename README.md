# `zicht/page-bundle`

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
* Gerard van Helden <gerard@zicht.nl>
