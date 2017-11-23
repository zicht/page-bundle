# 3.0.0
## Breaking Changes
- Naming admin services of Pages and ContentItems using the fully 
qualified class name instead of using only the last part. 
So when updating all the service names of admins change all strings
in configs where you depend on the service names.
To have easy access to the names make use of symfony debug in 
the console `php app/console debug:container` which will give 
a list of all registered services.
- Registering content items via the `$page->getContentItemMatrix()` 
has changed. 
Old situation 
```
    /**
     * @{inheritDoc}
     */
    public function getContentItemMatrix()
    {
        return ContentItemMatrix::create('Zicht\Bundle\SomeSiteBundle\Entity\ContentItem')
            ->region('center')
                ->type('Card')
                ->type('Contact');
    }
```
New situation
```
    /**
     * @{inheritDoc}
     */
    public function getContentItemMatrix()
    {
        return ContentItemMatrix::create()
            ->region('center')
                ->type(Zicht\Bundle\SomeSiteBundle\Entity\ContentItem\Card::class')
                ->type(Zicht\Bundle\SomeSiteBundle\Entity\ContentItem\Contact::class);
    }
```
Noticeable 2 changes here. 
First `create()` has no namespace params anymore.
Second `type()` only accepts an existing class name. 
So the best practice would be to use `::class` for this. 

# 2.6.0 - 2017-10-05
## Added
- Added a service `zicht_page.controller.view_validator` to vote on weather a page is viewable or not

# 2.3.0
- Includes compatibility with an admin-less kernel

# 2.4.0
- Includes a possibility to configure the aliasing conflict strategies

## Version 2.0.0
### Breaking Changes
- added support for Symfony >= _2.3_
- added FormEvent::SUBMIT listener to PageAdmin - since FormBuilder doesn't have getParent() anymore

## Version 1.3.4
- Symfony < _2.3_
