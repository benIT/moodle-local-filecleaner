# Moodle FileCleaner local plugin

## Installation

Place this repo in the `local` folder of your Moodle installation and named it `filecleaner`. 

## Purpose

I design this plugin to cleanup orphan files.
 
## Usage

Bypass `fileslastcleanup` setting to launch cron file cleanup again:

        php cli/cleanup.php --resetdate
        
or perform file cleanup:
        
        php cli/cleanup.php --clean
        