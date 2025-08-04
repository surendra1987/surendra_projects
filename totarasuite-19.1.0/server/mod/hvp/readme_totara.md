# Readme - Totara

## Versions at a glance
| repo           | version | maintainer | repo URL                                       |
|----------------|---------|------------|------------------------------------------------|
| totara/mod_hvp | latest  | Totara     | https://github.com/totara/mod_hvp              |
| moodle-mod_hvp | v1.25   | H5P        | https://github.com/h5p/moodle-mod_hvp          |
| H5P editor     | 53dc7bd | H5P        | https://github.com/h5p/h5p-editor-php-library/ |
| H5P library    | dca8f6d | H5P        | https://github.com/h5p/h5p-php-library/        |
| H5P reporting  | 0968db5 | H5P        | https://github.com/h5p/h5p-php-library/        |

You'll notice that the version for the editor/library/reporting submodules are all commit hashs - these are what they show up as when viewing the parent plugin repo, however these are tagged at version 1.25 as well at the time of writing.

## Initial Install Steps
The H5P plugin included in the codebase originates from the H5P moodle plugin:
https://github.com/h5p/moodle-mod_hvp

Totara has it's own private fork of this repo:
https://github.com/totara/mod_hvp

The Totara fork has a branch named totara-stable which was branched off of the H5P upstream repos stable branch. H5P recommends when using their repo that you work from their stable branch as that is where they run their releases from.

The repo houses the mod_hvp plugin repo, however this repo makes use of three submodules:
1. editor - https://github.com/h5p/h5p-editor-php-library/
2. library - https://github.com/h5p/h5p-php-library/
3. reporting - https://github.com/h5p/h5p-php-report/

The codebase when merged into TXP has these submodules cloned into the codebase, they are not added as submodules!

Due to the depth of git archives, you cannot add these submodules via git archive on the base repo, instead you need to run git archive for each submodule. The totara fork has a script called `prep.sh`: https://github.com/totara/mod_hvp/blob/totara-stable/prep.sh

When you first clone the repo you'll also want to checkout the submodules
`git submodule update --init --recursive`
Then you can run the `prep.sh` script - you simply give it the location of where to install in the TXP codebase:
`./prep.sh ~/totara-sites/integration/server/mod/hvp`
The script will go through and run git archive on the main repo and the submodules, then extract them into the directory given and will remove a few files as well as the prep.sh script itself.

## Updating Steps
As mentioned in the `Initial Install Steps` - the code exists in a totara fork of the H5P repo. The fork however is private, so the standard github actions of pulling changes from upstream don't exist / work in private forks.
### Updating mod_hvp
1. Checkout out the codebase
2. Add the original h5p moodle repo as another remote: `git remote add h5p https://github.com/h5p/moodle-mod_hvp`
3. Ensure you fetch all so git knows of the branchs in the h5p remote: `git fetch --all`
4. Checkout a new branch based off of the totara-stable branch `git checkout -b latest-updates origin/totara-stable`
5. Then pull in the upstream changes from H5P - there will probably be conflicts with any changes made since the last pull: `git pull h5p stable --rebase`
6. You'll need to step through any conflicts and resolve them as you go along. You only need to deal with the conflicts once, you won't be hit by these same conflicts the next time we update.
7. Then raise a PR to merge those changes in.

### Dealing with Conflicts
As the moodle plugin continues to be it's own repo that works will moodle there will no doubt be conflicts as time goes by. The best way to handle these is to check the differences that have taken place since the last update. Ensure that
they still work with our codebase. By pulling in and specifying the `--rebase` flag, we can step through each change.
To see the details of the change have a look at H5P's PR page and see if the change is something we should pull in. See here: https://github.com/h5p/moodle-mod_hvp/pulls?q=is%3Apr+is%3Aclosed

### Updating the submodules
The submodules will also need to be manually updated with any changes. As these have been included in the codebase via git archive a simpler approach of checking out their codebases and applying patches will need to take place. 
Refer to the versions numbering at the top of this readme for details on what versions of the submodules have been included in from.

**Note:** that we've tried to keep changes to the submodules directories to a minimum. The base H5P moodle plugin makes use of the three submodules across all of their integrations and the plugin code zips everything together to work with the platform.
At the time of integration of the plugin the only submodule that has been edited is the editor directory. This was to increase the version of fabric.js and get that working with darkroom.js.
To get a list of changes to any of the submodules simply do a git diff of the directory:
`git diff -- server/mod/hvp/editor`

## Upstream changes
We've forked the H5P repo, however we definitely want to play our part of being a good tenant in the open source world. Any changes that we make that we believe will benefit H5P should be submitted upstream as a patch:
1. Fork a copy of the original H5P moodle plugin on github into your own account: https://github.com/h5p/moodle-mod_hvp
2. clone the repo locally and checkout a branch based off of the `stable` branch - this is their protected master branch that they tag releases from.
3. Make your changes, commit and push the branch backup to your fork
4. On the original H5P moodle plugin repo, raise an issue detailing the issue and what you propose as a fix for it. Take note of the issue number
5. On your fork, create a PR back to the original H5P moodle plug repo and add a comment on the PR stating `solves #<issue_number_here>` - replacing `<issue_number_here>` with the issue number you just created.

The same goes for any changes that you can see in the submodule repos that should be made. Remember that the plugin was written for moodle so any changes we submit to H5P would need to work with moodle as well.


## A list of changes made by Totara  
Below is a list of changes made to the H5P module by Totara that have not been merged into the upstream GitHub repository:  
- CLOUD-795: Create code explanation; mermaid diagram; and Readme for modifying the H5P module
- CLOUD-1307: Fix H5P editor loading libraries with icons  
- CLOUD-1152: Update H5P editor permission checking
- CLOUD-1151: Improve H5P user state-saving code structure
- CLOUD-1080: Prevent XSS to H5P display
- CLOUD-1082: Add extra XSS sanitisation to H5P embed page
- CLOUD-641: Add "risk" field to capability definitions for the H5P module (merged as part of TL-36505)
- CLOUD-643: Improve ajax.php with sesskey requirement (merged as part of TL-36505)
- CLOUD-644: Improve ajax.php with require_login check (merged as part of TL-36505)
- CLOUD-645: Replace uses of PARAM_RAW with more appropriate values (merged as part of TL-36505)
- CLOUD-575: Add userdata support to H5P (merged as part of TL-36505)
- CLOUD-646: Added missing libraries to mod/hvp/thirdpartylibs.xml
- CLOUD-800: Minor performance optimisation in settings.php
- CLOUD-792: Add comments to improve understanding of security flow in embed.php
- CLOUD-651: Remove unnecessary H5P curl class
- CLOUD-936: Add self-completion checkbox to H5P activities (merged as part of TL-36505)
- CLOUD-806: Add flex icon for H5P (merged as part of TL-36505)
- CLOUD-650: Refactor parameter handling in H5P classes
- CLOUD-648: Remove unused H5P files note required by Totara (merged as part of TL-36505)
- CLOUD-1146: Fix restoration script for h5p-resizer.js (merged as part of TL-36505)
- CLOUD-1090: Update fabric.js version required by H5P (merged as part of TL-36505)
- CLOUD-791: Add H5P's jQuery library to the modules thirdpartylibs.xml (merged as part of TL-36505)
- CLOUD-794: Add H5P-specific error lang strings (merged as part of TL-36505)
- CLOUD-799: Add antivirus scanning to H5P activity installation (merged as part of TL-36505)
- TL-37894: Add error message to the H5P activity when H5P Hub is turned off
- TL-37807: Disable H5P plugin after installation by default
- TL-37696: Create ad-hoc task to fetch library metadata from external sources after installation
- TL-37607: Add more libraries to thirdpartylibs.xml for compatibility
- TL-37702: Make H5P compatible with right-to-left (RTL) languages
- TL-37815: Moved xapi-custom-report.css and view.css content into styles.css (merged as part of TL-37702)
- TL-38111: Add new course backup configuration option to in/exclude H5P libraries from activities
- TL-37993: Create an error that shows when attempting to upload to H5P without selecting a file
- TL-38118: Enable H5P plugin by default
- TL-37706: Capture liability agreement from customer via warning message when turning on H5P
- TL-37703: Add behat test coverage
- TL-37849: Remove unnecessary width hack for iOS/Safari
- TL-37964: Remove default external H5P access
- TL-37156: Patching dynamic declared properties for PHP 8.2 support