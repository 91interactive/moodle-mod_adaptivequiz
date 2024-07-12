#!/bin/bash

# change the version number in the file version.php in the line "$plugin->version = YYYYMMDDII;" increase the number II by 1

# # Increment version number in version.php
# awk '/\$plugin->version = [0-9]+;/{ 
#     match($0, /([0-9]+)([0-9]{2});$/, arr); 
#     newVersion=sprintf("%d%02d;", arr[1], arr[2]+1); 
#     sub(/[0-9]+;$/, newVersion); 
#     print; 
#     next 
# }1' version.php > tmp_version.php && mv tmp_version.php version.php

# Increment version number in version.php using awk
awk '{
    if ($0 ~ /\$plugin->version = [0-9]+;/) {
		newVersion = substr($0, 19, 11)+1;
		newVersion = sprintf("%d;", newVersion);
        sub(/[0-9]+;$/, newVersion);
    }
    print
}' version.php > tmp_version.php && mv tmp_version.php version.php


# Navigate to the directory containing the export folder and other files
# cd /path/to/your/directory

# 1. Delete export.zip
rm -f export.zip

# 2. Delete the contents of the export folder
rm -rf export/*

# 3. Copy all files to the export folder, excluding specified files and folders
rsync -av --exclude='.git/' --exclude='.gitignore' --exclude='export.sh' --exclude='export/' . export/

# 4. Compress the export folder
zip -r export.zip export