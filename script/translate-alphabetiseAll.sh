#!/bin/bash

SCRIPTPATH=$( cd $(dirname $0) ; pwd -P )

TRPATH="${SCRIPTPATH}/../translations"

$SCRIPTPATH/translate-poFileSorter.php "${TRPATH}/programmes.pot" "${TRPATH}/programmes.pot"
$SCRIPTPATH/translate-updateFromTemplate.sh
