#!/bin/bash
# we assume that you have installed the Win 10.

cd /c/xampp/htdocs/web-analyzer/html

php html.php

# firefox "http://localhost/evaluate.html"

php inte_chrome_csv.php
# php inte_chrome_csv.php name
# php inte_chrome_csv.php id

Rscript "$HOME/workspace(R)/tag-nest.R"
Rscript "$HOME/workspace(R)/tag-flat.R"

cd chrome-r-summary/

php avg_record.php
# php avg_record.php name
# php avg_record.php id

