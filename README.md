# pm4converter
This script tries to convert pm3 plugins to pm4 as good as possible, but sadly not perfect.
Please open issues if you find any unexpected behaviour, to help improving this script.

## How to use
 - Download the project and extract `pm4converter.php`
 - Move the file (`pm4converter.php`) to another directory, or leave it where it is right now
 - Select a plugin: it MUST be a folder plugin, the directory selected must contain `src` and `plugin.yml`. Copy it.
 - Paste it next to the previously extracted `pm4converter.php`
 - Now run the command: `php pm4converter.php <DIRECTORY_NAME>`, replace `<DIRECTORY_NAME>` with the name of the directory you pasted. If PHP is not set inside of the environment variables, you might have to enter the full path to php's binary, `php.exe` on Windows and `php` on Linux
 - The converted plugin will be stored in the same directory under `output/<DIRECTORY_NAME>`
