# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

# 2.7.5 - 2019-05-06
- Expanded the zicht/admin-bundle dependency to v4 to have real PHP 7 support in this package

# 2.7.4 - 2019-01-29
- Fixed bug in `PageVoter::vote` where non-supported objects were wrongly allowed to be interpreted and not abstained from voting.

# 2.7.3 - 2019-01-28
- Not to be used, use 2.7.4

# 2.7.2 - 2018-07-23
## Fixed
- Changed handling of versioned contentitems, which was fixed in 2.7.1 but the bug had even more scenario's that were not covered.

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
