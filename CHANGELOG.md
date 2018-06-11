# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

# 3.0.3 - 2018-01-04
## Removed
* Removed the wronly named and wronly placed override for `$datagridValues` in the PageAdmin. At this point we don't know yet how to sort the admin-list. Leave this to the default here.

# 3.0.1 - # 3.0.2
## Fixed
* Various bugfixes concerning the 3.0-release.

# 3.0.0
## Breaking Changes
- Naming admin services of Pages and ContentItems using the fully
qualified class name instead of using only the last part.
Updating changes the service ids of the Page Admins. In `sonata_admin` config
ids should be changed accordingly.
To have easy access to the ids make use of symfony debug in
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

- The discriminator fields in the database do need an update.
The type fields in Page tables and ContentItem tables can be updated using
a migration. An example script could be;
```
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach ($this->updatePage as $oldType => $newType) {
            $this->addSql('UPDATE page SET type = ? WHERE type = ?', [$newType, $oldType]);
        }

        foreach ($this->updateContentItem as $oldType => $newType) {
            $this->addSql('UPDATE content_item SET type = ? WHERE type = ?', [$newType, $oldType]);
        }
    }
```

The arrays in the migrations class should contain the oldtype and the newtype.

# 2.7.1 - 2018-06-11
## Fixed
- Undo commit d59d452 (Bugfix on ContentItemTypeType)
  Commit d59d452 added a check on $subject->getId(), and only allowed the edit_url to be generated when
  a subject existed and was already persisted in the database.  Unfortunately this is not correct when
  the `zicht/versioning-bundle` is used (as those ContentItem entities do exist but do *not* have an id.

# 2.7.0 - 2018-02-19
## Added
- Added `TranslatePageEventPropagationBuilder`.  This adds translation links into the menu.  These
  provide the `zz` locale translation urls

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
