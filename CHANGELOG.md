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
