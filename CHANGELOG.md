# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2018-05-31
### Fixed
- Redirecting to pages not located in the phpBB root (such as the FAQs page) would fail because the
  redirect link was built relative to the current page instead of relative to the login/logout page.

## [1.0.1] - 2018-04-20
### Added
- Version checking

## [1.0.0] - 2018-03-14
- Initial release for phpBB 3.1/3.2, ported from my Prime Login and Prime Logout MODs for phpBB 3.0.