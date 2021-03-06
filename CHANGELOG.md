# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 6.1.4 - 2021-06-23
### Added
- Added check in viewAction for the case an OverviewPage doesn't need a Controller
### Fixed
- Set correct return type for getController() in ControllerPageInterface.php

## 6.1.3 - 2021-03-01
### Added
- Added support for Psalm static analysis
### Fixed
- Set correct return type on Page::getLanguage()

## 6.1.2 - 2021-02-4
### Changed
- Made `LanguageAwareAliasingStrategy` a real service so it can be modified

## 6.1.1 - 2020-11-05
### Fixed
- Resolve the problem where the current weight is not set to the converted content-item

## 6.1.0 - 2020-10-29
### Added
- Added the AdminObjectDuplicateListener to listen to an event, dispatched in the admin-bundle, to set isPublic to false on duplicate.

## 6.0.3 - 2020-09-08
### Fixed
- Forward merge of v5.0.16: Trigger 403 response instead of redirecting to the login page
  (catch AccessDeniedException and throw AccessDeniedHttpException instead)

## 6.0.2 - 2020-07-20
### Added
- `ScheduledContentRepositoryTrait` to easily apply conditions to queries where the published state of an entity is relevant.

## 6.0.1 - 2020-06-08
### Changed
- Reverted the 2nd argument for `addChild` in `GenerateAdminServicesCompilerPass` as it causes bugs in `sonata-project/admin-bundle` handling parent-assocations.

## 6.0.0 - 2020-05-15
### Added
- Support for Symfony 4.x
### Removed
- Support for Symfony 3.x
### Changed
- Removed Zicht(Test)/Bundle/PageBundle/ directory depth: moved all code up directly into src/ and test/

## 5.0.16 - 2020-09-08
### Fixed
- Forward merge of v4.0.7: Trigger 403 response instead of redirecting to the login page
  (catch AccessDeniedException and throw AccessDeniedHttpException instead)

## 5.0.15 - 2020-05-15
### Changed
- Switched from PSR-0 to PSR-4 autoloading

## 5.0.14 - 2020-04-29
### Changed
- Use FQCN for form types

## 5.0.13 - 2019-08-05
### Fixed
- Fixed that tests are no longer auto loaded on production.

## 5.0.12 - 2019-07-09
### Fixed
- Merge from 4.0.5 and 4.0.6.
- Merge from 3.0.10 and 3.0.11.
- Merge from 2.7.3, 2.7.4, 2.7.5, and 2.7.6.
- Fix check in `ScheduledContentVoter`, using `is_object`, as this also covers `!is_null`
  but also some other cases.

## 5.0.11
### Changed
- Removed deprecations in `zicht_page.page_manager_subscriber`, we inject now only what is needed, no circular references occur
- `zicht_page.page_manager` and `zicht_page.controller.view_validator` are now public services as they are used widespread (in Controllers and Traits) and are not easily injectable

## 5.0.10
### Fixed
- Ignore checks for impersonated users in `AbstractAdminAwareVoter`

## 5.0.9 - 2019-01-29
### Fixed
- Regressionbug in `PageVoter::vote` where non-supported objects were wrongly allowed to be interpreted and not abstained from voting.

## 5.0.8 - 2019-01-10
### Fixed
- Rebased to include v3.0.9: Fix for the content item type view var to be constructed from the FQCN again

## 5.0.7 - 2018-11-05
### Changed
- Changed the variable `$eventDispatcher` in `AdminMenu/EventPropagationBuilder` to `protected`. So it can be overriden outside the class

## 5.0.6 - 2018-09-19
### Changed
- Replace empty_value for Symfony 3.x

## 5.0.2 - 2018-07-20
### Fixed
- Unittests by referencing the correct expect-method
- `context->addViolationAt` no longer exists and replaced with `buildViolation`

## 5.0.1 - 2018-07-20
### Fixed
- Updated Twig syntax for `replace` in `form_theme.html.twig`

## 5.0.0 - 2018-06-22
### Added
- Support for Symfony 3.x
### Removed
- Support for Symfony 2.x

## 4.0.7 - 2020-09-08
### Fixed
- Forward merge of v3.0.12: Trigger 403 response instead of redirecting to the login page
  (catch AccessDeniedException and throw AccessDeniedHttpException instead)

## 4.0.6 - 2019-07-09
### Fixed
- Merge from 3.0.10 and 3.0.11.
- Merge from 2.7.3, 2.7.4, 2.7.5, and 2.7.6.
- The `PageVoter` class now takes into account admin users, i.e. when
  you are logged in with ROLE_ADMIN or ROLE_SUPER_ADMIN you will be granted
  access.

  Note that ideally it would be better to have a separate voter that grants
  access whenever you are logged in as admin.  However, because this might
  conflict with voting strategies such as 'every voter must agree', and we do
  not know if this such strategies are in use, we have decides on this change,
  even though it is not perfect.

## 4.0.5 - 2019-01-10
### Fixed
- Rebased to include v3.0.9: Fix for the content item type view var to be
constructed from the FQCN again

## 4.0.4 - 2018-08-13
### Fixed
- Version 3.0.8 merged into v4: Check for pages that returns null for
getContentItemMatrix

## 4.0.3 - 2018-07-23
### Changed
- Version 3.0.7 merged into v4

## 4.0.2 - 2018-06-11
### Changed
- Versions 3.0.5 and 3.0.6 merged into v4

## 4.0.1 - 2018-02-19
### Fixed
- Correct versions in Composer JSON for zicht/admin-bundle and zicht/url-bundle

## 4.0.0 - 2018-01-24
### Added
- Support for PHP version 7
### Removed
- Support for PHP version 5 (^5.6)

## 3.0.12 - 2020-09-08
### Fixed
- Forward merge of v2.7.7: Trigger 403 response instead of redirecting to the login page
  (catch AccessDeniedException and throw AccessDeniedHttpException instead)

## 3.0.11 - 2019-07-05
### Fixed
- Expanding the zicht/url-bundle dependency to v3 to have real PHP 7 support
  in this package

## 3.0.10 - 2019-05-06
### Fixed
- Merged version 2.7.5 into v3, expanding the zicht/admin-bundle dependency to
  v4 to have real PHP 7 support in this package, which was removed between
  3.0.4 and 3.0.5 (as non-breaking!?)

## 3.0.9 - 2019-01-10
### Fixed
- Fix for the content item type view var to be constructed from the FQCN again

## 3.0.8 - 2018-08-13
### Fixed
- Check for pages that returns null for getContentItemMatrix

## 3.0.7 - 2018-07-23
### Changed
- Update handling versioned contentitems

## 3.0.6 - 2018-06-11
### Added
- Add translation links into the cms menu
### Changed
- Fix issue when page-bundle is used with versioning-bundle
- Fix typo in class name and add Router not available check

## 3.0.5 - 2018-01-24
### Changed
- reverted voters fix because it breaks code
- removed duplicated code and moved to new branch for php >= 7

## 3.0.4 - 2018-01-23
### Fixed
- Fixed the security voters
### Changed
- Changed the maintainers in the README.md

## 3.0.3 - 2018-01-04
### Removed
* Removed the wronly named and wronly placed override for `$datagridValues` in the PageAdmin. At this point we don't know yet how to sort the admin-list. Leave this to the default here.

## 3.0.1 - # 3.0.2
### Fixed
* Various bugfixes concerning the 3.0-release.

## 3.0.0
### Breaking Changes
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

## 2.7.7 - 2020-09-08
### Fixed
- Trigger 403 response instead of redirecting to the login page
  (catch AccessDeniedException and throw AccessDeniedHttpException instead)

## 2.7.6 - 2019-05-06
### Fixed
- Expanded the zicht/admin-bundle dependency to v4 to have real PHP 7 support in this package

## 2.7.5 - 2019-02-15
### Fixed
- Fixed leftover bugs related to PHP 7.2

## 2.7.4 - 2019-01-29
### Fixed
- Fixed bug in `PageVoter::vote` where non-supported objects were wrongly allowed to be interpreted and not abstained from voting.

## 2.7.3 - 2019-01-28
### Fixed
- Not to be used, use 2.7.4

## 2.7.2 - 2018-07-23
### Fixed
- Changed handling of versioned contentitems, which was fixed in 2.7.1 but the bug had even more scenario's that were not covered.

## 2.7.1 - 2018-06-11
### Fixed
- Undo commit d59d452 (Bugfix on ContentItemTypeType)
  Commit d59d452 added a check on $subject->getId(), and only allowed the edit_url to be generated when
  a subject existed and was already persisted in the database.  Unfortunately this is not correct when
  the `zicht/versioning-bundle` is used (as those ContentItem entities do exist but do *not* have an id.

## 2.7.0 - 2018-02-19
### Added
- Added `TranslatePageEventPropagationBuilder`.  This adds translation links into the menu.  These
  provide the `zz` locale translation urls

## 2.6.0 - 2017-10-05
### Added
- Added a service `zicht_page.controller.view_validator` to vote on weather a page is viewable or not

## 2.3.0
- Includes compatibility with an admin-less kernel

## 2.4.0
- Includes a possibility to configure the aliasing conflict strategies

## 2.0.0
### Breaking Changes
- added support for Symfony >= _2.3_
- added FormEvent::SUBMIT listener to PageAdmin - since FormBuilder doesn't have getParent() anymore

## 1.3.4
- Symfony < _2.3_
